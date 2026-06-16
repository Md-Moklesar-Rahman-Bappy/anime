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

// Homepage
Route::get('/manga', [MangaListController::class, 'index'])->name('manga.index');

// Specific routes
Route::get('/manga/genre/{slug}', [MangaGenreController::class, 'show'])
    ->name('manga.genre')
    ->where('slug', '[a-zA-Z0-9\-\_]+');

Route::get('/manga/az-list/{letter?}', [MangaListController::class, 'azList'])->name('manga.az-list');
Route::get('/manga/filter', [MangaListController::class, 'filter'])->name('manga.filter');

Route::get('/manga/newest', [MangaListController::class, 'newest'])->name('manga.newest');
Route::get('/manga/updated', [MangaListController::class, 'updated'])->name('manga.updated');
Route::get('/manga/ongoing', [MangaListController::class, 'ongoing'])->name('manga.ongoing');
Route::get('/manga/trending', [MangaListController::class, 'trending'])->name('manga.trending');
Route::get('/manga/completed', [MangaListController::class, 'completed'])->name('manga.completed');

Route::get('/manga/random', [MangaRandomController::class, 'index'])->name('manga.random');

<<<<<<< HEAD
// ✅ FIXED ROUTE (IMPORTANT)
Route::get('/manga/{slug}', MangaController::class)
    ->name('manga.detail')
    ->where('slug', '[a-zA-Z0-9\-\_]+');

// Reader
Route::get('/read/{slug}', [MangaReaderController::class, 'index'])
    ->name('manga.read')
    ->where('slug', '[a-zA-Z0-9\-\_]+');
=======
Route::get('/read/{slug}', MangaReaderController::class)->name('manga.read');
>>>>>>> 69efe2ee0ae0a15e36d5429779cd8c2f83671234
