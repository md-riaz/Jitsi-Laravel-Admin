<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('dashboard/organizations', 'dashboard.organizations')->middleware(['auth', 'tyro-dashboard.admin'])->name('dashboard.organizations');

Route::view('dashboard/system-usage', 'dashboard.system-usage')->middleware(['auth', 'tyro-dashboard.admin'])->name('dashboard.system-usage');

Route::view('dashboard/plans-quotas', 'dashboard.plans-quotas')->middleware(['auth', 'tyro-dashboard.admin'])->name('dashboard.plans-quotas');

Route::view('dashboard/delivery-logs', 'dashboard.delivery-logs')->middleware(['auth', 'tyro-dashboard.admin'])->name('dashboard.delivery-logs');

Route::view('dashboard/meetings', 'dashboard.meetings')->middleware(['auth', 'tyro-dashboard.admin'])->name('dashboard.meetings');

Route::view('dashboard/invitations', 'dashboard.invitations')->middleware(['auth', 'tyro-dashboard.admin'])->name('dashboard.invitations');

Route::view('dashboard/audit-logs', 'dashboard.audit-logs')->middleware(['auth', 'tyro-dashboard.admin'])->name('dashboard.audit-logs');

Route::get('dashboard/my-meetings', [\App\Http\Controllers\Dashboard\MyMeetingsController::class, 'index'])->middleware(['auth'])->name('dashboard.my-meetings');

Route::view('dashboard/create-meeting', 'dashboard.create-meeting')->middleware(['auth'])->name('dashboard.create-meeting');

Route::view('dashboard/profile', 'dashboard.profile')->middleware(['auth'])->name('dashboard.profile');

// Meeting join page (public)
Route::get('/meet/{meeting}', [\App\Http\Controllers\Web\MeetingPageController::class, 'show'])->name('meeting.show');
