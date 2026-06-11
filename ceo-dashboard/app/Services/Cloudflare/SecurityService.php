<?php

namespace App\Services\Cloudflare;

use App\Services\GoDaddy\GoDaddyService;
use App\Support\Snapshot;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * IT security overview for the whole estate:
 *  - Cloudflare zones (with DNSSEC + every web app/subdomain from DNS records)
 *  - all ACTIVE GoDaddy domains (reusing the renewals snapshot)
 * Each domain gets HTTPS reachability + SPF/DKIM/DMARC/MX checks and an
 * email-security grade; each web app gets status, response time and TLS
 * certificate expiry.
 */
class SecurityService
{
    private const CF = 'https://api.cloudflare.com/client/v4';

    public function __construct(private GoDaddyService $godaddy)
    {
    }

    public function overview(): array
    {
        return Snapshot::remember('security:all', 30, function () {
            $token = config('integrations.accounts.bcf.cloudflare.api_token');

            // ---------- Cloudflare zones, DNSSEC, DNS records ----------
            $zones = Http::withToken($token)->timeout(20)
                ->get(self::CF . '/zones', ['per_page' => 50])
                ->json('result', []);

            $cfDomains = [];
            $dnssec    = [];
            $dkimHint  = [];
            $webHosts  = [];

            foreach ($zones as $z) {
                $cfDomains[] = $z['name'];

                // Full status string: "active", "pending" (DS record not yet at
                // the registrar), or "disabled".
                $dnssec[$z['name']] = Http::withToken($token)->timeout(15)
                    ->get(self::CF . "/zones/{$z['id']}/dnssec")
                    ->json('result.status') ?? 'disabled';

                $records = Http::withToken($token)->timeout(20)
                    ->get(self::CF . "/zones/{$z['id']}/dns_records", ['per_page' => 200])
                    ->json('result', []);

                foreach ($records as $r) {
                    $name = $r['name'];
                    if (str_contains($name, '_domainkey')) {
                        $dkimHint[$z['name']] = true; // DKIM exists under a custom selector
                        continue;
                    }
                    if (! in_array($r['type'], ['A', 'AAAA', 'CNAME'])) {
                        continue;
                    }
                    // Skip plumbing records that aren't browsable apps.
                    if (preg_match('/^(autodiscover|pm-bounces|email)\./', $name)) {
                        continue;
                    }
                    $webHosts[$name] = [
                        'host'    => $name,
                        'zone'    => $z['name'],
                        'type'    => $r['type'],
                        'proxied' => (bool) ($r['proxied'] ?? false),
                    ];
                }
            }

            // ---------- Active GoDaddy domains (from the renewals snapshot) ----------
            $gdActive = collect($this->godaddy->overview()['domains'] ?? [])
                ->where('active', true)
                ->pluck('domain')
                ->all();

            $domains = [];
            foreach ($cfDomains as $d) {
                $domains[$d] = 'Cloudflare';
            }
            foreach ($gdActive as $d) {
                $domains[$d] = $domains[$d] ?? 'GoDaddy';
            }

            // ---------- HTTPS reachability (domains + web apps, pooled) ----------
            $domainHttp = $this->pooledHttps(array_keys($domains));
            $appHttp    = $this->pooledHttps(array_keys($webHosts));

            // ---------- Per-domain DNS + email grade ----------
            $domainsList = [];
            $dnsHealth = ['spf' => 0, 'dkim' => 0, 'dmarc' => 0, 'mx' => 0];
            $sslHealthy = 0;

            foreach ($domains as $name => $source) {
                $dns = $this->checkDns($name, $dkimHint[$name] ?? false);
                foreach (['spf', 'dkim', 'dmarc', 'mx'] as $k) {
                    if ($dns[$k]) {
                        $dnsHealth[$k]++;
                    }
                }

                $ok = $domainHttp[$name]['ok'] ?? false;
                if ($ok) {
                    $sslHealthy++;
                }

                $passes = count(array_filter($dns));
                $grade  = ['D', 'D', 'C', 'B', 'A'][$passes] ?? 'D';

                $domainsList[] = array_merge(
                    ['name' => $name, 'source' => $source, 'ssl' => $ok, 'grade' => $grade,
                     'dnssec' => $source === 'Cloudflare' ? ($dnssec[$name] ?? 'disabled') : null],
                    $dns,
                );
            }

            // ---------- Web apps: status, response time, cert expiry ----------
            $appsList = [];
            $appsOnline = 0;
            $certWarn = 0;
            $certChecked = 0;
            $certHealthy = 0;

            foreach ($webHosts as $name => $meta) {
                $h = $appHttp[$name] ?? ['ok' => false, 'status' => null, 'ms' => null];
                if ($h['ok']) {
                    $appsOnline++;
                }

                $certDays = null;
                if ($h['ok'] && $certChecked < 25) {
                    $certDays = $this->certDaysLeft($name);
                    if ($certDays !== null) {
                        $certChecked++;
                        if ($certDays > 14) {
                            $certHealthy++;
                        } else {
                            $certWarn++;
                        }
                    }
                }

                $appsList[] = array_merge($meta, [
                    'ok'        => $h['ok'],
                    'status'    => $h['status'],
                    'ms'        => $h['ms'],
                    'cert_days' => $certDays,
                ]);
            }

            usort($appsList, fn ($a, $b) => [$b['ok'], $a['host']] <=> [$a['ok'], $b['host']]);

            // ---------- Aggregates ----------
            $total      = max(count($domains), 1);
            $sslPercent = (int) round($sslHealthy / $total * 100);
            $dnsPercent = (int) round(array_sum($dnsHealth) / ($total * 4) * 100);
            $appsTotal  = count($appsList);
            $appsPct    = $appsTotal ? (int) round($appsOnline / $appsTotal * 100) : 100;
            $certPct    = $certChecked ? (int) round($certHealthy / $certChecked * 100) : null;

            $alerts = [];
            if ($sslPercent < 80) {
                $alerts[] = "Only {$sslPercent}% of domains respond over HTTPS";
            }
            if ($dnsPercent < 60) {
                $alerts[] = 'Email DNS (SPF/DKIM/DMARC) gaps across the domain portfolio';
            }
            foreach ($appsList as $a) {
                if (! $a['ok']) {
                    $alerts[] = "Web app unreachable: {$a['host']}";
                }
                if (($a['cert_days'] ?? 99) <= 14 && $a['cert_days'] !== null) {
                    $alerts[] = "TLS certificate for {$a['host']} expires in {$a['cert_days']} days";
                }
            }
            foreach ($dnssec as $zone => $status) {
                if ($status === 'pending') {
                    $alerts[] = "DNSSEC for $zone is pending — the DS record still needs adding at the registrar (GoDaddy)";
                }
            }

            return [
                'domains'      => count($domains),
                'cf_zones'     => count($cfDomains),
                'gd_domains'   => count($gdActive),
                'ssl_healthy'  => $sslHealthy,
                'alerts'       => count($alerts),
                'email_health' => $dnsPercent > 80 ? 'Good' : ($dnsPercent > 50 ? 'Mixed' : 'Issues'),
                'web_apps'     => ['total' => $appsTotal, 'online' => $appsOnline],
                'cert_warn'    => $certWarn,
                'compliance'   => [
                    'ssl'   => $sslPercent,
                    'dns'   => $dnsPercent,
                    'certs' => $certPct,
                    'apps'  => $appsPct,
                ],
                'alerts_list'  => $alerts,
                'domains_list' => $domainsList,
                'apps_list'    => $appsList,
            ];
        });
    }

    /** HTTPS reachability for many hosts at once; returns host => ok/status/ms. */
    private function pooledHttps(array $hosts): array
    {
        if (! $hosts) {
            return [];
        }

        $responses = Http::pool(fn (Pool $pool) => array_map(
            fn ($h) => $pool->as($h)
                ->withUserAgent('Mozilla/5.0 (CEO-Dashboard health check)')
                ->connectTimeout(8)
                ->timeout(12)
                ->withOptions(['allow_redirects' => true])
                ->get("https://$h"),
            $hosts,
        ));

        $out = [];
        foreach ($hosts as $h) {
            $r = $responses[$h] ?? null;
            if ($r instanceof Response) {
                $ms = null;
                try {
                    $ms = (int) round(($r->transferStats?->getTransferTime() ?? 0) * 1000) ?: null;
                } catch (\Throwable) {
                }
                // Any HTTP response means TLS handshake succeeded.
                $out[$h] = ['ok' => $r->status() < 500, 'status' => $r->status(), 'ms' => $ms];
            } else {
                $out[$h] = ['ok' => false, 'status' => null, 'ms' => null];
            }
        }

        return $out;
    }

    /** SPF / DKIM / DMARC / MX checks. $dkimKnown short-circuits custom selectors. */
    private function checkDns(string $domain, bool $dkimKnown = false): array
    {
        $result = ['spf' => false, 'dkim' => $dkimKnown, 'dmarc' => false, 'mx' => false];

        foreach (@dns_get_record($domain, DNS_TXT) ?: [] as $r) {
            if (isset($r['txt']) && str_contains($r['txt'], 'v=spf1')) {
                $result['spf'] = true;
            }
        }
        foreach (@dns_get_record("_dmarc.$domain", DNS_TXT) ?: [] as $r) {
            if (isset($r['txt']) && str_contains($r['txt'], 'v=DMARC1')) {
                $result['dmarc'] = true;
            }
        }
        if (! $result['dkim']) {
            foreach (['default', 'selector1', 'k2'] as $selector) {
                if (! empty(@dns_get_record("$selector._domainkey.$domain", DNS_TXT))
                    || ! empty(@dns_get_record("$selector._domainkey.$domain", DNS_CNAME))) {
                    $result['dkim'] = true;
                    break;
                }
            }
        }
        if (! empty(@dns_get_record($domain, DNS_MX))) {
            $result['mx'] = true;
        }

        return $result;
    }

    /** Days until the host's TLS certificate expires (null if unreadable). */
    private function certDaysLeft(string $host): ?int
    {
        try {
            $ctx = stream_context_create(['ssl' => [
                'capture_peer_cert' => true,
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'SNI_enabled'       => true,
                'peer_name'         => $host,
            ]]);
            $fp = @stream_socket_client("ssl://$host:443", $errno, $errstr, 6, STREAM_CLIENT_CONNECT, $ctx);
            if (! $fp) {
                return null;
            }
            $params = stream_context_get_params($fp);
            fclose($fp);
            $cert = $params['options']['ssl']['peer_certificate'] ?? null;
            if (! $cert) {
                return null;
            }
            $info = openssl_x509_parse($cert);

            return isset($info['validTo_time_t'])
                ? (int) floor(($info['validTo_time_t'] - time()) / 86400)
                : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
