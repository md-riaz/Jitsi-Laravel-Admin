<?php

namespace App\Mail;

use App\Models\Meeting;
use App\Services\MailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MeetingCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Meeting $meeting,
        public string $recipientEmail,
        public ?string $reason = null
    ) {}

    public function envelope(): Envelope
    {
        $variables = $this->templateVariables();
        $fallback = "Cancelled: {$this->meeting->title}";

        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: app(MailTemplateService::class)->renderSubject('meeting_cancelled', $variables, $fallback),
        );
    }

    public function content(): Content
    {
        $variables = $this->templateVariables();
        $fallbackHtml = view('emails.meeting-cancelled', [
            'meeting' => $this->meeting,
            'reason' => $this->reason,
        ])->render();

        $bodyHtml = app(MailTemplateService::class)->renderBodyHtml('meeting_cancelled', $variables, $fallbackHtml);

        return new Content(
            view: 'emails.dynamic-template',
            with: [
                'bodyHtml' => $bodyHtml,
            ],
        );
    }

    private function templateVariables(): array
    {
        return [
            'meeting_title' => $this->meeting->title,
            'meeting_datetime' => $this->meeting->start_at?->format('l, F j, Y \\a\\t g:i A') ?? '',
            'organizer_name' => $this->meeting->creator?->name ?? '',
            'cancellation_reason' => $this->reason ?: 'Not provided',
        ];
    }
}
