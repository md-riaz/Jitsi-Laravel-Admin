<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !method_exists($user, 'hasRole') || !$user->hasRole('super-admin')) {
            abort(403, 'Only super-admin can access this page.');
        }

        return $next($request);
    }
}
