<?php

namespace Tests\Feature;

use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_detects_subscription_expiring_soon(): void
    {
        $org = Organization::create([
            'name' => 'Test Org',
            'slug' => 'test-org',
            'is_active' => true,
            'billing_notification_days' => 5,
            'subscription_ends_at' => now()->addDays(3),
            'subscription_status' => 'active',
        ]);

        $this->assertTrue($org->isSubscriptionExpiringSoon());
        $this->assertFalse($org->isSubscriptionExpired());
    }

    public function test_organization_detects_subscription_not_expiring_soon(): void
    {
        $org = Organization::create([
            'name' => 'Test Org',
            'slug' => 'test-org',
            'is_active' => true,
            'billing_notification_days' => 5,
            'subscription_ends_at' => now()->addDays(30),
            'subscription_status' => 'active',
        ]);

        $this->assertFalse($org->isSubscriptionExpiringSoon());
        $this->assertFalse($org->isSubscriptionExpired());
    }

    public function test_organization_detects_expired_subscription(): void
    {
        $org = Organization::create([
            'name' => 'Test Org',
            'slug' => 'test-org',
            'is_active' => true,
            'billing_notification_days' => 5,
            'subscription_ends_at' => now()->subDays(2),
            'subscription_status' => 'expired',
        ]);

        $this->assertFalse($org->isSubscriptionExpiringSoon());
        $this->assertTrue($org->isSubscriptionExpired());
    }

    public function test_organization_without_subscription_end_date_does_not_trigger_notification(): void
    {
        $org = Organization::create([
            'name' => 'Test Org',
            'slug' => 'test-org',
            'is_active' => true,
        ]);

        $this->assertFalse($org->isSubscriptionExpiringSoon());
        $this->assertFalse($org->isSubscriptionExpired());
    }

    public function test_billing_notification_days_default_is_five(): void
    {
        $org = Organization::create([
            'name' => 'Test Org',
            'slug' => 'test-org',
        ]);

        $this->assertEquals(5, $org->billing_notification_days);
    }
}
