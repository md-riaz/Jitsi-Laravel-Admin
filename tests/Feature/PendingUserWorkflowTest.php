<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PendingUserWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function orgAdminRole(): Role
    {
        return Role::where('slug', 'org-admin')->first()
            ?? Role::create(['name' => 'Org Admin', 'slug' => 'org-admin']);
    }

    private function memberRole(): Role
    {
        return Role::where('slug', 'member')->first()
            ?? Role::create(['name' => 'Member', 'slug' => 'member']);
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

    private function createPendingUser(Organization $org): User
    {
        $user = User::factory()->create([
            'account_type' => 'organization',
            'organization_id' => $org->id,
            'status' => 'pending',
        ]);
        $user->assignRole($this->memberRole());

        return $user;
    }

    public function test_pending_user_is_redirected_to_pending_approval_page(): void
    {
        $org = Organization::create(['name' => 'Test Org', 'slug' => 'test-org', 'is_active' => true]);
        $pendingUser = $this->createPendingUser($org);

        $response = $this->actingAs($pendingUser)->get(route('dashboard.my-meetings'));
        $response->assertRedirect(route('auth.pending-approval'));
    }

    public function test_pending_user_can_access_pending_approval_page(): void
    {
        $org = Organization::create(['name' => 'Test Org', 'slug' => 'test-org', 'is_active' => true]);
        $pendingUser = $this->createPendingUser($org);

        $response = $this->actingAs($pendingUser)->get(route('auth.pending-approval'));
        $response->assertOk();
        $response->assertSee('Pending Approval');
    }

    public function test_org_admin_can_approve_pending_user(): void
    {
        $org = Organization::create(['name' => 'Test Org', 'slug' => 'test-org', 'is_active' => true]);
        $admin = $this->createOrgAdmin($org);
        $pendingUser = $this->createPendingUser($org);

        $response = $this->actingAs($admin)
            ->post(route('dashboard.pending-users.approve', $pendingUser->id));

        $response->assertRedirect(route('dashboard.pending-users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $pendingUser->id,
            'status' => 'active',
        ]);
    }

    public function test_org_admin_can_reject_pending_user(): void
    {
        $org = Organization::create(['name' => 'Test Org', 'slug' => 'test-org', 'is_active' => true]);
        $admin = $this->createOrgAdmin($org);
        $pendingUser = $this->createPendingUser($org);
        $org->users()->attach($pendingUser->id, ['role' => 'member']);

        $response = $this->actingAs($admin)
            ->delete(route('dashboard.pending-users.reject', $pendingUser->id));

        $response->assertRedirect(route('dashboard.pending-users.index'));

        $this->assertDatabaseMissing('users', ['id' => $pendingUser->id]);
    }

    public function test_active_user_is_not_forced_to_pending_page(): void
    {
        $org = Organization::create(['name' => 'Test Org', 'slug' => 'test-org', 'is_active' => true]);

        $activeUser = User::factory()->create([
            'account_type' => 'organization',
            'organization_id' => $org->id,
            'status' => 'active',
        ]);
        $activeUser->assignRole($this->memberRole());

        $this->assertFalse($activeUser->isPending());
        $this->assertTrue($activeUser->isActive());
    }

    public function test_org_admin_can_view_pending_users_list(): void
    {
        $org = Organization::create(['name' => 'Test Org', 'slug' => 'test-org', 'is_active' => true]);
        $admin = $this->createOrgAdmin($org);
        $pendingUser = $this->createPendingUser($org);

        $response = $this->actingAs($admin)->get(route('dashboard.pending-users.index'));
        $response->assertOk();
        $response->assertSee($pendingUser->name);
    }

    public function test_public_registration_with_organization_type_creates_pending_user(): void
    {
        $org = Organization::create(['name' => 'Test Org', 'slug' => 'test-org', 'is_active' => true]);
        $this->memberRole();

        $response = $this->post(route('register.submit'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'account_type' => 'organization',
            'organization_id' => $org->id,
        ]);

        $response->assertRedirect(route('auth.pending-approval'));

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'status' => 'pending',
            'organization_id' => $org->id,
        ]);
    }

    public function test_public_registration_with_personal_type_creates_active_user(): void
    {
        $response = $this->post(route('register.submit'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'account_type' => 'single',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'status' => 'active',
        ]);
    }
}
