<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MeetingInvite;
use App\Services\MeetingInviteService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InviteController extends Controller
{
    public function __construct(
        private readonly MeetingInviteService $inviteService
    ) {}

    public function show(Request $request, string $token): View
    {
        $invite = $this->inviteService->validateInvite($token);
        
        if (!$invite) {
            abort(404, 'Invalid or expired invitation');
        }
        
        $meeting = $invite->meeting()->with(['creator', 'organization'])->first();
        
        if (!$meeting) {
            abort(404, 'Meeting not found');
        }
        
        return view('invite.show', [
            'invite' => $invite,
            'meeting' => $meeting,
            'token' => $token,
        ]);
    }
    
    public function accept(Request $request, string $token)
    {
        $request->validate([
            'display_name' => 'required|string|max:255',
        ]);
        
        $invite = $this->inviteService->validateInvite($token);
        
        if (!$invite) {
            return back()->withErrors(['token' => 'Invalid or expired invitation']);
        }
        
        $meeting = $invite->meeting;
        
        // Store guest info in session
        session([
            'guest_name' => $request->display_name,
            'guest_email' => $invite->email,
            'invite_token' => $token,
        ]);
        
        // Redirect to meeting page
        return redirect()->route('meeting.show', ['meeting' => $meeting->id]);
    }
}
