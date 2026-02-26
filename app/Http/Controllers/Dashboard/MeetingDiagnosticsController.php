<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\MeetingEvent;
use App\Models\MeetingParticipant;
use App\Services\JitsiJwtService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MeetingDiagnosticsController extends Controller
{
    public function __construct(
        private readonly JitsiJwtService $jwtService
    ) {}

    public function show(Request $request, Meeting $meeting): View
    {
        $visibility = Meeting::normalizeVisibility($meeting->visibility);

        $recentDenials = MeetingEvent::where('meeting_id', $meeting->id)
            ->whereIn('type', [
                'join_denied',
                'admission_requested',
                'rejected',
            ])
            ->latest()
            ->limit(25)
            ->get();

        $pendingAdmissions = MeetingParticipant::where('meeting_id', $meeting->id)
            ->where('invite_status', 'invited')
            ->whereNull('user_id')
            ->latest('updated_at')
            ->get();

        $jwtConfig = [
            'domain' => (string) config('services.jitsi.domain'),
            'issuer' => (string) config('services.jitsi.issuer'),
            'audience' => (string) config('services.jitsi.audience'),
            'sub' => (string) config('services.jitsi.sub'),
            'has_secret' => !empty(config('services.jitsi.secret')),
            'org_require_jwt' => (bool) optional($meeting->organization)->require_jwt,
            'org_expiry_minutes' => (int) (optional($meeting->organization)->jwt_expiry_minutes ?? 120),
        ];

        $jwtChecks = [
            'secret_configured' => $jwtConfig['has_secret'],
            'issuer_present' => $jwtConfig['issuer'] !== '',
            'audience_present' => $jwtConfig['audience'] !== '',
            'sub_present' => $jwtConfig['sub'] !== '',
            'sub_matches_domain' => $jwtConfig['sub'] === $jwtConfig['domain'],
        ];

        $testToken = null;
        $testClaims = null;
        $testTokenError = null;

        try {
            $testToken = $this->jwtService->generateToken($meeting, $request->user(), 'DiagnosticsUser', true);
            if ($testToken) {
                $parts = explode('.', $testToken);
                if (count($parts) === 3) {
                    $claimsJson = base64_decode(strtr($parts[1], '-_', '+/'));
                    $testClaims = json_decode($claimsJson, true);
                }
            }
        } catch (\Throwable $e) {
            $testTokenError = $e->getMessage();
        }

        return view('dashboard.meeting-diagnostics', [
            'meeting' => $meeting,
            'visibility' => $visibility,
            'recentDenials' => $recentDenials,
            'pendingAdmissions' => $pendingAdmissions,
            'jwtConfig' => $jwtConfig,
            'jwtChecks' => $jwtChecks,
            'testToken' => $testToken,
            'testClaims' => $testClaims,
            'testTokenError' => $testTokenError,
        ]);
    }
}
