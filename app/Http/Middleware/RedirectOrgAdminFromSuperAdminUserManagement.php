<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectOrgAdminFromSuperAdminUserManagement
{
    /**
     * Redirect org-admins away from the super-admin user management pages.
     *
     * Org-admins manage only their own organisation's users via /dashboard/team.
     * The platform-wide /dashboard/users pages are reserved for super-admins.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user
            && method_exists($user, 'hasRole')
            && $user->hasRole('org-admin')
            && !$user->hasRole('super-admin')
            && $request->routeIs('tyro-dashboard.users.*')
        ) {
            return redirect()
                ->route('dashboard.team.index')
                ->with('error', 'Please use the Organization User Management page to manage your users.');
        }

        return $next($request);
    }
}
