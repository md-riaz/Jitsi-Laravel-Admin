<?php

namespace App\Mail;

use App\Models\Meeting;
use App\Models\MeetingInvite;
use App\Services\MailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MeetingInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Meeting $meeting,
        public MeetingInvite $invite,
        public string $inviteToken
    ) {}

    public function envelope(): Envelope
    {
        $variables = $this->templateVariables();
        $fallback = "Invitation: {$this->meeting->title}";

        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: app(MailTemplateService::class)->renderSubject('meeting_invitation', $variables, $fallback),
        );
    }

    public function content(): Content
    {
        $variables = $this->templateVariables();
        $fallbackHtml = view('emails.meeting-invitation', [
            'meeting' => $this->meeting,
            'invite' => $this->invite,
            'inviteUrl' => $variables['invite_url'],
            'meetingUrl' => $variables['meeting_url'],
        ])->render();

        $bodyHtml = app(MailTemplateService::class)->renderBodyHtml('meeting_invitation', $variables, $fallbackHtml);

        return new Content(
            view: 'emails.dynamic-template',
            with: [
                'bodyHtml' => $bodyHtml,
            ],
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        if (class_exists(\App\Services\CalendarService::class)) {
            $calendarService = app(\App\Services\CalendarService::class);
            $icsContent = $calendarService->generateIcs($this->meeting);

            $attachments[] = Attachment::fromData(fn () => $icsContent, 'meeting.ics')
                ->withMime('text/calendar');
        }

        return $attachments;
    }

    private function templateVariables(): array
    {
        return [
            'meeting_title' => $this->meeting->title,
            'meeting_date' => $this->meeting->start_at?->format('l, F j, Y') ?? '',
            'meeting_time' => ($this->meeting->start_at?->format('g:i A') ?? '') . ' - ' . ($this->meeting->end_at?->format('g:i A') ?? '') . " ({$this->meeting->timezone})",
            'meeting_duration_minutes' => $this->meeting->start_at && $this->meeting->end_at ? (string) $this->meeting->start_at->diffInMinutes($this->meeting->end_at) : '',
            'organizer_name' => $this->meeting->creator?->name ?? '',
            'invite_url' => route('invite.show', ['token' => $this->inviteToken]),
            'meeting_url' => route('meeting.show', ['meeting' => $this->meeting->id]),
            'join_early_minutes' => (string) $this->meeting->join_early_minutes,
        ];
    }
}
