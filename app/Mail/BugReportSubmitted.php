<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BugReportSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $report) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New StudentEdge report #' . $this->report['id'] . ': ' . $this->report['subject'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.bug-report-submitted',
            text: 'emails.bug-report-submitted-text',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
