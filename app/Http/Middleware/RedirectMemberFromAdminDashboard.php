<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectMemberFromAdminDashboard
{
    /**
     * Redirect members to My Meetings when they access the admin dashboard home.
     * Members have no use for the admin statistics page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasRole('member') && !$user->hasRole('org-admin') && !$user->hasRole('super-admin') && !$user->hasRole('host')) {
            // Only redirect on the exact dashboard home, not on sub-pages
            if ($request->routeIs('tyro-dashboard.index')) {
                return redirect()->route('dashboard.my-meetings');
            }
        }

        return $next($request);
    }
}
