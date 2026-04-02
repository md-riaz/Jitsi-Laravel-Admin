<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class OrganizationAccountOnboardingPreservationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Validates: Requirements 3.1
     *
     * @dataProvider superAdminAccountProvider
     */
    public function test_super_admin_accounts_may_remain_organization_less(string $accountType): void
    {
        $superAdmin = User::factory()->create([
            'account_type' => $accountType,
            'organization_id' => null,
            'status' => 'active',
        ]);
        $superAdmin->assignRole($this->role('super-admin', 'Super Admin'));

        $response = $this->actingAs($superAdmin)->get(route('dashboard.team.create'));

        $this->assertNull($superAdmin->fresh()->organization_id);
        $this->assertTrue($superAdmin->hasRole('super-admin'));
        $response->assertOk();
    }

    /**
     * Validates: Requirements 3.2
     *
     * @dataProvider organizationScopedRoleProvider
     */
    public function test_organization_scoped_non_super_admin_accounts_remain_organization_associated(string $roleSlug, string $roleName): void
    {
        $organization = $this->createOrganization('preserved-org');

        $user = User::factory()->create([
            'account_type' => 'organization',
            'organization_id' => $organization->id,
            'status' => 'active',
        ]);
        $user->assignRole($this->role($roleSlug, $roleName));

        $user = $user->fresh('organization', 'roles');

        $this->assertTrue($user->isOrganizationUser());
        $this->assertSame($organization->id, $user->organization_id);
        $this->assertSame($organization->id, $user->organization?->id);
        $this->assertFalse($user->hasRole('super-admin'));
    }

    /**
     * Validates: Requirements 3.3, 3.4
     */
    public function test_team_management_routes_remain_guarded_by_org_admin_or_super_admin_middleware(): void
    {
        $route = Route::getRoutes()->getByName('dashboard.team.store');

        $this->assertNotNull($route);
        $this->assertContains('auth', $route->gatherMiddleware());
        $this->assertContains('org-admin-or-super-admin', $route->gatherMiddleware());
    }

    /**
     * Validates: Requirements 3.3, 3.4
     */
    public function test_tyro_dashboard_admin_flow_keeps_super_admin_and_org_admin_patterns(): void
    {
        $organization = $this->createOrganization('tyro-admin-org');
        $orgAdmin = User::factory()->create([
            'account_type' => 'organization',
            'organization_id' => $organization->id,
            'status' => 'active',
        ]);
        $orgAdmin->assignRole($this->role('org-admin', 'Org Admin'));

        $member = User::factory()->create([
            'account_type' => 'organization',
            'organization_id' => $organization->id,
            'status' => 'active',
        ]);
        $member->assignRole($this->role('member', 'Member'));

        $host = User::factory()->create([
            'account_type' => 'organization',
            'organization_id' => $organization->id,
            'status' => 'active',
        ]);
        $host->assignRole($this->role('host', 'Host'));

        $this->actingAs($orgAdmin)
            ->get(route('tyro-dashboard.index'))
            ->assertOk();

        $this->actingAs($member)
            ->get(route('tyro-dashboard.index'))
            ->assertRedirect(route('dashboard.my-meetings'));

        $this->actingAs($host)
            ->get(route('tyro-dashboard.index'))
            ->assertRedirect(route('dashboard.my-meetings'));

        $this->assertSame(['super-admin', 'org-admin'], config('tyro-dashboard.admin_roles'));
        $this->assertSame(Organization::class, config('tyro-dashboard.resources.organizations.model'));
    }

    public static function superAdminAccountProvider(): array
    {
        return [
            'single account super admin' => ['single'],
        ];
    }

    public static function organizationScopedRoleProvider(): array
    {
        return [
            'org admin remains organization scoped' => ['org-admin', 'Org Admin'],
        ];
    }

    private function createOrganization(string $slug): Organization
    {
        return Organization::create([
            'name' => ucwords(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'is_active' => true,
        ]);
    }

    private function role(string $slug, string $name): Role
    {
        return Role::firstOrCreate(['slug' => $slug], ['name' => $name]);
    }
}
