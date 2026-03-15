<?php

namespace Database\Seeders;

use App\Models\Meeting;
use App\Models\MeetingParticipant;
use App\Models\Organization;
use App\Models\SubscriptionPlan;
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
        // Create Super Admin (for platform management)
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@jitsi-admin.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
                'account_type' => 'single',
            ]
        );

        // Assign super-admin role to Super Admin
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        if ($superAdminRole && !$superAdmin->roles()->where('role_id', $superAdminRole->id)->exists()) {
            $superAdmin->roles()->attach($superAdminRole->id);
        }

        // Create a demo organization admin user
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'account_type' => 'organization',
            ]
        );

        // Assign org-admin role to demo admin
        $orgAdminRole = Role::where('slug', 'org-admin')->first();
        if ($orgAdminRole && !$user->roles()->where('role_id', $orgAdminRole->id)->exists()) {
            $user->roles()->attach($orgAdminRole->id);
        }

        // Create Alpha Net organization
        $alphaNet = Organization::firstOrCreate(
            ['slug' => 'alpha-net'],
            [
                'name' => 'Alpha Net',
            ]
        );

        // Create Alpha Net team members
        $teamMembers = [
            [
                'name' => 'Abu Sufian Haider',
                'email' => 'abu.haider@alpha.net.bd',
                'role' => 'admin',
                'designation' => 'Founder & Director',
            ],
            [
                'name' => 'Akramul Haider',
                'email' => 'akramul.haider@alpha.net.bd',
                'role' => 'admin',
                'designation' => 'Chief Executive Officer (CEO)',
            ],
            [
                'name' => 'Esham Haider',
                'email' => 'esham.haider@alpha.net.bd',
                'role' => 'admin',
                'designation' => 'Chief Technical Officer (CTO)',
            ],
            [
                'name' => 'Laboni Akter',
                'email' => 'laboni.akter@alpha.net.bd',
                'role' => 'member',
                'designation' => 'Chief Human Resources Officer (CHRO)',
            ],
            [
                'name' => 'Mahabur Rahman',
                'email' => 'mahabur.rahman@alpha.net.bd',
                'role' => 'member',
                'designation' => 'Dept. Head of Support',
            ],
            [
                'name' => 'Abdur Rahim',
                'email' => 'abdur.rahim@alpha.net.bd',
                'role' => 'member',
                'designation' => 'Dept. Head of Sales & Marketing',
            ],
            [
                'name' => 'Nur Nabi',
                'email' => 'nur.nabi@alpha.net.bd',
                'role' => 'member',
                'designation' => 'Dept. Head of Digital Marketing',
            ],
            [
                'name' => 'Nazmous Shakib',
                'email' => 'nazmous.shakib@alpha.net.bd',
                'role' => 'member',
                'designation' => 'Dept. Head of Training & Communication',
            ],
            [
                'name' => 'Mithun Sutradhar',
                'email' => 'mithun.sutradhar@alpha.net.bd',
                'role' => 'member',
                'designation' => 'Dept. Head of Web & Software Development',
            ],
            [
                'name' => 'Omar Faruk',
                'email' => 'omar.faruk@alpha.net.bd',
                'role' => 'member',
                'designation' => 'Dept. Head of Accounts & Finance',
            ],
        ];

        $alphaNetUsers = [];
        foreach ($teamMembers as $member) {
            $alphaUser = User::firstOrCreate(
                ['email' => $member['email']],
                [
                    'name' => $member['name'],
                    'password' => Hash::make('password'),
                    'account_type' => 'organization',
                    'organization_id' => $alphaNet->id,
                ]
            );

            // Assign appropriate role
            if ($member['role'] === 'admin') {
                $orgAdminRole = Role::where('slug', 'org-admin')->first();
                if ($orgAdminRole && !$alphaUser->roles()->where('role_id', $orgAdminRole->id)->exists()) {
                    $alphaUser->roles()->attach($orgAdminRole->id);
                }
            } else {
                $memberRole = Role::where('slug', 'member')->first();
                if ($memberRole && !$alphaUser->roles()->where('role_id', $memberRole->id)->exists()) {
                    $alphaUser->roles()->attach($memberRole->id);
                }
            }

            // Add to organization pivot table
            if (!$alphaNet->users()->where('user_id', $alphaUser->id)->exists()) {
                $alphaNet->users()->attach($alphaUser->id, ['role' => $member['role']]);
            }

            $alphaNetUsers[] = $alphaUser;
        }

        // Create Demo Organization (for compatibility with existing demo)
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

        // Create default subscription plans
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Get started with basic meeting functionality.',
                'price' => 0.00,
                'billing_cycle' => 'monthly',
                'max_users' => 10,
                'max_meeting_duration' => 40,
                'recording_storage_gb' => 0,
                'concurrent_meetings' => 1,
                'trial_days' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'description' => 'For small teams that need reliable meetings.',
                'price' => 9.99,
                'billing_cycle' => 'monthly',
                'max_users' => 25,
                'max_meeting_duration' => 120,
                'recording_storage_gb' => 5,
                'concurrent_meetings' => 3,
                'trial_days' => 14,
                'is_active' => true,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'For growing organizations with advanced needs.',
                'price' => 29.99,
                'billing_cycle' => 'monthly',
                'max_users' => 100,
                'max_meeting_duration' => null,
                'recording_storage_gb' => 50,
                'concurrent_meetings' => 10,
                'trial_days' => 14,
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Unlimited scale for large enterprises.',
                'price' => 99.99,
                'billing_cycle' => 'monthly',
                'max_users' => null,
                'max_meeting_duration' => null,
                'recording_storage_gb' => null,
                'concurrent_meetings' => null,
                'trial_days' => 30,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $planData) {
            SubscriptionPlan::firstOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );
        }

        // Assign Pro plan to Alpha Net and Free plan to Demo Organization
        $proPlan = SubscriptionPlan::where('slug', 'pro')->first();
        $freePlan = SubscriptionPlan::where('slug', 'free')->first();

        if ($proPlan && !$alphaNet->subscription_plan_id) {
            $alphaNet->subscription_plan_id = $proPlan->id;
            $alphaNet->subscription_status = 'active';
            $alphaNet->subscription_starts_at = now();
            $alphaNet->subscription_ends_at = now()->addYear();
            $alphaNet->save();
        }

        if ($freePlan && !$organization->subscription_plan_id) {
            $organization->subscription_plan_id = $freePlan->id;
            $organization->subscription_status = 'active';
            $organization->subscription_starts_at = now();
            $organization->save();
        }

        $this->command->info('Demo data created successfully!');
        $this->command->info('');
        $this->command->info('=== Super Admin (Platform Management) ===');
        $this->command->info('Email: superadmin@jitsi-admin.com');
        $this->command->info('Password: password');
        $this->command->info('Role: Super Administrator');
        $this->command->info('Access: Full platform control - manage all users, organizations, and system settings');
        $this->command->info('');
        $this->command->info('=== Demo Organization Admin ===');
        $this->command->info('Email: admin@example.com');
        $this->command->info('Password: password');
        $this->command->info('Role: Organization Admin');
        $this->command->info('');
        $this->command->info('=== Alpha Net Organization ===');
        $this->command->info('Organization: Alpha Net (https://www.alpha.net.bd/)');
        $this->command->info('All Alpha Net users have password: password');
        $this->command->info('Team members created: ' . count($alphaNetUsers));
        foreach ($teamMembers as $member) {
            $this->command->info('  - ' . $member['name'] . ' (' . $member['email'] . ') - ' . $member['designation']);
        }
        $this->command->info('');
        $this->command->info('=== Subscription Plans Created ===');
        foreach ($plans as $plan) {
            $this->command->info('  - ' . $plan['name'] . ' ($' . $plan['price'] . '/' . $plan['billing_cycle'] . ')');
        }
        $this->command->info('');
        $this->command->info('=== Meetings Created ===');
        $this->command->info('- ' . $meetingSoon->title . ' (ID: ' . $meetingSoon->id . ')');
        $this->command->info('- ' . $meetingNow->title . ' (ID: ' . $meetingNow->id . ')');
        $this->command->info('- ' . $meetingFuture->title . ' (ID: ' . $meetingFuture->id . ')');
    }
}
