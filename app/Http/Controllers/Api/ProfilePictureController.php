<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProfilePictureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Throwable;

class ProfilePictureController extends Controller
{
    public function __construct(
        private readonly ProfilePictureService $profilePictureService
    ) {}

    /**
     * Upload a profile picture for the authenticated user
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $user = $request->user();
            $path = $this->profilePictureService->upload($user, $request->file('avatar'));

            return response()->json([
                'success' => true,
                'message' => 'Profile picture uploaded successfully',
                'avatar_url' => $user->fresh()->avatar_url,
                'avatar_path' => $path,
            ]);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
                'errors' => [
                    'avatar' => [$exception->getMessage()],
                ],
            ], 422);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Unable to upload profile picture right now. Please try again.',
            ], 500);
        }
    }

    /**
     * Delete the authenticated user's profile picture
     */
    public function delete(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->profilePictureService->delete($user);

        return response()->json([
            'success' => true,
            'message' => 'Profile picture deleted successfully',
            'avatar_url' => $user->fresh()->avatar_url,
        ]);
    }
}
