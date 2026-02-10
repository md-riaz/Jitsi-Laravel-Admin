<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('dashboard/my-meetings', [\App\Http\Controllers\Dashboard\MyMeetingsController::class, 'index'])->middleware(['auth'])->name('dashboard.my-meetings');

Route::get('dashboard/create-meeting', [\App\Http\Controllers\Dashboard\CreateMeetingController::class, 'create'])->middleware(['auth'])->name('dashboard.create-meeting');
Route::post('dashboard/create-meeting', [\App\Http\Controllers\Dashboard\CreateMeetingController::class, 'store'])->middleware(['auth'])->name('dashboard.create-meeting.store');

Route::view('dashboard/profile', 'dashboard.profile')->middleware(['auth'])->name('dashboard.profile');

// Meeting join page (public)
Route::get('/meet/{meeting}', [\App\Http\Controllers\Web\MeetingPageController::class, 'show'])->name('meeting.show');

// Guest invite routes
Route::get('/invite/{token}', [\App\Http\Controllers\Web\InviteController::class, 'show'])->name('invite.show');
Route::post('/invite/{token}/accept', [\App\Http\Controllers\Web\InviteController::class, 'accept'])->name('invite.accept');
