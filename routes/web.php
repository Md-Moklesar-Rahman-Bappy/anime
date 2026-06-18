<?php

use Illuminate\Support\Facades\Route;

// Core Controllers
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TgStreamController;
use App\Http\Controllers\StaticController;

// Social / Features
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\FavoritesController;
use App\Http\Controllers\MangaCommentsController;
use App\Http\Controllers\MangaFavoritesController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Streaming
|--------------------------------------------------------------------------
*/
Route::get('/tg/{messageId}', [TgStreamController::class, 'stream'])->name('tg.stream');

/*
|--------------------------------------------------------------------------
| Anime + Manga Routes (loaded from separate files)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/anime.php';
require __DIR__ . '/manga.php';

/*
|--------------------------------------------------------------------------
| Comments / Favorites / Reports
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Anime comments
    Route::post('/comments', [CommentsController::class, 'store'])
        ->name('comments.store')
        ->middleware('throttle:comments');

    Route::delete('/comments/{comment}', [CommentsController::class, 'destroy'])
        ->name('comments.destroy');

    // Anime favorites
    Route::post('/favorites/toggle', [FavoritesController::class, 'toggle'])
        ->name('favorites.toggle')
        ->middleware('throttle:favorites');

    Route::post('/favorites/list', [FavoritesController::class, 'updateList'])
        ->name('favorites.list')
        ->middleware('throttle:favorites');

    Route::get('/my-list', [FavoritesController::class, 'myList'])
        ->name('favorites.my-list');

    // Reports
    Route::post('/reports', [ReportController::class, 'store'])
        ->name('reports.store');

    // Manga favorites
    Route::post('/manga/favorites/toggle', [MangaFavoritesController::class, 'toggle'])
        ->name('manga.favorites.toggle');

    Route::post('/manga/favorites/list', [MangaFavoritesController::class, 'updateList'])
        ->name('manga.favorites.list');

    // Manga comments
    Route::post('/manga/comments', [MangaCommentsController::class, 'store'])
        ->name('manga.comments.store');

    Route::delete('/manga/comments/{mangaComment}', [MangaCommentsController::class, 'destroy'])
        ->name('manga.comments.destroy');

    // Manga bookmark
    Route::post('/manga/bookmark', [MangaFavoritesController::class, 'bookmark'])
        ->name('manga.bookmark');
});

/*
|--------------------------------------------------------------------------
| Static Pages
|--------------------------------------------------------------------------
*/
Route::get('/faq', [StaticController::class, 'faq'])->name('faq');
Route::get('/about', [StaticController::class, 'about'])->name('about');
Route::get('/contact', [StaticController::class, 'contact'])->name('contact');
Route::get('/dmca', [StaticController::class, 'dmca'])->name('dmca');
Route::get('/terms', [StaticController::class, 'terms'])->name('terms');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
require __DIR__ . '/admin.php';
