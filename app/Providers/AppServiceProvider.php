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
        // Inject meeting stats into the user dashboard view
        View::composer('vendor.tyro-dashboard.dashboard.user', function ($view) {
            $user = Auth::user();
            if (!$user) {
                $view->with([
                    'upcomingMeetings' => collect(),
                    'liveMeetings' => collect(),
                    'totalMeetings' => 0,
                    'totalParticipants' => 0,
                ]);
                return;
            }

            $userMeetings = Meeting::where('created_by', $user->id)
                ->with(['organization', 'participants'])
                ->get();

            $liveMeetings = $userMeetings->filter(fn ($m) => $m->canJoinAt(now()));

            $upcomingMeetings = Meeting::where(function ($query) use ($user) {
                $query->where('created_by', $user->id)
                    ->orWhereHas('participants', fn ($q) => $q->where('user_id', $user->id));
            })
            ->where(function ($query) {
                $query->whereNull('end_at')->orWhere('end_at', '>', now());
            })
            ->where('status', '!=', 'live')
            ->orderBy('start_at')
            ->with(['organization', 'participants'])
            ->get();

            $view->with([
                'upcomingMeetings' => $upcomingMeetings,
                'liveMeetings' => $liveMeetings,
                'totalMeetings' => $userMeetings->count(),
                'totalParticipants' => $userMeetings->sum(fn ($m) => $m->participants->count()),
            ]);
        });
    }
}
