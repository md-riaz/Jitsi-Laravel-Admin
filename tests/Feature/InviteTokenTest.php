<?php

namespace Tests\Feature;

use App\Models\Meeting;
use App\Models\MeetingInvite;
use App\Models\Organization;
use App\Services\MeetingInviteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InviteTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_invite_token_lookup_is_fast_and_correct()
    {
        $org = Organization::create(['name' => 'Test Org', 'slug' => 'test-org']);
        $meeting = Meeting::create([
            'title' => 'Test Meeting',
            'organization_id' => $org->id,
            'timezone' => 'UTC',
            'status' => 'scheduled',
            'visibility' => 'invite_only',
        ]);

        $service = new MeetingInviteService();
        $result = $service->createInvite($meeting, 'test@example.com', false);
        $token = $result['token'];
        $invite = $result['invite'];

        // Verify token_lookup was stored
        $this->assertNotNull($invite->token_lookup);
        $this->assertEquals(hash('sha256', $token), $invite->token_lookup);

        // Verify validation works
        $validatedInvite = $service->validateInvite($token);
        $this->assertNotNull($validatedInvite);
        $this->assertEquals($invite->id, $validatedInvite->id);

        // Verify invalid token fails
        $this->assertNull($service->validateInvite('invalid-token'));
    }
}
