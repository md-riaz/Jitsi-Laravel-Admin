<?php

namespace App\Console\Commands;

use App\Models\Meeting;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateMeetingStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meetings:update-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update meeting statuses based on current time (scheduled -> live -> ended)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        // Update scheduled meetings to live if start time has passed
        $scheduledToLive = Meeting::where('status', 'scheduled')
            ->whereNotNull('start_at')
            ->where('start_at', '<=', $now)
            ->update(['status' => 'live']);

        if ($scheduledToLive > 0) {
            $this->info("Updated {$scheduledToLive} meeting(s) from scheduled to live");
        }

        // Update live meetings to ended if end time has passed
        $liveToEnded = Meeting::where('status', 'live')
            ->whereNotNull('end_at')
            ->where('end_at', '<=', $now)
            ->update(['status' => 'ended']);

        if ($liveToEnded > 0) {
            $this->info("Updated {$liveToEnded} meeting(s) from live to ended");
        }

        if ($scheduledToLive === 0 && $liveToEnded === 0) {
            $this->info('No meeting status updates required');
        }

        return Command::SUCCESS;
    }
}
