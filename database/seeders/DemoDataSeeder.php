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

        // Create meetings with different statuses
        
        // 1. Meeting starting soon (in 5 minutes)
        $meetingSoon = Meeting::firstOrCreate(
            ['title' => 'Team Standup - Starting Soon'],
            [
                'organization_id' => $organization->id,
                'created_by' => $user->id,
                'description' => 'Daily team standup meeting to discuss progress and blockers.',
                'start_at' => Carbon::now()->addMinutes(5),
                'end_at' => Carbon::now()->addMinutes(35),
                'timezone' => 'UTC',
                'join_early_minutes' => 10,
                'join_late_minutes' => 60,
                'visibility' => 'org_only',
                'status' => 'scheduled',
            ]
        );

        // 2. Meeting happening now
        $meetingNow = Meeting::firstOrCreate(
            ['title' => 'Product Review - Live Now'],
            [
                'organization_id' => $organization->id,
                'created_by' => $user->id,
                'description' => 'Review of new product features and roadmap discussion.',
                'start_at' => Carbon::now()->subMinutes(5),
                'end_at' => Carbon::now()->addMinutes(55),
                'timezone' => 'UTC',
                'join_early_minutes' => 10,
                'join_late_minutes' => 60,
                'visibility' => 'link_anyone',
                'status' => 'live',
            ]
        );

        // 3. Meeting in the future (tomorrow)
        $meetingFuture = Meeting::firstOrCreate(
            ['title' => 'Weekly Planning - Tomorrow'],
            [
                'organization_id' => $organization->id,
                'created_by' => $user->id,
                'description' => 'Weekly planning session for the upcoming sprint.',
                'start_at' => Carbon::now()->addDay()->setTime(14, 0),
                'end_at' => Carbon::now()->addDay()->setTime(15, 0),
                'timezone' => 'UTC',
                'join_early_minutes' => 10,
                'join_late_minutes' => 60,
                'visibility' => 'invite_only',
                'status' => 'scheduled',
            ]
        );

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
