<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\SubscriptionPlan;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /**
     * Show the registration form
     */
    public function showRegistrationForm()
    {
        $organizations = Organization::where('is_active', true)->orderBy('name')->get();
        $freePlan = SubscriptionPlan::where('slug', 'free')->where('is_active', true)->first();

        return view('auth.register', compact('organizations', 'freePlan'));
    }

    /**
     * Handle a registration request
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'account_type' => 'required|in:single,organization',
            'organization_id' => 'required_if:account_type,organization|nullable|exists:organizations,id',
        ], [
            'organization_id.required_if' => 'Please select an organization to join.',
            'organization_id.exists' => 'The selected organization does not exist.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        if ($data['account_type'] === 'organization') {
            // Verify the chosen org is active
            $organization = Organization::find($data['organization_id']);
            if (!$organization || !$organization->is_active) {
                return redirect()->back()
                    ->withErrors(['organization_id' => 'The selected organization is not accepting new members.'])
                    ->withInput();
            }

            // Create the user in a pending state – awaiting Org Admin approval
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'account_type' => 'organization',
                'status' => 'pending',
                'organization_id' => $organization->id,
            ]);

            // Assign member role (will be upgraded by Org Admin if needed)
            $memberRole = Role::where('slug', 'member')->firstOrFail();
            $user->assignRole($memberRole);

            // Add to the organization pivot table with member role
            $organization->users()->attach($user->id, ['role' => 'member']);

            // Log the user in so they can see the pending-approval page
            Auth::login($user);

            return redirect()->route('auth.pending-approval');
        }

        // Personal / single account – active immediately, on Free Plan
        $freePlan = SubscriptionPlan::where('slug', 'free')->where('is_active', true)->first();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'account_type' => 'single',
            'status' => 'active',
            'subscription_plan_id' => $freePlan?->id,
        ]);

        $hostRole = Role::where('slug', 'host')->firstOrFail();
        $user->assignRole($hostRole);

        Auth::login($user);

        return redirect()->route('dashboard.my-meetings')
            ->with('success', 'Account created successfully!');
    }

    /**
     * Show the pending-approval page for users awaiting Org Admin approval.
     */
    public function pendingApproval()
    {
        return view('auth.pending-approval');
    }
}
