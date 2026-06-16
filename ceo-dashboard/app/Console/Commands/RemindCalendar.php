<?php

namespace App\Console\Commands;

use App\Mail\CalendarReminder;
use App\Models\CalendarEntry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class RemindCalendar extends Command
{
    protected $signature = 'calendar:remind';
    protected $description = 'Email a digest of calendar jobs that are overdue or coming due within their reminder window.';

    public function handle(): int
    {
        $open = CalendarEntry::query()
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->orderByRaw('COALESCE(end_date, start_date) asc')
            ->get();

        $overdue  = $open->filter->isOverdue()->values();
        $dueSoon  = $open->filter(function (CalendarEntry $e) {
            $due = $e->dueDate();
            return ! $e->isOverdue()
                && $due->betweenIncluded(now()->startOfDay(), now()->addDays($e->reminder_days)->endOfDay());
        })->values();

        if ($overdue->isEmpty() && $dueSoon->isEmpty()) {
            $this->info('No calendar jobs overdue or due within their reminder window.');
            return self::SUCCESS;
        }

        $emails = config('integrations.renewal_alerts.emails', []);
        if (! $emails) {
            $this->warn('Jobs need attention but no alert recipients configured (RENEWAL_ALERT_EMAILS).');
            return self::FAILURE;
        }

        Mail::to($emails)->send(new CalendarReminder($overdue->all(), $dueSoon->all()));

        $this->info("Calendar reminder sent ({$overdue->count()} overdue, {$dueSoon->count()} due soon) to: " . implode(', ', $emails));

        return self::SUCCESS;
    }
}
