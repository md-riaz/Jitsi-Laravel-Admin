<?php

namespace App\Providers;

use App\Models\Meeting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Inject meeting stats into both user dashboard views
        $meetingComposer = function ($view) {
            $user = Auth::user();
            if (!$user) {
                $view->with([
                    'upcomingMeetings' => collect(),
                    'liveMeetings'     => collect(),
                    'totalMeetings'    => 0,
                    'totalParticipants' => 0,
                ]);
                return;
            }

            // Single query: all meetings the user created or participated in
            $allMeetings = Meeting::where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereHas('participants', fn ($p) => $p->where('user_id', $user->id));
            })->with(['organization', 'participants'])->get();

            $view->with([
                'upcomingMeetings'  => $allMeetings->filter(
                    fn ($m) => ($m->end_at === null || $m->end_at->isFuture()) && $m->status !== 'live'
                )->sortBy('start_at')->values(),
                'liveMeetings'      => $allMeetings->filter(fn ($m) => $m->canJoinAt(now())),
                'totalMeetings'     => $allMeetings->where('created_by', $user->id)->count(),
                'totalParticipants' => $allMeetings->sum(fn ($m) => $m->participants->count()),
            ]);
        };

        View::composer('vendor.tyro-dashboard.dashboard.user', $meetingComposer);
        View::composer('vendor.tyro-dashboard.dashboard.index', $meetingComposer);
    }
}
