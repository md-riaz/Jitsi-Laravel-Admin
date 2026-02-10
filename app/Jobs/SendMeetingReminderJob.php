<?php

namespace App\Jobs;

use App\Mail\MeetingReminderMail;
use App\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendMeetingReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Meeting $meeting,
        public int $minutesBefore = 10
    ) {}

    public function handle(): void
    {
        // Get all participants for this meeting
        $participants = $this->meeting->participants()
            ->whereNotNull('email')
            ->get();
        
        foreach ($participants as $participant) {
            Mail::to($participant->email)
                ->send(new MeetingReminderMail(
                    $this->meeting,
                    $participant->email,
                    $this->minutesBefore
                ));
        }
        
        // Also send to invites
        $invites = $this->meeting->invites()
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->get();
        
        foreach ($invites as $invite) {
            Mail::to($invite->email)
                ->send(new MeetingReminderMail(
                    $this->meeting,
                    $invite->email,
                    $this->minutesBefore
                ));
        }
    }
}
