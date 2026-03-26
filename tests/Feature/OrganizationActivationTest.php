<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationActivationTest extends TestCase
{
    use RefreshDatabase;

    private function createOrgAdmin(Organization $org): User
    {
        $admin = User::factory()->create([
            'account_type' => 'organization',
            'organization_id' => $org->id,
            'status' => 'active',
        ]);

        $role = Role::where('slug', 'org-admin')->first()
            ?? Role::create(['name' => 'Org Admin', 'slug' => 'org-admin']);
        $admin->assignRole($role);

        return $admin;
    }

    public function test_users_in_active_organization_can_access_dashboard(): void
    {
        $org = Organization::create(['name' => 'Active Org', 'slug' => 'active-org', 'is_active' => true]);
        $admin = $this->createOrgAdmin($org);

        $response = $this->actingAs($admin)->get(route('tyro-dashboard.index'));

        $response->assertSuccessful();
    }

    public function test_users_in_deactivated_organization_are_logged_out(): void
    {
        $org = Organization::create(['name' => 'Inactive Org', 'slug' => 'inactive-org', 'is_active' => false]);
        $admin = $this->createOrgAdmin($org);

        $response = $this->actingAs($admin)->get(route('tyro-dashboard.index'));

        // Should be redirected away (not 200)
        $response->assertRedirect();

        // The session should be invalidated – follow redirect to login
        $followedResponse = $this->get($response->headers->get('Location'));
        $followedResponse->assertSee('Your account has an issue');
    }

    public function test_super_admin_is_never_blocked_by_org_check(): void
    {
        $superAdmin = User::factory()->create(['status' => 'active']);
        $role = Role::where('slug', 'super-admin')->first()
            ?? Role::create(['name' => 'Super Admin', 'slug' => 'super-admin']);
        $superAdmin->assignRole($role);

        $response = $this->actingAs($superAdmin)->get(route('tyro-dashboard.index'));

        $response->assertSuccessful();
    }
}
