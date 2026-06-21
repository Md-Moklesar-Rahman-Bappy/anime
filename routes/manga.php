<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MangaController;
use App\Http\Controllers\MangaGenreController;
use App\Http\Controllers\MangaListController;
use App\Http\Controllers\MangaRandomController;
use App\Http\Controllers\MangaReaderController;

/*
|--------------------------------------------------------------------------
| Manga Routes
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| MAIN
|--------------------------------------------------------------------------
*/

Route::get('/manga', [MangaListController::class, 'index'])
    ->name('manga.index');

/*
|--------------------------------------------------------------------------
| GENRES
|--------------------------------------------------------------------------
*/
Route::get('/manga/genre/{slug}', [MangaGenreController::class, 'show'])
    ->name('manga.genre')
    ->where('slug', '[A-Za-z0-9\-_]+');

/*
|--------------------------------------------------------------------------
| LIST / FILTER
|--------------------------------------------------------------------------
*/
Route::get('/manga/az-list/{letter?}', [MangaListController::class, 'azList'])
    ->name('manga.az-list');

Route::get('/manga/filter', [MangaListController::class, 'filter'])
    ->name('manga.filter')
    ->middleware('throttle:search');

/*
|--------------------------------------------------------------------------
| CATEGORY
|--------------------------------------------------------------------------
*/
Route::get('/manga/newest', [MangaListController::class, 'newest'])->name('manga.newest');
Route::get('/manga/updated', [MangaListController::class, 'updated'])->name('manga.updated');
Route::get('/manga/ongoing', [MangaListController::class, 'ongoing'])->name('manga.ongoing');
Route::get('/manga/trending', [MangaListController::class, 'trending'])->name('manga.trending');
Route::get('/manga/completed', [MangaListController::class, 'completed'])->name('manga.completed');

/*
|--------------------------------------------------------------------------
| RANDOM
|--------------------------------------------------------------------------
*/
Route::get('/manga/random', [MangaRandomController::class, 'index'])
    ->name('manga.random');

/*
|--------------------------------------------------------------------------
| READER (IMPORTANT)
|--------------------------------------------------------------------------
*/
Route::get('/manga/read/{slug}/{chapter?}', MangaReaderController::class)
    ->name('manga.read')
    ->where('slug', '[A-Za-z0-9\-_]+');

/*
|--------------------------------------------------------------------------
| DETAIL (MUST BE LAST)
|--------------------------------------------------------------------------
*/
Route::get('/manga/{slug}', MangaController::class)
    ->name('manga.detail')
    ->where('slug', '[A-Za-z0-9\-_]+');
