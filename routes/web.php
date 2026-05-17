<?php

use App\Http\Controllers\Admin\AnimeController as AdminAnimeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EpisodeController;
use App\Http\Controllers\Admin\GenreController as AdminGenreController;
use App\Http\Controllers\Admin\JikanController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RequestController as AdminRequestController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AnimeController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\RandomController;
use App\Http\Controllers\StaticController;
use App\Http\Controllers\WatchController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Auth routes
require __DIR__.'/auth.php';

// Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Anime pages
Route::get('/watch/{slug}', WatchController::class)->name('watch');
Route::get('/anime/{slug}', AnimeController::class)->name('anime.detail');
Route::get('/genre/{slug}', GenreController::class)->name('genre');
Route::get('/az-list/{letter?}', [ListController::class, 'azList'])->name('az-list');
Route::get('/filter', [ListController::class, 'filter'])->name('filter');
Route::get('/newest', [ListController::class, 'newest'])->name('newest');
Route::get('/updated', [ListController::class, 'updated'])->name('updated');
Route::get('/ongoing', [ListController::class, 'ongoing'])->name('ongoing');
Route::get('/trending', [ListController::class, 'trending'])->name('trending');
Route::get('/random', RandomController::class)->name('random');

// Comments & Favorites (auth required)
Route::middleware('auth')->group(function () {
    Route::post('/comments', [App\Http\Controllers\CommentsController::class, 'store'])->name('comments.store');
    Route::post('/favorites/toggle', [App\Http\Controllers\FavoritesController::class, 'toggle'])->name('favorites.toggle');
});

// Static pages
Route::get('/faq', [StaticController::class, 'faq'])->name('faq');
Route::get('/about', [StaticController::class, 'about'])->name('about');
Route::get('/contact', [StaticController::class, 'contact'])->name('contact');
Route::get('/dmca', [StaticController::class, 'dmca'])->name('dmca');
Route::get('/terms', [StaticController::class, 'terms'])->name('terms');

// Admin routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:super_admin,admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('anime', AdminAnimeController::class);
    Route::prefix('anime/{anime}')->name('anime.')->group(function () {
        Route::resource('episodes', EpisodeController::class);
    });

    Route::get('/genres', [AdminGenreController::class, 'index'])->name('genres.index');
    Route::post('/genres', [AdminGenreController::class, 'store'])->name('genres.store');
    Route::put('/genres/{genre}', [AdminGenreController::class, 'update'])->name('genres.update');
    Route::delete('/genres/{genre}', [AdminGenreController::class, 'destroy'])->name('genres.destroy');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::put('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.role');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::put('/reports/{report}', [ReportController::class, 'update'])->name('reports.update');

    Route::get('/requests', [AdminRequestController::class, 'index'])->name('requests.index');
    Route::put('/requests/{animeRequest}', [AdminRequestController::class, 'update'])->name('requests.update');

    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

    // Jikan MAL import
    Route::prefix('jikan')->name('jikan.')->group(function () {
        Route::get('/', [JikanController::class, 'searchForm'])->name('search');
        Route::post('/search', [JikanController::class, 'search'])->name('search.results');
        Route::get('/preview/{malId}', [JikanController::class, 'preview'])->name('preview');
        Route::post('/import/{malId}', [JikanController::class, 'import'])->name('import');
        Route::post('/batch-import', [JikanController::class, 'batchImport'])->name('batch-import');
        Route::post('/reset-progress', [JikanController::class, 'resetProgress'])->name('reset-progress');
    });
});
