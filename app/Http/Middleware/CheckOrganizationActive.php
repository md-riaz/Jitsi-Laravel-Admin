<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckOrganizationActive
{
    /**
     * Block users whose organization has been deactivated by the Super Admin.
     *
     * When an org is deactivated no user belonging to it should be able to
     * access authenticated pages.  We log them out immediately and send them
     * to the login screen with a clear, user-facing message.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user
            && $user->organization_id
            && $user->organization
            && $user->organization->is_active === false
        ) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('tyro-login.login')
                ->withErrors([
                    'email' => 'Your account has an issue. Please contact your organization admin.',
                ]);
        }

        return $next($request);
    }
}
