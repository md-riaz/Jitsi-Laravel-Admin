<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MyMeetingsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $upcomingMeetings = Meeting::where(function ($query) use ($user) {
            $query->where('created_by', $user->id)
                ->orWhereHas('participants', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
        })
        ->where('end_at', '>', now())
        ->orderBy('start_at')
        ->with(['organization', 'creator', 'participants'])
        ->get();

        $pastMeetings = Meeting::where(function ($query) use ($user) {
            $query->where('created_by', $user->id)
                ->orWhereHas('participants', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
        })
        ->where('end_at', '<=', now())
        ->orderByDesc('start_at')
        ->with(['organization', 'creator', 'participants'])
        ->limit(10)
        ->get();

        return view('dashboard.my-meetings', compact('upcomingMeetings', 'pastMeetings'));
    }
}
