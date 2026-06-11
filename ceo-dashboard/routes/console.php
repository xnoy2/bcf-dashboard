<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Keep dashboard snapshots warm so pages load instantly.
// Requires `php artisan schedule:work` (dev) or a cron entry (prod).
Schedule::command('dashboard:warm')
    ->everyFiveMinutes()
    ->withoutOverlapping();

// Daily domain-expiry alert email (window set by RENEWAL_ALERT_DAYS).
Schedule::command('renewals:notify')
    ->dailyAt('08:00');
