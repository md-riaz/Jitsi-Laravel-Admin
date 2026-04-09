<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;

class MyMeetingsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $isSuperAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole('super-admin');
        $isOrgAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole('org-admin') && !$isSuperAdmin;

        $baseQuery = Meeting::query();

        if ($isSuperAdmin) {
            // platform-wide visibility
        } elseif ($isOrgAdmin && $user->organization_id) {
            $baseQuery->where('organization_id', $user->organization_id);
        } else {
            info('Debug: User role or organization is missing.', ['user' => $user]);
            $baseQuery->where(function ($query) use ($user) {
                $query->where('created_by', $user->id)
                    ->orWhereHas('participants', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            });
        }

        info('Debug: Query before execution', [
            'isSuperAdmin' => $isSuperAdmin,
            'isOrgAdmin' => $isOrgAdmin,
            'user' => $user,
            'query' => $baseQuery->toSql(),
            'bindings' => $baseQuery->getBindings(),
            'request' => $request->all(),
        ]);

        $allMeetings = (clone $baseQuery)
            ->with(['organization', 'creator'])
            ->orderByDesc('created_at')
            ->get();

        $now = now();

        $liveCollection = $allMeetings
            ->filter(fn ($meeting) => $meeting->status === 'live')
            ->sortByDesc(fn ($meeting) => $meeting->actual_started_at ?? $meeting->start_at ?? $meeting->created_at)
            ->values();

        $upcomingCollection = $allMeetings
            ->filter(fn ($meeting) => $meeting->isUpcomingAt($now))
            ->sortBy(fn ($meeting) => $meeting->start_at ?? $meeting->created_at)
            ->values();

        $pastCollection = $allMeetings
            ->filter(fn ($meeting) => $meeting->isPastAt($now))
            ->sortByDesc(fn ($meeting) => $meeting->end_at ?? $meeting->actual_ended_at ?? $meeting->created_at)
            ->values();

        $pastMeetings = $this->paginateCollection($pastCollection, 15, 'past_page', $request);

        $analytics = [
            'total_meetings' => $allMeetings->count(),
            'live_now' => $liveCollection->count(),
            'upcoming_meetings' => $upcomingCollection->count(),
            'past_meetings' => $pastCollection->count(),
        ];

        $liveMeetings = $liveCollection;
        $upcomingMeetings = $upcomingCollection;
        $analytics = $analytics ?? [
            'total_meetings' => 0,
            'live_now' => 0,
            'upcoming_meetings' => 0,
            'past_meetings' => 0,
        ];

        return view('dashboard.my-meetings', compact('liveMeetings', 'upcomingMeetings', 'pastMeetings', 'analytics'));
    }

    private function paginateCollection(Collection $items, int $perPage, string $pageName, Request $request): LengthAwarePaginator
    {
        $page = max(1, (int) $request->query($pageName, 1));
        $results = $items->forPage($page, $perPage)->values();

        return (new LengthAwarePaginator(
            $results,
            $items->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'pageName' => $pageName,
            ]
        ))->appends($request->except($pageName));
    }
}
