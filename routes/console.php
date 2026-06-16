<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;

/*
|--------------------------------------------------------------------------
| Custom Artisan Commands
|--------------------------------------------------------------------------
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


/*
|--------------------------------------------------------------------------
| Scheduled Tasks (IMPORTANT FOR YOUR PROJECT)
|--------------------------------------------------------------------------
*/

Schedule::command('inspire')->hourly();

// Example: clear cache daily
Schedule::command('cache:clear')->daily();

// Example: prune old sessions
Schedule::command('session:prune')->daily();

// Example: your future scraper (IMPORTANT for anime site)
Schedule::call(function () {
    // Example logic
    \Log::info('Running scheduled anime update...');
})->hourly();
