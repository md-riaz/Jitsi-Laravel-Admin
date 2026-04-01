<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\SubscriptionPlan;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $freePlan = SubscriptionPlan::where('slug', 'free')->where('is_active', true)->first();

        return view('auth.register', compact('freePlan'));
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
            'account_type' => 'required|in:organization',
            'organization_name' => 'required|string|max:255',
        ], [
            'account_type.in' => 'New registrations must create an organization account.',
            'organization_name.required' => 'Please provide your organization name.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        $organization = DB::transaction(function () use ($data) {
            $organization = $this->createOrganization([
                'name' => $data['organization_name'],
            ]);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'account_type' => 'organization',
                'status' => 'active',
                'organization_id' => $organization->id,
            ]);

            $orgAdminRole = Role::where('slug', 'org-admin')->firstOrFail();
            $user->assignRole($orgAdminRole);
            $organization->users()->attach($user->id, ['role' => 'admin']);

            return $organization->setRelation('users', collect([$user]));
        });

        $user = $organization->users->first();

        Auth::login($user);

        return redirect()->route('dashboard.my-meetings')
            ->with('success', 'Organization account created successfully!');
    }

    /**
     * Show the pending-approval page for users awaiting Org Admin approval.
     */
    public function pendingApproval()
    {
        return view('auth.pending-approval');
    }

    private function createOrganization(array $attributes): Organization
    {
        $name = trim($attributes['name']);
        $baseSlug = Str::slug($name);
        $slug = $baseSlug !== '' ? $baseSlug : Str::lower(Str::random(8));
        $suffix = 1;

        while (Organization::where('slug', $slug)->exists()) {
            $slug = $baseSlug !== '' ? $baseSlug . '-' . $suffix : Str::lower(Str::random(8));
            $suffix++;
        }

        return Organization::create([
            'name' => $name,
            'slug' => $slug,
            'is_active' => true,
        ]);
    }
}
