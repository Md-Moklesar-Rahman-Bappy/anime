<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;

/*
|--------------------------------------------------------------------------
| Custom Artisan Commands
|--------------------------------------------------------------------------
*/

// Default inspire command
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
| IMPORTANT: Make sure you run:
| php artisan schedule:work  (local)
| or a cron job (production)
*/

// Inspire (example)
Schedule::command('inspire')->hourly();


/*
|--------------------------------------------------------------------------
| System Maintenance
|--------------------------------------------------------------------------
*/

// Clear cache daily
Schedule::command('cache:clear')->dailyAt('03:00');

// Clear route cache weekly
Schedule::command('route:clear')->weekly();

// Clear view cache
Schedule::command('view:clear')->daily();

// Prune sessions safely
Schedule::command('session:prune')->daily();

// Clear expired password resets
Schedule::command('auth:clear-resets')->daily();


/*
|--------------------------------------------------------------------------
| Anime / Manga Platform Tasks (🔥 IMPORTANT)
|--------------------------------------------------------------------------
*/

// Update trending stats
Schedule::call(function () {
    \Log::info('Updating trending anime...');
    // Example:
    // App\Models\Anime::updateTrending();
})->hourly();

// Update view counts aggregation
Schedule::call(function () {
    \Log::info('Aggregating views...');
})->everyThirtyMinutes();

// Refresh featured anime (auto-fill)
Schedule::call(function () {
    \Log::info('Refreshing featured anime...');
})->daily();

/*
|--------------------------------------------------------------------------
| Scraper Jobs (🔥 CORE FEATURE)
|--------------------------------------------------------------------------
*/

// Auto scraper (Jikan / API import)
Schedule::call(function () {
    \Log::info('Running auto anime scraper...');
    // App\Services\AnimeScraper::run();
})->hourly();

// Manga scraper
Schedule::call(function () {
    \Log::info('Running manga scraper...');
})->everyTwoHours();


/*
|--------------------------------------------------------------------------
| Cleanup Tasks
|--------------------------------------------------------------------------
*/

// Delete old reports (older than 30 days)
Schedule::call(function () {
    \Log::info('Cleaning old reports...');
    \App\Models\Report::where('created_at', '<', now()->subDays(30))->delete();
})->daily();

// Remove temporary uploads
Schedule::call(function () {
    \Log::info('Cleaning temp uploads...');
})->daily();


/*
|--------------------------------------------------------------------------
| Watch History & Optimization
|--------------------------------------------------------------------------
*/

// Compress watch history
Schedule::call(function () {
    \Log::info('Optimizing watch history...');
})->daily();

// Recalculate rankings
Schedule::call(function () {
    \Log::info('Recalculating rankings...');
})->everySixHours();
