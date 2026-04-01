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

class MeetingUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Meeting $meeting,
        public string $recipientEmail,
        public array $changes = []
    ) {}

    public function envelope(): Envelope
    {
        $variables = $this->templateVariables();
        $fallback = "Updated: {$this->meeting->title}";

        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: app(MailTemplateService::class)->renderSubject('meeting_updated', $variables, $fallback),
        );
    }

    public function content(): Content
    {
        $variables = $this->templateVariables();
        $fallbackHtml = view('emails.meeting-updated', [
            'meeting' => $this->meeting,
            'changes' => $this->changes,
            'meetingUrl' => $variables['meeting_url'],
        ])->render();

        $bodyHtml = app(MailTemplateService::class)->renderBodyHtml('meeting_updated', $variables, $fallbackHtml);

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
            'meeting_date' => $this->meeting->start_at?->format('l, F j, Y') ?? '',
            'meeting_time' => ($this->meeting->start_at?->format('g:i A') ?? '') . ' - ' . ($this->meeting->end_at?->format('g:i A') ?? '') . " ({$this->meeting->timezone})",
            'meeting_url' => route('meeting.show', ['meeting' => $this->meeting->id]),
            'changes_html' => $this->renderChangesHtml(),
        ];
    }

    private function renderChangesHtml(): string
    {
        if (empty($this->changes)) {
            return '';
        }

        $items = [];

        foreach ($this->changes as $field => $change) {
            $old = e((string) ($change['old'] ?? ''));
            $new = e((string) ($change['new'] ?? ''));
            $label = e(ucfirst((string) $field));
            $items[] = "<li><strong>{$label}:</strong> {$old} → {$new}</li>";
        }

        return '<ul>' . implode('', $items) . '</ul>';
    }
}
