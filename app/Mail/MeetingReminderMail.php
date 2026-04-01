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

class MeetingReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Meeting $meeting,
        public string $recipientEmail,
        public int $minutesUntilStart = 10
    ) {}

    public function envelope(): Envelope
    {
        $variables = $this->templateVariables();
        $fallback = "Reminder: {$this->meeting->title} starts in {$this->minutesUntilStart} minutes";

        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: app(MailTemplateService::class)->renderSubject('meeting_reminder', $variables, $fallback),
        );
    }

    public function content(): Content
    {
        $variables = $this->templateVariables();
        $fallbackHtml = view('emails.meeting-reminder', [
            'meeting' => $this->meeting,
            'minutesUntilStart' => $this->minutesUntilStart,
            'meetingUrl' => $variables['meeting_url'],
        ])->render();

        $bodyHtml = app(MailTemplateService::class)->renderBodyHtml('meeting_reminder', $variables, $fallbackHtml);

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
            'minutes_until_start' => (string) $this->minutesUntilStart,
            'meeting_date' => $this->meeting->start_at?->format('l, F j, Y') ?? '',
            'meeting_time' => ($this->meeting->start_at?->format('g:i A') ?? '') . " ({$this->meeting->timezone})",
            'meeting_duration_minutes' => $this->meeting->start_at && $this->meeting->end_at ? (string) $this->meeting->start_at->diffInMinutes($this->meeting->end_at) : '',
            'meeting_url' => route('meeting.show', ['meeting' => $this->meeting->id]),
        ];
    }
}
