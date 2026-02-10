<?php

namespace App\Mail;

use App\Models\Meeting;
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
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: "Updated: {$this->meeting->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.meeting-updated',
            with: [
                'meeting' => $this->meeting,
                'changes' => $this->changes,
                'meetingUrl' => route('meeting.show', ['meeting' => $this->meeting->id]),
            ],
        );
    }
}
