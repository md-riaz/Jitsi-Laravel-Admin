<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Profile Picture API
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/profile/avatar', [\App\Http\Controllers\Api\ProfilePictureController::class, 'upload'])->name('api.profile.avatar.upload');
    Route::delete('/profile/avatar', [\App\Http\Controllers\Api\ProfilePictureController::class, 'delete'])->name('api.profile.avatar.delete');
});

// v1 deployment-agnostic API (same contract for web/flutter/desktop)
Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [\App\Http\Controllers\Api\V1\AuthController::class, 'login']);

    Route::post('/invites/resolve', [\App\Http\Controllers\Api\V1\InviteController::class, 'resolve']);
    Route::post('/invites/{token}/accept', [\App\Http\Controllers\Api\V1\InviteController::class, 'accept']);
    Route::post('/meetings/{meeting}/join-guest', [\App\Http\Controllers\Api\V1\InviteController::class, 'joinGuest']);
    Route::get('/meetings/{meeting}/admission-status', [\App\Http\Controllers\Api\V1\MeetingController::class, 'admissionStatus']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [\App\Http\Controllers\Api\V1\AuthController::class, 'me']);
        Route::post('/auth/logout', [\App\Http\Controllers\Api\V1\AuthController::class, 'logout']);

        Route::get('/meetings', [\App\Http\Controllers\Api\V1\MeetingController::class, 'index']);
        Route::get('/meetings/{meeting}', [\App\Http\Controllers\Api\V1\MeetingController::class, 'show']);
        Route::get('/meetings/{meeting}/health', [\App\Http\Controllers\Api\V1\MeetingController::class, 'health']);
        Route::post('/meetings/{meeting}/join', [\App\Http\Controllers\Api\V1\MeetingController::class, 'join']);
        Route::post('/meetings/{meeting}/leave', [\App\Http\Controllers\Api\V1\MeetingController::class, 'leave']);

        Route::get('/meetings/{meeting}/pending-admissions', [\App\Http\Controllers\Api\V1\MeetingController::class, 'pendingAdmissions']);
        Route::post('/meetings/{meeting}/admissions/{participant}', [\App\Http\Controllers\Api\V1\MeetingController::class, 'decideAdmission']);

        Route::get('/meetings/{meeting}/summary', [\App\Http\Controllers\Api\V1\MeetingController::class, 'summary']);
        Route::get('/meetings/{meeting}/timeline', [\App\Http\Controllers\Api\V1\MeetingController::class, 'timeline']);
        Route::get('/meetings/{meeting}/attendance', [\App\Http\Controllers\Api\V1\MeetingController::class, 'attendance']);
        Route::get('/meetings/{meeting}/diagnostics', [\App\Http\Controllers\Api\V1\MeetingController::class, 'diagnostics']);
    });
});
