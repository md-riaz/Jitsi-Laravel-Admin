<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /**
     * Show the registration form
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
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
            'organization_name' => 'required_if:account_type,organization|nullable|string|max:255',
        ], [
            'organization_name.required_if' => 'Organization name is required when creating an organization account.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Create the organization if account type is organization
        $organizationId = null;
        if ($data['account_type'] === 'organization' && !empty($data['organization_name'])) {
            $organization = Organization::create([
                'name' => $data['organization_name'],
                'slug' => Str::slug($data['organization_name']) . '-' . Str::random(6),
            ]);
            $organizationId = $organization->id;
        }

        // Create the user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'account_type' => $data['account_type'],
            'organization_id' => $organizationId,
        ]);

        // Assign appropriate role based on account type
        if ($data['account_type'] === 'organization') {
            $user->assignRole('org-admin');

            // Add user to organization pivot table
            if ($organizationId) {
                $user->organization->users()->attach($user->id, ['role' => 'admin']);
            }
        } else {
            $user->assignRole('host');
        }

        // Log the user in
        Auth::login($user);

        return redirect()->route('dashboard.my-meetings')
            ->with('success', 'Account created successfully!');
    }
}
