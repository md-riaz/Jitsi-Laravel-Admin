<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Services\MeetingInviteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InviteController extends Controller
{
    public function __construct(
        private readonly MeetingInviteService $inviteService,
        private readonly MeetingController $meetingController
    ) {}

    public function resolve(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => 'required|string',
        ]);

        $invite = $this->inviteService->validateInvite($data['token']);
        if (!$invite) {
            return response()->json([
                'ok' => false,
                'error_code' => 'ERR_INVITE_EXPIRED',
                'message' => 'Invitation is invalid or expired',
            ], 404);
        }

        $meeting = Meeting::find($invite->meeting_id);
        if (!$meeting) {
            return response()->json([
                'ok' => false,
                'error_code' => 'ERR_MEETING_NOT_FOUND',
                'message' => 'Meeting not found',
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'data' => [
                'token' => $invite->token,
                'meeting' => [
                    'id' => $meeting->id,
                    'title' => $meeting->title,
                    'visibility' => Meeting::normalizeVisibility($meeting->visibility),
                    'allow_guests' => (bool) $meeting->allow_guests,
                    'lobby_enabled' => (bool) $meeting->lobby_enabled,
                ],
            ],
        ]);
    }

    public function accept(Request $request, string $token): JsonResponse
    {
        $invite = $this->inviteService->validateInvite($token);
        if (!$invite) {
            return response()->json([
                'ok' => false,
                'error_code' => 'ERR_INVITE_EXPIRED',
                'message' => 'Invitation is invalid or expired',
            ], 404);
        }

        $request->validate([
            'name' => 'nullable|string|max:120',
            'email' => 'nullable|email',
        ]);

        // API-first flow: client sends invite_token/name/email in join-guest request.
        // We intentionally avoid web session coupling here.

        return response()->json([
            'ok' => true,
            'data' => [
                'invite_token' => $token,
                'message' => 'Invitation accepted',
            ],
        ]);
    }

    public function joinGuest(Request $request, Meeting $meeting): JsonResponse
    {
        // Reuse same join policy/logic while user is unauthenticated; invite token handled via session.
        return $this->meetingController->join($request, $meeting);
    }
}
