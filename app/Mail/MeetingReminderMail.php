<?php

namespace App\Mail;

use App\Models\Meeting;
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
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: "Reminder: {$this->meeting->title} starts in {$this->minutesUntilStart} minutes",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.meeting-reminder',
            with: [
                'meeting' => $this->meeting,
                'minutesUntilStart' => $this->minutesUntilStart,
                'meetingUrl' => route('meeting.show', ['meeting' => $this->meeting->id]),
            ],
        );
    }
}
