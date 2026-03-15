<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamUserListTest extends TestCase
{
    use RefreshDatabase;

    private function orgAdminRole(): Role
    {
        return Role::firstOrCreate(['slug' => 'org-admin'], ['name' => 'Org Admin']);
    }

    private function memberRole(): Role
    {
        return Role::firstOrCreate(['slug' => 'member'], ['name' => 'Member']);
    }

    private function createOrg(): Organization
    {
        return Organization::create(['name' => 'Test Org', 'slug' => 'test-org', 'is_active' => true]);
    }

    private function createOrgAdmin(Organization $org): User
    {
        $admin = User::factory()->create([
            'account_type' => 'organization',
            'organization_id' => $org->id,
            'status' => 'active',
        ]);
        $admin->assignRole($this->orgAdminRole());

        return $admin;
    }

    /** @test */
    public function org_admin_sees_users_created_via_team_controller(): void
    {
        $org = $this->createOrg();
        $admin = $this->createOrgAdmin($org);

        // Member created via org admin (organization_id FK + pivot)
        $member = User::factory()->create([
            'account_type' => 'organization',
            'organization_id' => $org->id,
            'status' => 'active',
        ]);
        $member->assignRole($this->memberRole());
        $org->users()->attach($member->id, ['role' => 'member']);

        $this->actingAs($admin)
             ->get(route('dashboard.team.index'))
             ->assertStatus(200)
             ->assertSee($member->name)
             ->assertSee($member->email);
    }

    /** @test */
    public function org_admin_sees_users_assigned_via_super_admin_fk_only(): void
    {
        $org = $this->createOrg();
        $admin = $this->createOrgAdmin($org);

        // Simulate super admin assigning a user via FK only (no pivot entry)
        $user = User::factory()->create([
            'account_type' => 'organization',
            'organization_id' => $org->id,
            'status' => 'active',
        ]);
        $user->assignRole($this->memberRole());
        // Deliberately NOT attaching to pivot — super admin path

        $this->actingAs($admin)
             ->get(route('dashboard.team.index'))
             ->assertStatus(200)
             ->assertSee($user->name)
             ->assertSee($user->email);
    }

    /** @test */
    public function org_admin_does_not_see_users_from_other_orgs(): void
    {
        $org = $this->createOrg();
        $admin = $this->createOrgAdmin($org);

        $otherOrg = Organization::create(['name' => 'Other Org', 'slug' => 'other-org', 'is_active' => true]);
        $stranger = User::factory()->create([
            'account_type' => 'organization',
            'organization_id' => $otherOrg->id,
            'status' => 'active',
        ]);

        $this->actingAs($admin)
             ->get(route('dashboard.team.index'))
             ->assertStatus(200)
             ->assertDontSee($stranger->email);
    }

    /** @test */
    public function team_list_shows_pending_status_badge(): void
    {
        $org = $this->createOrg();
        $admin = $this->createOrgAdmin($org);

        $pending = User::factory()->create([
            'account_type' => 'organization',
            'organization_id' => $org->id,
            'status' => 'pending',
        ]);
        $pending->assignRole($this->memberRole());

        $this->actingAs($admin)
             ->get(route('dashboard.team.index'))
             ->assertStatus(200)
             ->assertSee($pending->name)
             ->assertSee('Pending');
    }

    /** @test */
    public function team_list_status_filter_works(): void
    {
        $org = $this->createOrg();
        $admin = $this->createOrgAdmin($org);

        $active = User::factory()->create([
            'name' => 'Active Member',
            'account_type' => 'organization',
            'organization_id' => $org->id,
            'status' => 'active',
        ]);
        $active->assignRole($this->memberRole());

        $pending = User::factory()->create([
            'name' => 'Pending Member',
            'account_type' => 'organization',
            'organization_id' => $org->id,
            'status' => 'pending',
        ]);
        $pending->assignRole($this->memberRole());

        // Filter by active — only active member shows
        $this->actingAs($admin)
             ->get(route('dashboard.team.index', ['status' => 'active']))
             ->assertSee('Active Member')
             ->assertDontSee('Pending Member');

        // Filter by pending — only pending member shows
        $this->actingAs($admin)
             ->get(route('dashboard.team.index', ['status' => 'pending']))
             ->assertSee('Pending Member')
             ->assertDontSee('Active Member');
    }
}
