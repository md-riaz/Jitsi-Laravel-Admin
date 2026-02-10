<?php

namespace App\Mail;

use App\Models\Meeting;
use App\Models\MeetingInvite;
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
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: "Invitation: {$this->meeting->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.meeting-invitation',
            with: [
                'meeting' => $this->meeting,
                'invite' => $this->invite,
                'inviteUrl' => route('invite.show', ['token' => $this->inviteToken]),
                'meetingUrl' => route('meeting.show', ['meeting' => $this->meeting->id]),
            ],
        );
    }

    public function attachments(): array
    {
        $attachments = [];
        
        // Generate and attach .ics calendar file
        if (class_exists(\App\Services\CalendarService::class)) {
            $calendarService = app(\App\Services\CalendarService::class);
            $icsContent = $calendarService->generateIcs($this->meeting);
            
            $attachments[] = Attachment::fromData(fn () => $icsContent, 'meeting.ics')
                ->withMime('text/calendar');
        }
        
        return $attachments;
    }
}
