<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    /**
     * Show the current subscription plan for the authenticated user.
     *
     * - Personal users: their own subscription_plan
     * - Org users: their organization's subscription_plan
     */
    public function show(Request $request)
    {
        $user = Auth::user();

        $plan = $user->getEffectiveSubscriptionPlan();
        $isOrgPlan = $user->isOrganizationUser();
        $org = $isOrgPlan ? $user->organization : null;

        return view('dashboard.subscription', compact('plan', 'isOrgPlan', 'org'));
    }
}
