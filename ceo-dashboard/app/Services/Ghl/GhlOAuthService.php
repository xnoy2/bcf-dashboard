<?php

namespace App\Services\Ghl;

use App\Models\GhlLocation;
use App\Models\GhlOauthToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * GoHighLevel agency OAuth: one agency install → access to every sub-account.
 *
 * Flow: authorizeUrl() → user approves → exchangeCode() stores the agency
 * (Company) token → discoverLocations() lists sub-accounts → locationToken()
 * mints a short-lived per-location token used to read that sub-account's data.
 * The agency token auto-refreshes.
 */
class GhlOAuthService
{
    private string $apiBase;
    private string $version;

    public function __construct()
    {
        $this->apiBase = (string) config('integrations.ghl.base_url');
        $this->version = (string) config('integrations.ghl.version');
    }

    private function cfg(string $key): ?string
    {
        return config("integrations.ghl_oauth.$key");
    }

    public function isConfigured(): bool
    {
        return filled($this->cfg('client_id')) && filled($this->cfg('client_secret'));
    }

    public function isConnected(): bool
    {
        return GhlOauthToken::current() !== null;
    }

    /** Full redirect URI — must exactly match one registered in the GHL app. */
    public function redirectUri(): string
    {
        return url($this->cfg('redirect_path'));
    }

    /** The app version id (Draft apps authorise by version_id, not client_id). */
    private function versionId(): ?string
    {
        return $this->cfg('version_id') ?: strtok((string) $this->cfg('client_id'), '-');
    }

    /** URL to send the agency admin to, to approve the app. */
    public function authorizeUrl(): string
    {
        return $this->cfg('authorize_url') . '?' . http_build_query([
            'response_type' => 'code',
            'redirect_uri'  => $this->redirectUri(),
            'version_id'    => $this->versionId(),
            'scope'         => $this->cfg('scopes'),
        ]);
    }

    /** Exchange an authorization code for the agency (Company) token. */
    public function exchangeCode(string $code): GhlOauthToken
    {
        $res = Http::asForm()->post("{$this->apiBase}/oauth/token", [
            'client_id'     => $this->cfg('client_id'),
            'client_secret' => $this->cfg('client_secret'),
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'user_type'     => 'Company',
            'redirect_uri'  => $this->redirectUri(),
        ]);

        $res->throw();

        return $this->store($res->json());
    }

    /** A valid agency access token, refreshing if it has expired. */
    public function companyToken(): ?string
    {
        $token = GhlOauthToken::current();
        if (! $token) {
            return null;
        }
        if ($token->isExpired()) {
            $token = $this->refresh($token) ?? $token;
        }

        return $token->access_token;
    }

    private function refresh(GhlOauthToken $token): ?GhlOauthToken
    {
        if (! $token->refresh_token) {
            return null;
        }

        try {
            $res = Http::asForm()->post("{$this->apiBase}/oauth/token", [
                'client_id'     => $this->cfg('client_id'),
                'client_secret' => $this->cfg('client_secret'),
                'grant_type'    => 'refresh_token',
                'refresh_token' => $token->refresh_token,
                'user_type'     => 'Company',
            ]);
            $res->throw();

            return $this->store($res->json());
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    private function store(array $data): GhlOauthToken
    {
        $token = GhlOauthToken::current() ?? new GhlOauthToken();
        $token->company_id    = $data['companyId'] ?? $token->company_id;
        $token->access_token  = $data['access_token'];
        $token->refresh_token = $data['refresh_token'] ?? $token->refresh_token;
        $token->scope         = $data['scope'] ?? $token->scope;
        $token->expires_at    = isset($data['expires_in'])
            ? now()->addSeconds((int) $data['expires_in'])
            : now()->addDay();
        $token->save();

        return $token;
    }

    /**
     * List sub-accounts under the agency and upsert them into ghl_locations.
     * Returns the number discovered.
     */
    public function discoverLocations(): int
    {
        $token = $this->companyToken();
        $companyId = GhlOauthToken::current()?->company_id;
        if (! $token || ! $companyId) {
            return 0;
        }

        $res = Http::withHeaders([
            'Authorization' => "Bearer $token",
            'Version'       => $this->version,
        ])->get("{$this->apiBase}/locations/search", [
            'companyId' => $companyId,
            'limit'     => 1000,
        ]);

        if (! $res->successful()) {
            report(new \RuntimeException('GHL location discovery failed: ' . $res->status() . ' ' . $res->body()));
            return 0;
        }

        $locations = $res->json('locations', []);
        foreach ($locations as $loc) {
            $id = $loc['id'] ?? $loc['_id'] ?? null;
            if (! $id) {
                continue;
            }
            GhlLocation::updateOrCreate(
                ['location_id' => $id],
                [
                    'name'      => $loc['name'] ?? null,
                    'address'   => $loc['address'] ?? null,
                    'synced_at' => now(),
                ]
            );
        }

        return count($locations);
    }

    /** A short-lived location (sub-account) access token, cached. */
    public function locationToken(string $locationId): ?string
    {
        return Cache::remember("ghl:loctoken:$locationId", now()->addMinutes(45), function () use ($locationId) {
            $token = $this->companyToken();
            $companyId = GhlOauthToken::current()?->company_id;
            if (! $token || ! $companyId) {
                return null;
            }

            try {
                $res = Http::asForm()->withHeaders([
                    'Authorization' => "Bearer $token",
                    'Version'       => $this->version,
                ])->post("{$this->apiBase}/oauth/locationToken", [
                    'companyId'  => $companyId,
                    'locationId' => $locationId,
                ]);
                $res->throw();

                return $res->json('access_token');
            } catch (\Throwable $e) {
                report($e);
                return null;
            }
        });
    }

    public function disconnect(): void
    {
        GhlOauthToken::query()->delete();
        GhlLocation::query()->delete();
    }
}
