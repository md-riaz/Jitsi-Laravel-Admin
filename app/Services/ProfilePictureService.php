<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfilePictureService
{
    /**
     * Upload and store a user's profile picture
     */
    public function upload(User $user, UploadedFile $file): string
    {
        // Delete old avatar if exists
        if ($user->avatar_path) {
            $this->delete($user);
        }

        // Get image info and create resource
        $image = $this->createImageResource($file);

        // Resize image to 400px width maintaining aspect ratio
        $resizedImage = $this->resizeImage($image, 400);

        // Generate filename
        $filename = 'avatars/' . $user->id . '_' . time() . '.jpg';

        // Create directory if it doesn't exist
        $directory = storage_path('app/public/avatars');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Save to storage
        $path = storage_path('app/public/' . $filename);
        imagejpeg($resizedImage, $path, 85);

        // Free memory
        imagedestroy($image);
        imagedestroy($resizedImage);

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

    /**
     * Create image resource from uploaded file
     */
    private function createImageResource(UploadedFile $file)
    {
        $mimeType = $file->getMimeType();

        return match ($mimeType) {
            'image/jpeg', 'image/jpg' => imagecreatefromjpeg($file->getPathname()),
            'image/png' => imagecreatefrompng($file->getPathname()),
            'image/gif' => imagecreatefromgif($file->getPathname()),
            default => throw new \InvalidArgumentException('Unsupported image type'),
        };
    }

    /**
     * Resize image maintaining aspect ratio
     */
    private function resizeImage($image, int $maxWidth)
    {
        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        if ($originalWidth <= $maxWidth) {
            return $image;
        }

        $ratio = $maxWidth / $originalWidth;
        $newWidth = $maxWidth;
        $newHeight = (int) ($originalHeight * $ratio);

        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled(
            $resizedImage,
            $image,
            0, 0, 0, 0,
            $newWidth,
            $newHeight,
            $originalWidth,
            $originalHeight
        );

        return $resizedImage;
    }
}
