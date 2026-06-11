<?php

namespace App\Console\Commands;

use App\Mail\DomainExpiryAlert;
use App\Services\GoDaddy\GoDaddyService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotifyRenewals extends Command
{
    protected $signature = 'renewals:notify {--days= : Override the alert window in days}';
    protected $description = 'Email an alert when domains are expired or expiring within the alert window.';

    public function handle(GoDaddyService $godaddy): int
    {
        $days   = (int) ($this->option('days') ?: config('integrations.renewal_alerts.days', 10));
        $emails = config('integrations.renewal_alerts.emails', []);

        $urgent = collect($godaddy->overview()['domains'] ?? [])
            ->filter(fn ($d) => ($d['active'] ?? false)
                && $d['days_until'] !== null
                && $d['days_until'] <= $days)
            ->sortBy('days_until')
            ->values()
            ->all();

        if (! $urgent) {
            $this->info("No domains expired or expiring within $days days — no alert sent.");
            return self::SUCCESS;
        }

        if (! $emails) {
            $this->warn('Urgent domains found, but RENEWAL_ALERT_EMAILS is empty — nothing sent.');
            return self::FAILURE;
        }

        Mail::to($emails)->send(new DomainExpiryAlert($urgent, $days));

        $this->info(count($urgent) . " urgent domain(s) — alert sent to: " . implode(', ', $emails));

        return self::SUCCESS;
    }
}
