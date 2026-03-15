<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\SubscriptionPlan;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionPlanTest extends TestCase
{
    use RefreshDatabase;

    private function freePlan(): SubscriptionPlan
    {
        return SubscriptionPlan::firstOrCreate(
            ['slug' => 'free'],
            [
                'name' => 'Free',
                'price' => 0.00,
                'billing_cycle' => 'monthly',
                'max_users' => 10,
                'max_meeting_duration' => 40,
                'recording_storage_gb' => 0,
                'concurrent_meetings' => 1,
                'trial_days' => 0,
                'is_active' => true,
            ]
        );
    }

    private function proPlan(): SubscriptionPlan
    {
        return SubscriptionPlan::firstOrCreate(
            ['slug' => 'pro'],
            [
                'name' => 'Pro',
                'price' => 29.99,
                'billing_cycle' => 'monthly',
                'is_active' => true,
            ]
        );
    }

    private function hostRole(): Role
    {
        return Role::where('slug', 'host')->first()
            ?? Role::create(['name' => 'Host', 'slug' => 'host']);
    }

    // -----------------------------------------------------------------------
    // Registration: personal account gets Free Plan
    // -----------------------------------------------------------------------

    public function test_personal_registration_auto_assigns_free_plan(): void
    {
        $freePlan = $this->freePlan();
        $this->hostRole();

        $response = $this->post(route('register.submit'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'account_type' => 'single',
        ]);

        $response->assertRedirect(route('dashboard.my-meetings'));

        $user = User::where('email', 'john@example.com')->firstOrFail();
        $this->assertEquals($freePlan->id, $user->subscription_plan_id);
    }

    public function test_organization_registration_does_not_set_user_plan(): void
    {
        $org = Organization::create(['name' => 'Test Org', 'slug' => 'test-org', 'is_active' => true]);
        Role::firstOrCreate(['slug' => 'member'], ['name' => 'Member']);

        $this->post(route('register.submit'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'account_type' => 'organization',
            'organization_id' => $org->id,
        ]);

        $user = User::where('email', 'jane@example.com')->firstOrFail();
        // Org users inherit their plan from the org, not from personal field
        $this->assertNull($user->subscription_plan_id);
    }

    // -----------------------------------------------------------------------
    // getEffectiveSubscriptionPlan()
    // -----------------------------------------------------------------------

    public function test_personal_user_effective_plan_is_own_plan(): void
    {
        $freePlan = $this->freePlan();

        $user = User::factory()->create([
            'account_type' => 'single',
            'subscription_plan_id' => $freePlan->id,
        ]);

        $this->assertEquals($freePlan->id, $user->getEffectiveSubscriptionPlan()?->id);
    }

    public function test_org_user_effective_plan_comes_from_organization(): void
    {
        $proPlan = $this->proPlan();

        $org = Organization::create([
            'name' => 'Test Org',
            'slug' => 'test-org',
            'is_active' => true,
            'subscription_plan_id' => $proPlan->id,
        ]);

        $user = User::factory()->create([
            'account_type' => 'organization',
            'organization_id' => $org->id,
            'subscription_plan_id' => null,
        ]);

        $effective = $user->getEffectiveSubscriptionPlan();
        $this->assertNotNull($effective);
        $this->assertEquals($proPlan->id, $effective->id);
    }

    public function test_user_with_no_plan_returns_null(): void
    {
        $user = User::factory()->create([
            'account_type' => 'single',
            'subscription_plan_id' => null,
        ]);

        $this->assertNull($user->getEffectiveSubscriptionPlan());
    }

    // -----------------------------------------------------------------------
    // My Subscription page
    // -----------------------------------------------------------------------

    public function test_authenticated_user_can_view_subscription_page(): void
    {
        $freePlan = $this->freePlan();

        $user = User::factory()->create([
            'account_type' => 'single',
            'subscription_plan_id' => $freePlan->id,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard.subscription'));
        $response->assertOk();
        $response->assertSee('Free');
    }

    public function test_subscription_page_shows_upgrade_notice(): void
    {
        $freePlan = $this->freePlan();

        $user = User::factory()->create([
            'account_type' => 'single',
            'subscription_plan_id' => $freePlan->id,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard.subscription'));
        $response->assertSee('Contact Sales');
    }

    public function test_org_user_subscription_page_shows_org_plan(): void
    {
        $proPlan = $this->proPlan();

        $org = Organization::create([
            'name' => 'Test Org',
            'slug' => 'test-org',
            'is_active' => true,
            'subscription_plan_id' => $proPlan->id,
        ]);

        $user = User::factory()->create([
            'account_type' => 'organization',
            'organization_id' => $org->id,
            'subscription_plan_id' => null,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard.subscription'));
        $response->assertOk();
        $response->assertSee('Pro');
    }

    public function test_registration_page_shows_free_plan_info(): void
    {
        $this->freePlan();

        $response = $this->get(route('register'));
        $response->assertOk();
        $response->assertSee('Free');
    }
}
