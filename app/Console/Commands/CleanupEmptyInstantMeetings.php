<?php

namespace App\Console\Commands;

use App\Services\MeetingLifecycleService;
use Illuminate\Console\Command;

class CleanupEmptyInstantMeetings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meetings:cleanup-empty-instant {--grace-seconds=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ends live instant meetings that have stayed empty past the configured grace period';

    public function __construct(
        private readonly MeetingLifecycleService $lifecycleService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $graceSeconds = (int) ($this->option('grace-seconds') ?: config('services.jitsi.empty_room_grace_seconds', 60));
        $updated = $this->lifecycleService->cleanupEmptyInstantMeetings($graceSeconds);

        $this->info("Ended {$updated} empty instant meeting(s).");

        return self::SUCCESS;
    }
}
