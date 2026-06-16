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
|
| IMPORTANT:
| - Specific routes MUST come first
| - Generic {slug} route MUST come last
|
*/

Route::prefix('manga')->name('manga.')->group(function () {

    // Homepage
    Route::get('/', [MangaListController::class, 'index'])->name('index');

    // Specific routes (VERY IMPORTANT ORDER)
    Route::get('/genre/{slug}', MangaGenreController::class)->name('genre');
    Route::get('/az-list/{letter?}', [MangaListController::class, 'azList'])->name('az-list');
    Route::get('/filter', [MangaListController::class, 'filter'])->name('filter');
    Route::get('/newest', [MangaListController::class, 'newest'])->name('newest');
    Route::get('/updated', [MangaListController::class, 'updated'])->name('updated');
    Route::get('/ongoing', [MangaListController::class, 'ongoing'])->name('ongoing');
    Route::get('/trending', [MangaListController::class, 'trending'])->name('trending');
    Route::get('/completed', [MangaListController::class, 'completed'])->name('completed');
    Route::get('/random', MangaRandomController::class)->name('random');

    // Catch-all MUST be last
    Route::get('/{slug}', MangaController::class)->name('detail');
});

/*
|--------------------------------------------------------------------------
| Reader (separate to avoid conflict with /manga/{slug})
|--------------------------------------------------------------------------
*/

Route::get('/read/{slug}', MangaReaderController::class)->name('manga.read');
