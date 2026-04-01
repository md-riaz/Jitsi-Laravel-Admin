<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeamOwnerProtectionTest extends TestCase
{
    use RefreshDatabase;

    private function seedRoles(): void
    {
        Role::firstOrCreate(['slug' => 'org-admin'], ['name' => 'Org Admin']);
        Role::firstOrCreate(['slug' => 'member'], ['name' => 'Member']);
        Role::firstOrCreate(['slug' => 'host'], ['name' => 'Host']);
    }

    private function createOrganizationWithOwnerAndAdmin(): array
    {
        $this->seedRoles();

        $organization = Organization::create([
            'name' => 'Owner Protected Org',
            'slug' => 'owner-protected-org',
            'is_active' => true,
        ]);

        $owner = User::factory()->create([
            'name' => 'Org Owner',
            'email' => 'owner@[email].com',
            'account_type' => 'organization',
            'status' => 'active',
            'organization_id' => $organization->id,
            'password' => Hash::make('password123'),
        ]);

        $admin = User::factory()->create([
            'name' => 'Peer Admin',
            'email' => 'peer-admin@[email].com',
            'account_type' => 'organization',
            'status' => 'active',
            'organization_id' => $organization->id,
            'password' => Hash::make('password123'),
        ]);

        $owner->assignRole(Role::where('slug', 'org-admin')->firstOrFail());
        $admin->assignRole(Role::where('slug', 'org-admin')->firstOrFail());

        $organization->users()->attach($owner->id, ['role' => 'admin']);
        $organization->users()->attach($admin->id, ['role' => 'admin']);

        $organization->owner_id = $owner->id;
        $organization->save();

        return [$organization, $owner, $admin];
    }

    /** @test */
    public function org_admin_cannot_impersonate_organization_owner(): void
    {
        [, $owner, $admin] = $this->createOrganizationWithOwnerAndAdmin();

        $this->actingAs($admin)
            ->from(route('dashboard.team.index'))
            ->post(route('dashboard.team.login-as', $owner->id))
            ->assertRedirect(route('dashboard.team.index'))
            ->assertSessionHas('error', 'The organization owner account cannot be managed by another admin.');

        $this->assertAuthenticatedAs($admin);
    }

    /** @test */
    public function org_admin_cannot_suspend_organization_owner(): void
    {
        [, $owner, $admin] = $this->createOrganizationWithOwnerAndAdmin();

        $this->actingAs($admin)
            ->from(route('dashboard.team.index'))
            ->post(route('dashboard.team.suspend', $owner->id), ['reason' => 'unauthorized'])
            ->assertRedirect(route('dashboard.team.index'))
            ->assertSessionHas('error', 'The organization owner account cannot be managed by another admin.');
    }

    /** @test */
    public function org_admin_cannot_remove_organization_owner(): void
    {
        [$organization, $owner, $admin] = $this->createOrganizationWithOwnerAndAdmin();

        $this->actingAs($admin)
            ->from(route('dashboard.team.index'))
            ->delete(route('dashboard.team.destroy', $owner->id))
            ->assertRedirect(route('dashboard.team.index'))
            ->assertSessionHas('error', 'The organization owner account cannot be managed by another admin.');

        $this->assertDatabaseHas('organization_user', [
            'organization_id' => $organization->id,
            'user_id' => $owner->id,
            'role' => 'admin',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $owner->id,
            'organization_id' => $organization->id,
            'account_type' => 'organization',
        ]);
    }
}
