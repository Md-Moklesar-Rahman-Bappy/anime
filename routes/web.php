<?php

use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\FavoritesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MangaCommentsController;
use App\Http\Controllers\MangaFavoritesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaticController;
use App\Http\Controllers\TgStreamController;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Auth
require __DIR__.'/auth.php';

// Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Telegram streaming
Route::get('/tg/{messageId}', [TgStreamController::class, 'stream'])->name('tg.stream');

// Anime routes
require __DIR__.'/anime.php';

// Manga routes
require __DIR__.'/manga.php';

// Comments & Favorites (auth required)
Route::middleware('auth')->group(function () {
    Route::post('/comments', [CommentsController::class, 'store'])->name('comments.store')->middleware('throttle:comments');
    Route::post('/favorites/toggle', [FavoritesController::class, 'toggle'])->name('favorites.toggle')->middleware('throttle:favorites');
    Route::post('/favorites/list', [FavoritesController::class, 'updateList'])->name('favorites.list')->middleware('throttle:favorites');
    Route::post('/reports/submit', [ReportController::class, 'store'])->name('reports.submit')->middleware('throttle:reports');

    Route::post('/manga/favorites/toggle', [MangaFavoritesController::class, 'toggle'])->name('manga.favorites.toggle')->middleware('throttle:favorites');
    Route::post('/manga/favorites/list', [MangaFavoritesController::class, 'updateList'])->name('manga.favorites.list')->middleware('throttle:favorites');
    Route::post('/manga/comments', [MangaCommentsController::class, 'store'])->name('manga.comments.store')->middleware('throttle:comments');
    Route::post('/manga/bookmark', [MangaFavoritesController::class, 'bookmark'])->name('manga.bookmark');
});

// Static pages
Route::get('/faq', [StaticController::class, 'faq'])->name('faq');
Route::get('/about', [StaticController::class, 'about'])->name('about');
Route::get('/contact', [StaticController::class, 'contact'])->name('contact');
Route::get('/dmca', [StaticController::class, 'dmca'])->name('dmca');
Route::get('/terms', [StaticController::class, 'terms'])->name('terms');

// Admin routes
require __DIR__.'/admin.php';
