<?php

namespace App\Jobs;

use App\Mail\MeetingInvitationMail;
use App\Models\Meeting;
use App\Models\MeetingInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendMeetingInvitationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Meeting $meeting,
        public MeetingInvite $invite,
        public string $inviteToken
    ) {}

    public function handle(): void
    {
        Mail::to($this->invite->email)
            ->send(new MeetingInvitationMail(
                $this->meeting,
                $this->invite,
                $this->inviteToken
            ));
    }
}
