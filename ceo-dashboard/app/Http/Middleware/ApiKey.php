<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Static API-key guard for the read-only integration API.
 *
 * Accepts the key in any of (in order):
 *   - "X-API-Key: <key>" header
 *   - "Authorization: Bearer <key>" header
 *   - "?api_key=<key>" query string (handy for quick browser/cURL checks)
 *
 * The expected key lives in DASHBOARD_API_KEY (config integrations.api.key).
 * Comparison is constant-time to avoid timing leaks.
 */
class ApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('integrations.api.key');

        // Fail closed: if no key is configured, the API is effectively disabled.
        if ($expected === '') {
            return response()->json([
                'error' => 'API is not configured on this server.',
            ], 503);
        }

        $provided = $this->extractKey($request);

        if ($provided === null || ! hash_equals($expected, $provided)) {
            return response()->json([
                'error' => 'Invalid or missing API key.',
            ], 401);
        }

        return $next($request);
    }

    private function extractKey(Request $request): ?string
    {
        if ($header = $request->header('X-API-Key')) {
            return trim($header);
        }

        $bearer = $request->bearerToken();
        if ($bearer !== null && $bearer !== '') {
            return trim($bearer);
        }

        $query = $request->query('api_key');
        if (is_string($query) && $query !== '') {
            return trim($query);
        }

        return null;
    }
}
