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

// Meeting Join API
// Use web middleware so session-authenticated users are recognized on /api join flow,
// while still allowing guests when meeting policy permits.
Route::middleware('web')->group(function () {
    Route::get('/meetings/{meeting}/health', [\App\Http\Controllers\Api\MeetingJoinController::class, 'health'])->name('api.meeting.health');
    Route::post('/meetings/{meeting}/join', [\App\Http\Controllers\Api\MeetingJoinController::class, 'join'])->name('api.meeting.join');
    Route::post('/meetings/{meeting}/leave', [\App\Http\Controllers\Api\MeetingJoinController::class, 'leave'])->name('api.meeting.leave');
});
