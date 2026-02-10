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
