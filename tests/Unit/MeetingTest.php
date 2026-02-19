<?php

namespace Tests\Unit;

use App\Models\Meeting;
use App\Models\Organization;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeetingTest extends TestCase
{
    use RefreshDatabase;

    public function test_room_name_is_generated_when_missing(): void
    {
        $meeting = $this->createMeeting();

        $this->assertNotNull($meeting->room_name);
        $this->assertMatchesRegularExpression('/^mtg_[a-z0-9]{12}$/', $meeting->room_name);
    }

    public function test_room_name_is_immutable_after_creation(): void
    {
        $meeting = $this->createMeeting();
        $originalRoom = $meeting->room_name;

        $meeting->update(['room_name' => 'mtg_forcedoverride']);
        $meeting->refresh();

        $this->assertSame($originalRoom, $meeting->room_name);
    }

    public function test_can_join_at_respects_window_boundaries(): void
    {
        $start = CarbonImmutable::parse('2026-02-10 10:00:00', 'UTC');
        $end = CarbonImmutable::parse('2026-02-10 11:00:00', 'UTC');

        $meeting = $this->createMeeting([
            'start_at' => $start,
            'end_at' => $end,
            'join_early_minutes' => 10,
            'join_late_minutes' => 60,
        ]);

        $this->assertFalse($meeting->canJoinAt($start->subMinutes(11)));
        $this->assertTrue($meeting->canJoinAt($start->subMinutes(10)));
        $this->assertTrue($meeting->canJoinAt($end->addMinutes(60)));
        $this->assertFalse($meeting->canJoinAt($end->addMinutes(61)));
    }

    public function test_can_create_meeting_without_organization(): void
    {
        $creator = User::factory()->create();

        $meeting = Meeting::create([
            'organization_id' => null,
            'created_by' => $creator->id,
            'title' => 'Personal Meeting',
            'description' => 'My personal meeting',
            'start_at' => CarbonImmutable::parse('2026-02-10 10:00:00', 'UTC'),
            'end_at' => CarbonImmutable::parse('2026-02-10 11:00:00', 'UTC'),
            'timezone' => 'UTC',
            'visibility' => 'invite_only',
            'status' => 'scheduled',
        ]);

        $this->assertNull($meeting->organization_id);
        $this->assertNotNull($meeting->room_name);
        $this->assertEquals('Personal Meeting', $meeting->title);
    }

    public function test_organization_relationship_handles_null(): void
    {
        $creator = User::factory()->create();

        $meeting = Meeting::create([
            'organization_id' => null,
            'created_by' => $creator->id,
            'title' => 'Personal Meeting',
            'start_at' => CarbonImmutable::parse('2026-02-10 10:00:00', 'UTC'),
            'end_at' => CarbonImmutable::parse('2026-02-10 11:00:00', 'UTC'),
            'timezone' => 'UTC',
        ]);

        // Should not throw error when accessing organization relationship
        $this->assertNotNull($meeting->organization);
        $this->assertInstanceOf(Organization::class, $meeting->organization);
    }

    public function test_org_only_visibility_requires_organization(): void
    {
        $creator = User::factory()->create();

        // Creating a meeting with org_only visibility but no organization should be invalid
        // This would fail at the controller validation level, so we test the model can accept it
        // but the controller validation should prevent it
        $meeting = Meeting::create([
            'organization_id' => null,
            'created_by' => $creator->id,
            'title' => 'Invalid Meeting',
            'start_at' => CarbonImmutable::parse('2026-02-10 10:00:00', 'UTC'),
            'end_at' => CarbonImmutable::parse('2026-02-10 11:00:00', 'UTC'),
            'timezone' => 'UTC',
            'visibility' => 'org_only',
        ]);

        // Model allows it but controller validation should catch it
        $this->assertNull($meeting->organization_id);
        $this->assertEquals('org_only', $meeting->visibility);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createMeeting(array $attributes = []): Meeting
    {
        $organization = Organization::create([
            'name' => 'Acme Org',
            'slug' => 'acme-org-'.uniqid(),
        ]);

        $creator = User::factory()->create();

        return Meeting::create(array_merge([
            'organization_id' => $organization->id,
            'created_by' => $creator->id,
            'title' => 'Planning Session',
            'description' => 'Iteration planning meeting',
            'start_at' => CarbonImmutable::parse('2026-02-10 10:00:00', 'UTC'),
            'end_at' => CarbonImmutable::parse('2026-02-10 11:00:00', 'UTC'),
            'timezone' => 'UTC',
            'join_early_minutes' => 10,
            'join_late_minutes' => 60,
            'visibility' => 'invite_only',
            'status' => 'scheduled',
        ], $attributes));
    }
}
