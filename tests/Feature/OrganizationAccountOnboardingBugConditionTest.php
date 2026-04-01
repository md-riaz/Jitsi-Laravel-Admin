<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Throwable;
use Tests\TestCase;

class OrganizationAccountOnboardingBugConditionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Validates: Requirements 1.1, 1.2, 1.3, 1.4
     *
     * This exploration property intentionally encodes the fixed invariant.
     * It is expected to fail on unfixed code and surface a concrete counterexample.
     *
     * @dataProvider nonSuperAdminOnboardingActorsProvider
     */
    public function test_non_super_admin_onboarding_always_creates_an_organization_and_links_the_initial_user(string $actor, string $targetRole): void
    {
        $result = $actor === 'public_registration'
            ? $this->runPublicRegistrationScenario()
            : $this->runSuperAdminProvisioningScenario($targetRole);

        $counterexample = $this->counterexampleFor($actor, $targetRole, $result);

        $this->assertTrue(
            $result['user_created'],
            sprintf(
                'Actor [%s] with target role [%s] did not create a user during onboarding. Result: %s',
                $actor,
                $targetRole,
                json_encode($counterexample, JSON_THROW_ON_ERROR)
            )
        );

        $this->assertTrue(
            $result['organization_created'],
            sprintf(
                'Actor [%s] with target role [%s] created a user without creating an organization. Result: %s',
                $actor,
                $targetRole,
                json_encode($counterexample, JSON_THROW_ON_ERROR)
            )
        );

        $this->assertNotNull(
            $result['user']?->organization_id,
            sprintf(
                'Actor [%s] with target role [%s] created a user without organization_id. Result: %s',
                $actor,
                $targetRole,
                json_encode($counterexample, JSON_THROW_ON_ERROR)
            )
        );
    }

    public static function nonSuperAdminOnboardingActorsProvider(): array
    {
        return [
            'public signup single-account path' => ['public_registration', 'host'],
        ];
    }

    private function runPublicRegistrationScenario(): array
    {
        Role::firstOrCreate(['slug' => 'host'], ['name' => 'Host']);
        Role::firstOrCreate(['slug' => 'org-admin'], ['name' => 'Org Admin']);

        $email = 'public-bug@example.com';

        $response = $this->post(route('register.submit'), [
            'name' => 'Public Bug',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'account_type' => 'organization',
            'organization_name' => 'Test Org',
        ]);
        $user = User::where('email', $email)->first();
        $validationErrors = null;

        if ($response->getSession() && $response->getSession()->has('errors')) {
            $validationErrors = $response->getSession()->get('errors')->all();
        }

        return [
            'response_status' => $response->getStatusCode(),
            'user_created' => $user !== null,
            'organization_created' => Organization::count() > 0,
            'user' => $user,
            'exception' => null,
            'validation_errors' => $validationErrors,
        ];
    }

    private function runSuperAdminProvisioningScenario(string $targetRole): array
    {
        $this->seedProvisioningRoles();

        $superAdmin = User::factory()->create([
            'account_type' => 'single',
            'organization_id' => null,
            'status' => 'active',
            'password' => Hash::make('password123'),
        ]);
        $role = Role::firstOrCreate(['slug' => 'super-admin'], ['name' => 'Super Admin']);
        $superAdmin->roles()->syncWithoutDetaching([$role->id]);

        $email = 'super-admin-bug@example.com';
        $exception = null;

        try {
            $response = $this->actingAs($superAdmin)->post(route('dashboard.team.store'), [
                'name' => 'Provisioned Bug',
                'email' => $email,
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => $targetRole,
            ]);
        } catch (Throwable $throwable) {
            $exception = $throwable;
            $response = null;
        }

        $user = User::where('email', $email)->first();

        return [
            'response_status' => $response?->getStatusCode(),
            'user_created' => $user !== null,
            'organization_created' => Organization::count() > 0,
            'user' => $user,
            'exception' => $exception,
        ];
    }

    private function seedProvisioningRoles(): void
    {
        Role::firstOrCreate(['slug' => 'super-admin'], ['name' => 'Super Admin']);
        Role::firstOrCreate(['slug' => 'org-admin'], ['name' => 'Org Admin']);
        Role::firstOrCreate(['slug' => 'member'], ['name' => 'Member']);
        Role::firstOrCreate(['slug' => 'host'], ['name' => 'Host']);
    }

    private function counterexampleFor(string $actor, string $targetRole, array $result): array
    {
        return [
            'actor' => $actor,
            'target_role' => $targetRole,
            'user_created' => $result['user_created'],
            'organization_created' => $result['organization_created'],
            'user_account_type' => $result['user']?->account_type,
            'user_organization_id' => $result['user']?->organization_id,
            'response_status' => $result['response_status'],
            'exception' => $result['exception']?->getMessage(),
            'validation_errors' => $result['validation_errors'] ?? null,
        ];
    }
}
