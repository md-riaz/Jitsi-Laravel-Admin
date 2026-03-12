<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule meeting status updates every minute
Schedule::command('meetings:update-statuses')->everyMinute();
Schedule::command('meetings:cleanup-empty-instant')->everyMinute();
