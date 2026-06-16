<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::command('images:cleanup')->dailyAt('04:00');

// I want to run this command four times a day, at 2 AM, 8 AM, 2 PM, and 8 PM. So I will use the following code without touching the cron expression directly, as Laravel's scheduler provides a convenient method for this:
Schedule::command('sitemap:generate')->twiceDaily(2, 14); // This will run at 2 AM and 2 PM
