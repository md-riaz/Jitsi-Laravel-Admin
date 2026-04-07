<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Custom Registration Routes (override Tyro Login)
Route::get('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'register'])->name('register.submit');
Route::get('/pending-approval', [\App\Http\Controllers\Auth\RegisterController::class, 'pendingApproval'])->middleware(['auth'])->name('auth.pending-approval');

Route::get('dashboard/my-meetings', [\App\Http\Controllers\Dashboard\MyMeetingsController::class, 'index'])->middleware(['auth'])->name('dashboard.my-meetings');

Route::get('dashboard/calendar', [\App\Http\Controllers\Dashboard\CalendarController::class, 'index'])->middleware(['auth'])->name('dashboard.calendar');
Route::get('dashboard/calendar/events', [\App\Http\Controllers\Dashboard\CalendarController::class, 'events'])->middleware(['auth'])->name('dashboard.calendar.events');

Route::get('dashboard/create-meeting', [\App\Http\Controllers\Dashboard\CreateMeetingController::class, 'create'])->middleware(['auth'])->name('dashboard.create-meeting');
Route::post('dashboard/create-meeting', [\App\Http\Controllers\Dashboard\CreateMeetingController::class, 'store'])->middleware(['auth'])->name('dashboard.create-meeting.store');
Route::get('dashboard/meetings/{meeting}/edit', [\App\Http\Controllers\Dashboard\EditMeetingController::class, 'edit'])->middleware(['auth'])->name('dashboard.meetings.edit');
Route::put('dashboard/meetings/{meeting}', [\App\Http\Controllers\Dashboard\EditMeetingController::class, 'update'])->middleware(['auth'])->name('dashboard.meetings.update');
Route::delete('dashboard/meetings/{meeting}', [\App\Http\Controllers\Dashboard\EditMeetingController::class, 'destroy'])->middleware(['auth'])->name('dashboard.meetings.destroy');

// Team Management Routes (for organization admins)
Route::middleware(['auth', 'org-admin-or-super-admin'])->group(function () {
    Route::get('dashboard/team', [\App\Http\Controllers\Dashboard\TeamController::class, 'index'])->name('dashboard.team.index');
    Route::get('dashboard/team/create', [\App\Http\Controllers\Dashboard\TeamController::class, 'create'])->name('dashboard.team.create');
    Route::post('dashboard/team', [\App\Http\Controllers\Dashboard\TeamController::class, 'store'])->name('dashboard.team.store');
    Route::get('dashboard/team/{id}/edit', [\App\Http\Controllers\Dashboard\TeamController::class, 'edit'])->name('dashboard.team.edit');
    Route::put('dashboard/team/{id}', [\App\Http\Controllers\Dashboard\TeamController::class, 'update'])->name('dashboard.team.update');
    Route::delete('dashboard/team/{id}', [\App\Http\Controllers\Dashboard\TeamController::class, 'destroy'])->name('dashboard.team.destroy');
    Route::post('dashboard/team/{id}/suspend', [\App\Http\Controllers\Dashboard\TeamController::class, 'suspend'])->name('dashboard.team.suspend');
    Route::post('dashboard/team/{id}/unsuspend', [\App\Http\Controllers\Dashboard\TeamController::class, 'unsuspend'])->name('dashboard.team.unsuspend');
    Route::post('dashboard/team/{id}/login-as', [\App\Http\Controllers\Dashboard\TeamController::class, 'loginAs'])->name('dashboard.team.login-as');
});

Route::view('dashboard/profile', 'dashboard.profile')->middleware(['auth'])->name('dashboard.profile');
Route::middleware(['auth'])->group(function () {
    Route::post('dashboard/profile/avatar', [\App\Http\Controllers\Api\ProfilePictureController::class, 'upload'])->name('dashboard.profile.avatar.upload');
    Route::delete('dashboard/profile/avatar', [\App\Http\Controllers\Api\ProfilePictureController::class, 'delete'])->name('dashboard.profile.avatar.delete');
});
Route::get('dashboard/subscription', [\App\Http\Controllers\Dashboard\SubscriptionController::class, 'show'])->middleware(['auth'])->name('dashboard.subscription');
Route::get('dashboard/meetings/{meeting}/diagnostics', [\App\Http\Controllers\Dashboard\MeetingDiagnosticsController::class, 'show'])
    ->middleware(['auth'])
    ->name('dashboard.meetings.diagnostics');
Route::get('dashboard/meetings/{meeting}/summary', [\App\Http\Controllers\Dashboard\MeetingSummaryController::class, 'show'])
    ->middleware(['auth'])
    ->name('dashboard.meetings.summary');
Route::get('dashboard/meetings/{meeting}/summary/export/participants', [\App\Http\Controllers\Dashboard\MeetingSummaryController::class, 'exportParticipants'])
    ->middleware(['auth'])
    ->name('dashboard.meetings.summary.export.participants');
Route::get('dashboard/meetings/{meeting}/summary/export/events', [\App\Http\Controllers\Dashboard\MeetingSummaryController::class, 'exportEvents'])
    ->middleware(['auth'])
    ->name('dashboard.meetings.summary.export.events');

// Meeting join page (public)
Route::get('/meet/{meeting}', [\App\Http\Controllers\Web\MeetingPageController::class, 'show'])->name('meeting.show');
Route::get('/meet/{meeting}/download-ics', [\App\Http\Controllers\Web\MeetingPageController::class, 'downloadIcs'])->name('meeting.download-ics');

// Guest invite routes
Route::get('/invite/{token}', [\App\Http\Controllers\Web\InviteController::class, 'show'])->name('invite.show');
Route::post('/invite/{token}/accept', [\App\Http\Controllers\Web\InviteController::class, 'accept'])->name('invite.accept');

// Alias expected by Laravel auth middleware when unauthenticated
Route::get('/_login', fn () => redirect()->route('tyro-login.login'))->name('login');
