<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrgAdminOrSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Unauthorized.');
        }

        $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('super-admin');
        $isOrgAdmin = $user->isOrganizationUser()
            && $user->organization_id
            && method_exists($user, 'hasRole')
            && $user->hasRole('org-admin');

        if (!$isSuperAdmin && !$isOrgAdmin) {
            abort(403, 'Only organization admins can manage team members.');
        }

        return $next($request);
    }
}
