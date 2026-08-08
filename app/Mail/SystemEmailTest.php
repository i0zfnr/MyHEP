<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class SystemEmailTest extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $reference,
        public Carbon $sentAt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'StudentEdge Email Delivery Test',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.system-test',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
