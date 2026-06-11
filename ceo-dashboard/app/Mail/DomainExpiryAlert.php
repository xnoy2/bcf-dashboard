<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DomainExpiryAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $domains,   // each: name, account, expires, days_until, renew_auto
        public int $windowDays,
    ) {
    }

    public function envelope(): Envelope
    {
        $expired = count(array_filter($this->domains, fn ($d) => ($d['days_until'] ?? 0) < 0));
        $soon    = count($this->domains) - $expired;

        $subject = '⚠️ Domain renewals need attention: ';
        $parts = [];
        if ($expired) {
            $parts[] = "$expired expired";
        }
        if ($soon) {
            $parts[] = "$soon expiring within {$this->windowDays} days";
        }

        return new Envelope(subject: $subject . implode(', ', $parts));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.domain-expiry');
    }
}
