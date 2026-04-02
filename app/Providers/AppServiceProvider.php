<?php

namespace App\Providers;

use App\Models\Meeting;
use App\Models\User;
use App\Policies\MeetingPolicy;
use App\Policies\TeamPolicy;
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
        Gate::policy(User::class, TeamPolicy::class);

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

            $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('super-admin');
            $isOrgAdmin = method_exists($user, 'hasRole') && $user->hasRole('org-admin') && !$isSuperAdmin;

            $allMeetingsQuery = Meeting::query();

            if ($isSuperAdmin) {
                // platform-wide
            } elseif ($isOrgAdmin && $user->organization_id) {
                // organization-wide for org admin
                $allMeetingsQuery->where('organization_id', $user->organization_id);
            } else {
                // user-scoped for host/member/personal users
                $allMeetingsQuery->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhereHas('participants', fn ($p) => $p->where('user_id', $user->id));
                });
            }

            $allMeetings = $allMeetingsQuery
                ->with(['organization', 'participants', 'creator'])
                ->get();

            $totalMeetings = $isOrgAdmin
                ? $allMeetings->count()
                : $allMeetings->where('created_by', $user->id)->count();

            $view->with([
                'upcomingMeetings'  => $allMeetings->filter(
                    fn ($m) => ($m->end_at === null || $m->end_at->isFuture()) && $m->status !== 'live'
                )->sortBy('start_at')->values(),
                'liveMeetings'      => $allMeetings->filter(fn ($m) => $m->canJoinAt(now())),
                'totalMeetings'     => $totalMeetings,
                'totalParticipants' => $allMeetings->sum(fn ($m) => (int) $m->active_participant_count),
            ]);
        };

        View::composer('vendor.tyro-dashboard.dashboard.user', $meetingComposer);
        View::composer('vendor.tyro-dashboard.dashboard.index', $meetingComposer);

        View::composer([
            'vendor.tyro-dashboard.dashboard.admin',
            'vendor.tyro-dashboard.dashboard.index',
        ], function ($view) {
            $user = Auth::user();

            if (! $user || ! method_exists($user, 'hasRole')) {
                return;
            }

            $isOrgAdmin = $user->hasRole('org-admin') && ! $user->hasRole('super-admin');
            if (! $isOrgAdmin || ! $user->organization_id) {
                return;
            }

            $organizationUsersQuery = User::query()
                ->where('organization_id', $user->organization_id)
                ->whereDoesntHave('tyroRoles', function ($query) {
                    $query->where('slug', 'super-admin');
                });

            $organizationUsers = (clone $organizationUsersQuery)
                ->with('tyroRoles')
                ->latest()
                ->get();

            $suspendedUsers = $organizationUsers->filter(function ($organizationUser) {
                if (method_exists($organizationUser, 'isSuspended')) {
                    return $organizationUser->isSuspended();
                }

                return $organizationUser->status === 'suspended';
            });

            $roleDistribution = $organizationUsers
                ->flatMap(function ($organizationUser) {
                    return $organizationUser->tyroRoles
                        ->unique('id')
                        ->map(fn ($role) => [
                            'id' => $role->id,
                            'name' => $role->name,
                            'user_id' => $organizationUser->id,
                        ]);
                })
                ->groupBy('id')
                ->map(fn ($roles, $roleId) => [
                    'id' => $roleId,
                    'name' => $roles->first()['name'],
                    'count' => $roles->pluck('user_id')->unique()->count(),
                ])
                ->sortByDesc('count')
                ->values();

            $existingStats = $view->getData()['stats'] ?? [];
            if (! is_array($existingStats)) {
                $existingStats = (array) $existingStats;
            }

            $view->with('stats', array_merge($existingStats, [
                'total_users' => $organizationUsers->count(),
                'suspended_users' => $suspendedUsers->count(),
                'recent_users' => $organizationUsers->take(5)->values(),
                'role_distribution' => $roleDistribution,
            ]));
        });

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

        // Inject pending user count for org admins (sidebar)
        View::composer('vendor.tyro-dashboard.partials.admin-sidebar', function ($view) {
            $user = Auth::user();
            $pendingCount = 0;
            if ($user && method_exists($user, 'hasRole') && $user->hasRole('org-admin') && $user->organization_id) {
                $pendingCount = \App\Models\User::where('organization_id', $user->organization_id)
                    ->where('status', 'pending')
                    ->count();
            }
            $view->with('sidebarPendingCount', $pendingCount);
        });
    }
}
