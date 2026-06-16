<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CalendarEntry extends Model
{
    protected $fillable = [
        'client_name', 'address', 'phone', 'start_date', 'end_date',
        'is_birthday', 'dob', 'order_details', 'status', 'business',
        'assigned_to', 'reminder_days', 'created_by',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'dob'         => 'date',
        'is_birthday' => 'boolean',
    ];

    public const STATUSES = ['scheduled', 'in_progress', 'completed', 'cancelled'];

    public function attachments(): HasMany
    {
        return $this->hasMany(CalendarAttachment::class);
    }

    /** The date the job is considered "due" (range end if present, else start). */
    public function dueDate()
    {
        return $this->end_date ?: $this->start_date;
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['scheduled', 'in_progress'], true);
    }

    public function isOverdue(): bool
    {
        return $this->isOpen() && $this->dueDate()->isPast() && ! $this->dueDate()->isToday();
    }

    /** Open jobs whose due date is today or in the past (need attention). */
    public function scopeOverdue($q)
    {
        return $q->whereIn('status', ['scheduled', 'in_progress'])
            ->whereRaw('COALESCE(end_date, start_date) < ?', [now()->startOfDay()]);
    }

    /** Open jobs due within the next $days days (upcoming). */
    public function scopeDueWithin($q, int $days)
    {
        return $q->whereIn('status', ['scheduled', 'in_progress'])
            ->whereRaw('COALESCE(end_date, start_date) BETWEEN ? AND ?',
                [now()->startOfDay(), now()->addDays($days)->endOfDay()]);
    }
}
