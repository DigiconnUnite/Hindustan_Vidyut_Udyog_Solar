<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Shared cPanel hosting has no persistent queue worker or Redis (PRD NFR-4) —
// a single cron entry runs `schedule:run` every minute, which drains the
// database queue here. cPanel Cron Job: * * * * * php artisan schedule:run
Schedule::command('queue:work --stop-when-empty --max-time=50')->everyMinute()->withoutOverlapping();
