<?php

namespace Database\Seeders;

use App\Models\Meeting;
use App\Models\MeetingParticipant;
use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create a test user
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'account_type' => 'organization',
            ]
        );

        // Assign super-admin role
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        if ($superAdminRole && !$user->roles()->where('role_id', $superAdminRole->id)->exists()) {
            $user->roles()->attach($superAdminRole->id);
        }

        // Create organization
        $organization = Organization::firstOrCreate(
            ['slug' => 'demo-org'],
            [
                'name' => 'Demo Organization',
            ]
        );

        // Link user to organization if not already linked
        if (!$user->organization_id) {
            $user->organization_id = $organization->id;
            $user->save();
        }

        // Add user to organization pivot table if not already added
        if (!$organization->users()->where('user_id', $user->id)->exists()) {
            $organization->users()->attach($user->id, ['role' => 'admin']);
        }

        // Create meetings with different statuses
        
        // 1. Meeting starting soon (in 5 minutes)
        $meetingSoon = Meeting::where('title', 'Team Standup - Starting Soon')->first();
        if (!$meetingSoon) {
            $meetingSoon = new Meeting();
            $meetingSoon->organization_id = $organization->id;
            $meetingSoon->created_by = $user->id;
            $meetingSoon->title = 'Team Standup - Starting Soon';
            $meetingSoon->description = 'Daily team standup meeting to discuss progress and blockers.';
            $meetingSoon->room_name = 'mtg_' . Str::lower(Str::random(12));
            $meetingSoon->start_at = Carbon::now()->addMinutes(5);
            $meetingSoon->end_at = Carbon::now()->addMinutes(35);
            $meetingSoon->timezone = 'UTC';
            $meetingSoon->join_early_minutes = 10;
            $meetingSoon->join_late_minutes = 60;
            $meetingSoon->visibility = 'org_only';
            $meetingSoon->status = 'scheduled';
            $meetingSoon->save();
        }

        // 2. Meeting happening now
        $meetingNow = Meeting::where('title', 'Product Review - Live Now')->first();
        if (!$meetingNow) {
            $meetingNow = new Meeting();
            $meetingNow->organization_id = $organization->id;
            $meetingNow->created_by = $user->id;
            $meetingNow->title = 'Product Review - Live Now';
            $meetingNow->description = 'Review of new product features and roadmap discussion.';
            $meetingNow->room_name = 'mtg_' . Str::lower(Str::random(12));
            $meetingNow->start_at = Carbon::now()->subMinutes(5);
            $meetingNow->end_at = Carbon::now()->addMinutes(55);
            $meetingNow->timezone = 'UTC';
            $meetingNow->join_early_minutes = 10;
            $meetingNow->join_late_minutes = 60;
            $meetingNow->visibility = 'link_anyone';
            $meetingNow->status = 'live';
            $meetingNow->save();
        }

        // 3. Meeting in the future (tomorrow)
        $meetingFuture = Meeting::where('title', 'Weekly Planning - Tomorrow')->first();
        if (!$meetingFuture) {
            $meetingFuture = new Meeting();
            $meetingFuture->organization_id = $organization->id;
            $meetingFuture->created_by = $user->id;
            $meetingFuture->title = 'Weekly Planning - Tomorrow';
            $meetingFuture->description = 'Weekly planning session for the upcoming sprint.';
            $meetingFuture->room_name = 'mtg_' . Str::lower(Str::random(12));
            $meetingFuture->start_at = Carbon::now()->addDay()->setTime(14, 0);
            $meetingFuture->end_at = Carbon::now()->addDay()->setTime(15, 0);
            $meetingFuture->timezone = 'UTC';
            $meetingFuture->join_early_minutes = 10;
            $meetingFuture->join_late_minutes = 60;
            $meetingFuture->visibility = 'invite_only';
            $meetingFuture->status = 'scheduled';
            $meetingFuture->save();
        }

        // Add participants
        foreach ([$meetingSoon, $meetingNow, $meetingFuture] as $meeting) {
            MeetingParticipant::firstOrCreate(
                [
                    'meeting_id' => $meeting->id,
                    'user_id' => $user->id,
                ],
                [
                    'role' => 'host',
                    'invite_status' => 'accepted',
                ]
            );
        }

        $this->command->info('Demo data created successfully!');
        $this->command->info('Login credentials: admin@example.com / password');
        $this->command->info('Meetings created:');
        $this->command->info('- ' . $meetingSoon->title . ' (ID: ' . $meetingSoon->id . ')');
        $this->command->info('- ' . $meetingNow->title . ' (ID: ' . $meetingNow->id . ')');
        $this->command->info('- ' . $meetingFuture->title . ' (ID: ' . $meetingFuture->id . ')');
    }
}
