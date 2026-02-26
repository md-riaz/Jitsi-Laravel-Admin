<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:120',
        ]);

        $user = User::where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json([
                'ok' => false,
                'error_code' => 'ERR_INVALID_CREDENTIALS',
                'message' => 'Invalid email or password',
            ], 401);
        }

        $abilities = ['meetings:read', 'meetings:join'];
        if ($user->hasRole('super-admin') || $user->hasRole('org-admin')) {
            $abilities[] = 'meetings:host-controls';
        }

        $token = $user->createToken($data['device_name'] ?? 'app-client', $abilities)->plainTextToken;

        return response()->json([
            'ok' => true,
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => null,
                'abilities' => $abilities,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'organization_id' => $user->organization_id,
                ],
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'ok' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'organization_id' => $user->organization_id,
                'roles' => $user->tyroRoles()->pluck('name')->values(),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();
        if ($token) {
            $token->delete();
        }

        return response()->json([
            'ok' => true,
            'data' => ['message' => 'Logged out'],
        ]);
    }
}
