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
| System Maintenance
|--------------------------------------------------------------------------
*/

Schedule::command('cache:clear')
    ->dailyAt('04:00')
    ->withoutOverlapping();

Schedule::command('route:clear')
    ->weekly()
    ->withoutOverlapping();

Schedule::command('view:clear')
    ->daily()
    ->withoutOverlapping();

Schedule::command('session:prune')
    ->daily()
    ->withoutOverlapping();

Schedule::command('auth:clear-resets')
    ->daily()
    ->withoutOverlapping();


/*
|--------------------------------------------------------------------------
| Platform Tasks
|--------------------------------------------------------------------------
*/

Schedule::call(function () {
    // app(ViewCounterService::class)->flushBuffered(...);
})
    ->name('views.aggregate') // ✅ REQUIRED
    ->everyThirtyMinutes()
    ->withoutOverlapping();

Schedule::call(function () {
    // app(FeaturedService::class)->autoFill();
})
    ->name('featured.refresh') // ✅ REQUIRED
    ->daily()
    ->withoutOverlapping();


/*
|--------------------------------------------------------------------------
| Scrapers
|--------------------------------------------------------------------------
*/

Schedule::call(function () {
    // dispatch(new AutoAnimeImportJob());
})
    ->name('anime.scraper') // ✅ REQUIRED
    ->hourly()
    ->withoutOverlapping();

Schedule::call(function () {
    // dispatch(new MangaScraperJob());
})
    ->name('manga.scraper') // ✅ REQUIRED
    ->everyTwoHours()
    ->withoutOverlapping();


/*
|--------------------------------------------------------------------------
| Cleanup Tasks
|--------------------------------------------------------------------------
*/

Schedule::call(function () {
    \App\Models\Report::where('created_at', '<', now()->subDays(30))->delete();
})
    ->name('reports.cleanup') // ✅ REQUIRED
    ->daily()
    ->withoutOverlapping();

Schedule::call(function () {
    // Storage::disk('temp')->delete(...);
})
    ->name('uploads.cleanup') // ✅ REQUIRED
    ->daily()
    ->withoutOverlapping();


/*
|--------------------------------------------------------------------------
| Optimization Tasks
|--------------------------------------------------------------------------
*/

Schedule::call(function () {
    // app(WatchHistoryService::class)->optimize();
})
    ->name('watch.optimize') // ✅ REQUIRED
    ->daily()
    ->withoutOverlapping();

Schedule::call(function () {
    // app(RankingService::class)->recalculate();
})
    ->name('ranking.recalculate') // ✅ REQUIRED
    ->everySixHours()
    ->withoutOverlapping();
