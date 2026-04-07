<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class DefaultSubscriptionPlansSeeder extends Seeder
{
    /**
     * Seed the application's default subscription plans.
     */
    public function run(): void
    {
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
            SubscriptionPlan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData,
            );
        }
    }
}
