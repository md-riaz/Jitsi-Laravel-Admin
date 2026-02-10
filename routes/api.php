<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Meeting Join API
Route::post('/meetings/{meeting}/join', [\App\Http\Controllers\Api\MeetingJoinController::class, 'join'])->name('api.meeting.join');
