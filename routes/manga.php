<?php

use App\Http\Controllers\MangaController;
use App\Http\Controllers\MangaGenreController;
use App\Http\Controllers\MangaListController;
use App\Http\Controllers\MangaRandomController;
use App\Http\Controllers\MangaReaderController;
use Illuminate\Support\Facades\Route;

// Specific routes MUST come before the generic {slug} catch-all
Route::get('/manga', [MangaListController::class, 'index'])->name('manga.index');
Route::get('/manga/genre/{slug}', MangaGenreController::class)->name('manga.genre');
Route::get('/manga/az-list/{letter?}', [MangaListController::class, 'azList'])->name('manga.az-list');
Route::get('/manga/filter', [MangaListController::class, 'filter'])->name('manga.filter');
Route::get('/manga/newest', [MangaListController::class, 'newest'])->name('manga.newest');
Route::get('/manga/updated', [MangaListController::class, 'updated'])->name('manga.updated');
Route::get('/manga/ongoing', [MangaListController::class, 'ongoing'])->name('manga.ongoing');
Route::get('/manga/trending', [MangaListController::class, 'trending'])->name('manga.trending');
Route::get('/manga/completed', [MangaListController::class, 'completed'])->name('manga.completed');
Route::get('/manga/random', MangaRandomController::class)->name('manga.random');
// Catch-all must be last
Route::get('/read/{slug}', MangaReaderController::class)->name('manga.read');
Route::get('/manga/{slug}', MangaController::class)->name('manga.detail');
