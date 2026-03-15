<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Redirect pending users to the approval-pending page.
     *
     * Users who registered under an organisation are placed in 'pending'
     * status until an Org Admin approves them.  They may still be logged in
     * (we show them the pending page rather than logging them out immediately
     * so they can see their status), but they cannot access any other page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isPending()) {
            // Allow them to see the pending page and to log out
            if (
                !$request->routeIs('auth.pending-approval')
                && !$request->routeIs('tyro-login.logout')
                && !$request->routeIs('logout')
            ) {
                return redirect()->route('auth.pending-approval');
            }
        }

        return $next($request);
    }
}
