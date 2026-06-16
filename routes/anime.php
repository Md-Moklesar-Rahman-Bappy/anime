<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AnimeController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\RandomController;
use App\Http\Controllers\WatchController;

/*
|--------------------------------------------------------------------------
| Anime Routes
|--------------------------------------------------------------------------
|
| RULES:
| - Specific routes MUST come first
| - Generic {slug} MUST be LAST
|
*/

Route::prefix('anime')->name('anime.')->group(function () {

    // Listing & filters
    Route::get('/az-list/{letter?}', [ListController::class, 'azList'])->name('az-list');
    Route::get('/filter', [ListController::class, 'filter'])->name('filter');
    Route::get('/search/ajax', [ListController::class, 'searchAjax'])->name('search.ajax');

    // Categories
    Route::get('/newest', [ListController::class, 'newest'])->name('newest');
    Route::get('/updated', [ListController::class, 'updated'])->name('updated');
    Route::get('/ongoing', [ListController::class, 'ongoing'])->name('ongoing');
    Route::get('/trending', [ListController::class, 'trending'])->name('trending');

    // Random
    Route::get('/random', RandomController::class)->name('random');

    // Genre
    Route::get('/genre/{slug}', GenreController::class)->name('genre');

    // IMPORTANT: catch-all must be LAST
    Route::get('/{slug}', AnimeController::class)->name('detail');
});

/*
|--------------------------------------------------------------------------
| Watch Page (separate to avoid conflict)
|--------------------------------------------------------------------------
*/

Route::get('/watch/{slug}', WatchController::class)->name('watch');
