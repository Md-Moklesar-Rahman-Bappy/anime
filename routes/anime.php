<?php

use App\Http\Controllers\AnimeController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\RandomController;
use App\Http\Controllers\WatchController;
use Illuminate\Support\Facades\Route;

Route::get('/watch/{slug}', WatchController::class)->name('watch');
Route::get('/anime/{slug}', AnimeController::class)->name('anime.detail');
Route::get('/genre/{slug}', GenreController::class)->name('genre');
Route::get('/az-list/{letter?}', [ListController::class, 'azList'])->name('az-list');
Route::get('/filter', [ListController::class, 'filter'])->name('filter');
Route::get('/search/ajax', [ListController::class, 'searchAjax'])->name('search.ajax');
Route::get('/newest', [ListController::class, 'newest'])->name('newest');
Route::get('/updated', [ListController::class, 'updated'])->name('updated');
Route::get('/ongoing', [ListController::class, 'ongoing'])->name('ongoing');
Route::get('/trending', [ListController::class, 'trending'])->name('trending');
Route::get('/random', RandomController::class)->name('random');
