<?php

namespace App\Services\Portals;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Builds a configured HTTP client for a named portal, applying the right
 * auth style (Bearer token or x-api-key) from config/integrations.php.
 */
class PortalClient
{
    public static function for(string $portal): PendingRequest
    {
        $cfg = config("integrations.portals.$portal");

        $http = Http::baseUrl(rtrim($cfg['base_url'] ?? '', '/'))
            ->acceptJson()
            ->timeout(30);

        $auth = $cfg['auth'] ?? [];

        return match ($auth['type'] ?? null) {
            'bearer'    => $http->withToken($auth['value']),
            'x-api-key' => $http->withHeaders(['x-api-key' => $auth['value']]),
            default     => $http,
        };
    }
}
