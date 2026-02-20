<?php

namespace Tests\Feature;

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_page_requires_authentication(): void
    {
        $response = $this->get(route('dashboard.calendar'));
        $response->assertRedirect();
    }

    public function test_authenticated_user_can_view_calendar(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard.calendar'));
        $response->assertOk();
        $response->assertViewIs('dashboard.calendar');
    }

    public function test_calendar_events_endpoint_returns_user_meetings(): void
    {
        $user = User::factory()->create();

        // Create a meeting for the user
        $meeting = Meeting::create([
            'created_by' => $user->id,
            'title' => 'Test Meeting',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'timezone' => 'UTC',
            'status' => 'scheduled',
            'visibility' => 'invite_only',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard.calendar.events'));

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment([
            'title' => 'Test Meeting',
        ]);
    }

    public function test_calendar_events_includes_instant_meetings(): void
    {
        $user = User::factory()->create();

        // Create an instant meeting (no start/end times)
        $meeting = Meeting::create([
            'created_by' => $user->id,
            'title' => 'Instant Meeting',
            'start_at' => null,
            'end_at' => null,
            'timezone' => 'UTC',
            'status' => 'live',
            'visibility' => 'invite_only',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard.calendar.events'));

        $response->assertOk();
        $data = $response->json();

        $this->assertCount(1, $data);
        $this->assertEquals('Instant Meeting', $data[0]['title']);
        $this->assertTrue($data[0]['extendedProps']['isInstant']);
    }

    public function test_download_ics_file_for_meeting(): void
    {
        $user = User::factory()->create();

        $meeting = Meeting::create([
            'created_by' => $user->id,
            'title' => 'Test Meeting',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'timezone' => 'UTC',
            'visibility' => 'invite_only',
        ]);

        $response = $this->get(route('meeting.download-ics', $meeting->id));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        $response->assertHeader('Content-Disposition');

        $content = $response->getContent();
        $this->assertStringContainsString('BEGIN:VCALENDAR', $content);
        $this->assertStringContainsString('Test Meeting', $content);
        $this->assertStringContainsString('END:VCALENDAR', $content);
    }

    public function test_calendar_service_handles_instant_meetings(): void
    {
        $user = User::factory()->create();

        // Create instant meeting
        $meeting = Meeting::create([
            'created_by' => $user->id,
            'title' => 'Instant Meeting',
            'start_at' => null,
            'end_at' => null,
            'timezone' => 'UTC',
            'status' => 'live',
            'visibility' => 'invite_only',
        ]);

        $calendarService = app(\App\Services\CalendarService::class);
        $icsContent = $calendarService->generateIcs($meeting);

        $this->assertStringContainsString('BEGIN:VCALENDAR', $icsContent);
        $this->assertStringContainsString('Instant Meeting', $icsContent);
        $this->assertStringNotContainsString('BEGIN:VALARM', $icsContent); // No alarm for instant meetings
    }
}
