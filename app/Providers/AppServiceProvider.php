<?php

namespace App\Providers;

use App\Models\Meeting;
use App\Policies\MeetingPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
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
        // Force HTTPS URL generation when behind a reverse proxy / tunnel
        if (request()->header('X-Forwarded-Proto') === 'https' || config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Dynamically add current request host to Sanctum stateful domains (tunnel/proxy support)
        $host = request()->getHost();
        if ($host) {
            $stateful = config('sanctum.stateful', []);
            if (!in_array($host, $stateful)) {
                $stateful[] = $host;
                config(['sanctum.stateful' => $stateful]);
            }
        }

        Gate::policy(Meeting::class, MeetingPolicy::class);

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

        // Inject billing notification data for org admins (dashboard views only)
        View::composer('vendor.tyro-dashboard.*', function ($view) {
            $user = Auth::user();
            if (
                $user
                && method_exists($user, 'hasRole')
                && $user->hasRole('org-admin')
                && !$user->hasRole('super-admin')
                && $user->organization
            ) {
                $org = $user->organization;
                $view->with('billingExpiringSoon', $org->isSubscriptionExpiringSoon());
                $view->with('billingExpired', $org->isSubscriptionExpired());
                $view->with('billingNotificationDays', $org->billing_notification_days ?? 5);
                $view->with('subscriptionEndsAt', $org->subscription_ends_at);
            } else {
                $view->with('billingExpiringSoon', false);
                $view->with('billingExpired', false);
                $view->with('billingNotificationDays', 5);
                $view->with('subscriptionEndsAt', null);
            }
        });

    }
}
