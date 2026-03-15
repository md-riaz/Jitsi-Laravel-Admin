<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfilePictureService
{
    /** Allowed MIME types mapped to safe extensions */
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/jpg'  => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
    ];

    /**
     * Upload and store a user's profile picture
     */
    public function upload(User $user, UploadedFile $file): string
    {
        // Validate MIME type against allowed types using the actual detected MIME type
        $mimeType = $file->getMimeType();
        if (!isset(self::ALLOWED_MIME_TYPES[$mimeType])) {
            throw new \InvalidArgumentException('Unsupported image type. Only JPEG, PNG, and GIF are allowed.');
        }

        // Delete old avatar if exists
        if ($user->avatar_path) {
            $this->delete($user);
        }

        // Generate a secure, unpredictable filename using a random string
        $extension = self::ALLOWED_MIME_TYPES[$mimeType];
        $basename = $user->id . '_' . Str::random(16) . '.' . $extension;

        // Store the file in the public disk under avatars/
        Storage::disk('public')->putFileAs('avatars', $file, $basename);

        $filename = 'avatars/' . $basename;

        // Update user record
        $user->update(['avatar_path' => $filename]);

        return $filename;
    }

    /**
     * Delete a user's profile picture
     */
    public function delete(User $user): void
    {
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->update(['avatar_path' => null]);
        }
    }

    /**
     * Get the URL for a user's avatar or fallback to Gravatar
     */
    public function getAvatarUrl(User $user): string
    {
        return $user->avatar_url;
    }
}
