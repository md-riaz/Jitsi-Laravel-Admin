<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectSuperAdminFromOrganizationPages
{
    /**
     * Redirect super-admins away from organization-user pages.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user
            && method_exists($user, 'hasRole')
            && $user->hasRole('super-admin')
            && $request->routeIs('dashboard.subscription', 'dashboard.create-meeting*')
        ) {
            return redirect()
                ->route('tyro-dashboard.index')
                ->with('error', 'This page is only available for organization users.');
        }

        return $next($request);
    }
}
