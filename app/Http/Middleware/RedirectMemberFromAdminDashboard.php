<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectMemberFromAdminDashboard
{
    /**
     * Redirect non-admin organization users away from the admin dashboard home.
     * Hosts and members should land on the user-facing meetings page instead.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user
            && $request->routeIs('tyro-dashboard.index')
            && !$user->hasRole('org-admin')
            && !$user->hasRole('super-admin')
            && ($user->hasRole('member') || $user->hasRole('host'))
        ) {
            return redirect()->route('dashboard.my-meetings');
        }

        return $next($request);
    }
}
