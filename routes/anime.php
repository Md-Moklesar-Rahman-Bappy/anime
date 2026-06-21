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
| RULES:
| - Specific routes FIRST
| - Catch-all LAST
*/

/*
|--------------------------------------------------------------------------
| WATCH
|--------------------------------------------------------------------------
*/

Route::get('/watch/{slug}', [WatchController::class, 'index'])
    ->name('watch')
    ->where('slug', '[A-Za-z0-9\-_]+');

/*
|--------------------------------------------------------------------------
| GENRES
|--------------------------------------------------------------------------
*/
Route::get('/genre/{slug}', [GenreController::class, 'show'])
    ->name('genre')
    ->where('slug', '[A-Za-z0-9\-_]+');

/*
|--------------------------------------------------------------------------
| LIST / FILTER / SEARCH
|--------------------------------------------------------------------------
*/
Route::get('/az-list/{letter?}', [ListController::class, 'azList'])
    ->name('az-list');

Route::get('/filter', [ListController::class, 'filter'])
    ->name('filter');

Route::get('/search/ajax', [ListController::class, 'searchAjax'])
    ->name('search.ajax')
    ->middleware('throttle:search');

/*
|--------------------------------------------------------------------------
| CATEGORY PAGES
|--------------------------------------------------------------------------
*/
Route::get('/newest', [ListController::class, 'newest'])->name('newest');
Route::get('/updated', [ListController::class, 'updated'])->name('updated');
Route::get('/ongoing', [ListController::class, 'ongoing'])->name('ongoing');
Route::get('/trending', [ListController::class, 'trending'])->name('trending');

/*
|--------------------------------------------------------------------------
| RANDOM
|--------------------------------------------------------------------------
*/
Route::get('/random', [RandomController::class, 'index'])
    ->name('random');

/*
|--------------------------------------------------------------------------
| ANIME DETAIL (IMPORTANT → LAST)
|--------------------------------------------------------------------------
*/
Route::get('/anime/{slug}', [AnimeController::class, 'show'])
    ->name('anime.detail')
    ->where('slug', '[A-Za-z0-9\-_]+');
