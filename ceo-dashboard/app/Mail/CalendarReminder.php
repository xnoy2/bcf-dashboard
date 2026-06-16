<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CalendarReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $overdue, public array $dueSoon)
    {
    }

    public function envelope(): Envelope
    {
        $parts = [];
        if ($this->overdue) {
            $parts[] = count($this->overdue) . ' overdue';
        }
        if ($this->dueSoon) {
            $parts[] = count($this->dueSoon) . ' coming up';
        }

        return new Envelope(subject: '📅 Calendar: ' . implode(' · ', $parts) . ' — jobs need attention');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.calendar-reminder');
    }
}
