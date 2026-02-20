<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Custom Registration Routes (override Tyro Login)
Route::get('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'register'])->name('register.submit');

Route::get('dashboard/my-meetings', [\App\Http\Controllers\Dashboard\MyMeetingsController::class, 'index'])->middleware(['auth'])->name('dashboard.my-meetings');

Route::get('dashboard/calendar', [\App\Http\Controllers\Dashboard\CalendarController::class, 'index'])->middleware(['auth'])->name('dashboard.calendar');
Route::get('dashboard/calendar/events', [\App\Http\Controllers\Dashboard\CalendarController::class, 'events'])->middleware(['auth'])->name('dashboard.calendar.events');

Route::get('dashboard/create-meeting', [\App\Http\Controllers\Dashboard\CreateMeetingController::class, 'create'])->middleware(['auth'])->name('dashboard.create-meeting');
Route::post('dashboard/create-meeting', [\App\Http\Controllers\Dashboard\CreateMeetingController::class, 'store'])->middleware(['auth'])->name('dashboard.create-meeting.store');

// Team Management Routes (for organization admins)
Route::get('dashboard/team', [\App\Http\Controllers\Dashboard\TeamController::class, 'index'])->middleware(['auth'])->name('dashboard.team.index');
Route::get('dashboard/team/create', [\App\Http\Controllers\Dashboard\TeamController::class, 'create'])->middleware(['auth'])->name('dashboard.team.create');
Route::post('dashboard/team', [\App\Http\Controllers\Dashboard\TeamController::class, 'store'])->middleware(['auth'])->name('dashboard.team.store');
Route::get('dashboard/team/{id}/edit', [\App\Http\Controllers\Dashboard\TeamController::class, 'edit'])->middleware(['auth'])->name('dashboard.team.edit');
Route::put('dashboard/team/{id}', [\App\Http\Controllers\Dashboard\TeamController::class, 'update'])->middleware(['auth'])->name('dashboard.team.update');
Route::delete('dashboard/team/{id}', [\App\Http\Controllers\Dashboard\TeamController::class, 'destroy'])->middleware(['auth'])->name('dashboard.team.destroy');

Route::view('dashboard/profile', 'dashboard.profile')->middleware(['auth'])->name('dashboard.profile');

// Meeting join page (public)
Route::get('/meet/{meeting}', [\App\Http\Controllers\Web\MeetingPageController::class, 'show'])->name('meeting.show');
Route::get('/meet/{meeting}/download-ics', [\App\Http\Controllers\Web\MeetingPageController::class, 'downloadIcs'])->name('meeting.download-ics');

// Guest invite routes
Route::get('/invite/{token}', [\App\Http\Controllers\Web\InviteController::class, 'show'])->name('invite.show');
Route::post('/invite/{token}/accept', [\App\Http\Controllers\Web\InviteController::class, 'accept'])->name('invite.accept');
