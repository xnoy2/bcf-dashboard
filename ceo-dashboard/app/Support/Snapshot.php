<?php

namespace App\Support;

use App\Models\DashboardSnapshot;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * DB-backed snapshot cache with stale-while-error fallback.
 *
 * - Returns the stored payload when it is still fresh.
 * - Otherwise runs the live fetch, stores it, and returns it.
 * - If the live fetch throws (API down, timeout), returns the last good
 *   payload so the dashboard never shows an empty/broken page.
 */
class Snapshot
{
    /** When true (the warm command / cron), remember() always fetches fresh. */
    protected static bool $force = false;

    /** Keys already refreshed during this force run (avoids refetching the
     *  same source when services nest each other, e.g. operations→staff). */
    protected static array $refreshedKeys = [];

    public static function forceRefresh(bool $on = true): void
    {
        self::$force = $on;
        if ($on) {
            self::$refreshedKeys = [];
        }
    }

    public static function isForced(): bool
    {
        return self::$force;
    }

    public static function remember(string $key, int $freshMinutes, callable $callback): array
    {
        // Force mode (warm command): always fetch fresh and store. Never serves
        // stale and never spawns a background warm (avoids recursion). Each key
        // is only fetched once per run — nested services reuse the fresh copy.
        if (self::$force) {
            if (isset(self::$refreshedKeys[$key])) {
                return DashboardSnapshot::where('key', $key)->first()?->payload ?? [];
            }
            try {
                $data = self::refresh($key, $callback);
                self::$refreshedKeys[$key] = true;
                return $data;
            } catch (\Throwable $e) {
                report($e);
                $row = DashboardSnapshot::where('key', $key)->first();
                return $row?->payload ?? ['error' => 'Data temporarily unavailable'];
            }
        }

        $row = DashboardSnapshot::where('key', $key)->first();

        // Present → ALWAYS serve the cached payload instantly (pure DB read,
        // no API call). If it's stale, kick off a throttled background refresh
        // so the data catches up without ever blocking this request.
        if ($row) {
            $fresh = $row->generated_at && $row->generated_at->gt(now()->subMinutes($freshMinutes));
            if (! $fresh) {
                self::triggerBackgroundWarm();
            }
            return $row->payload ?? [];
        }

        // Missing (first ever, before any warm) → fetch synchronously this once.
        try {
            return self::refresh($key, $callback);
        } catch (\Throwable $e) {
            report($e);
            return ['error' => 'Data temporarily unavailable'];
        }
    }

    /**
     * Spawn a detached `dashboard:warm` process to refresh all snapshots in the
     * background. Throttled to once per 5 minutes via a non-released cache lock,
     * so stale page views never pile up duplicate refreshes — and the web server
     * process is never occupied by the (slow) API calls.
     */
    public static function triggerBackgroundWarm(bool $ignoreThrottle = false): void
    {
        // get() without release() = the lock simply expires after 5 min,
        // acting as a throttle window. A manual refresh bypasses it.
        $lock = Cache::lock('snapshot:bg-warm', 300);
        if ($ignoreThrottle) {
            $lock->forceRelease();
        }
        if (! $lock->get()) {
            return;
        }

        $php     = PHP_BINARY;
        $artisan = base_path('artisan');

        try {
            if (PHP_OS_FAMILY === 'Windows') {
                pclose(popen('start /B "" ' . escapeshellarg($php) . ' ' . escapeshellarg($artisan) . ' dashboard:warm > NUL 2>&1', 'r'));
            } else {
                exec(escapeshellarg($php) . ' ' . escapeshellarg($artisan) . ' dashboard:warm > /dev/null 2>&1 &');
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** Force-refresh a snapshot (used by the warm-up command). */
    public static function refresh(string $key, callable $callback): array
    {
        $data = $callback();

        DashboardSnapshot::updateOrCreate(
            ['key' => $key],
            ['payload' => $data, 'generated_at' => now()],
        );

        return $data;
    }

    public static function generatedAt(string $key): ?Carbon
    {
        return DashboardSnapshot::where('key', $key)->value('generated_at');
    }

    /** Most recent snapshot time across all keys (for a global "data as of"). */
    public static function latest(): ?Carbon
    {
        $max = DashboardSnapshot::max('generated_at');

        return $max ? Carbon::parse($max) : null;
    }
}
