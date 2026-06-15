<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;

// Anime Controllers
use App\Http\Controllers\AnimeController;
use App\Http\Controllers\WatchController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\RandomController;

// Manga Controllers
use App\Http\Controllers\MangaController;
use App\Http\Controllers\MangaListController;
use App\Http\Controllers\MangaGenreController;
use App\Http\Controllers\MangaReaderController;
use App\Http\Controllers\MangaRandomController;

// Social / Features
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\FavoritesController;
use App\Http\Controllers\MangaCommentsController;
use App\Http\Controllers\MangaFavoritesController;
use App\Http\Controllers\StaticController;
use App\Http\Controllers\TgStreamController;

// Admin Controllers
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AnimeController as AdminAnimeController;
use App\Http\Controllers\Admin\EpisodeController;
use App\Http\Controllers\Admin\GenreController as AdminGenreController;
use App\Http\Controllers\Admin\MangaController as AdminMangaController;
use App\Http\Controllers\Admin\MangaChapterController;
use App\Http\Controllers\Admin\MangaGenreController as AdminMangaGenreController;
use App\Http\Controllers\Admin\FeaturedController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RequestController as AdminRequestController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\JikanController;
use App\Http\Controllers\Admin\ScraperController;
use App\Http\Controllers\Admin\UploadController;


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

require __DIR__.'/auth.php';


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
| Anime Routes
|--------------------------------------------------------------------------
*/

Route::get('/watch/{slug}', WatchController::class)->name('watch');

Route::get('/genre/{slug}', GenreController::class)->name('genre');
Route::get('/az-list/{letter?}', [ListController::class, 'azList'])->name('az-list');
Route::get('/filter', [ListController::class, 'filter'])->name('filter');
Route::get('/search/ajax', [ListController::class, 'searchAjax'])->name('search.ajax');
Route::get('/newest', [ListController::class, 'newest'])->name('newest');
Route::get('/updated', [ListController::class, 'updated'])->name('updated');
Route::get('/ongoing', [ListController::class, 'ongoing'])->name('ongoing');
Route::get('/trending', [ListController::class, 'trending'])->name('trending');
Route::get('/random', RandomController::class)->name('random');

/* IMPORTANT: slug route always LAST */
Route::get('/anime/{slug}', AnimeController::class)->name('anime.detail');


/*
|--------------------------------------------------------------------------
| Manga Routes
|--------------------------------------------------------------------------
*/

Route::get('/manga', [MangaListController::class, 'index'])->name('manga.index');

/* FIXED ORDER (IMPORTANT) */
Route::get('/manga/genre/{slug}', MangaGenreController::class)->name('manga.genre');
Route::get('/manga/az-list/{letter?}', [MangaListController::class, 'azList'])->name('manga.az-list');
Route::get('/manga/filter', [MangaListController::class, 'filter'])->name('manga.filter');
Route::get('/manga/newest', [MangaListController::class, 'newest'])->name('manga.newest');
Route::get('/manga/updated', [MangaListController::class, 'updated'])->name('manga.updated');
Route::get('/manga/ongoing', [MangaListController::class, 'ongoing'])->name('manga.ongoing');
Route::get('/manga/trending', [MangaListController::class, 'trending'])->name('manga.trending');
Route::get('/manga/completed', [MangaListController::class, 'completed'])->name('manga.completed');
Route::get('/manga/random', MangaRandomController::class)->name('manga.random');

/* Important: slug route LAST */
Route::get('/manga/{slug}', MangaController::class)->name('manga.detail');

Route::get('/read/{slug}', MangaReaderController::class)->name('manga.read');


/*
|--------------------------------------------------------------------------
| Comments & Favorites
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::post('/comments', [CommentsController::class, 'store'])->name('comments.store')->middleware('throttle:comments');
    Route::delete('/comments/{comment}', [CommentsController::class, 'destroy'])->name('comments.destroy');

    Route::post('/favorites/toggle', [FavoritesController::class, 'toggle'])->name('favorites.toggle')->middleware('throttle:favorites');
    Route::post('/favorites/list', [FavoritesController::class, 'updateList'])->name('favorites.list')->middleware('throttle:favorites');
    Route::get('/my-list', [FavoritesController::class, 'myList'])->name('favorites.my-list');

    Route::post('/reports/submit', [ReportController::class, 'store'])->name('reports.submit');

    // Manga
    Route::post('/manga/favorites/toggle', [MangaFavoritesController::class, 'toggle'])->name('manga.favorites.toggle');
    Route::post('/manga/favorites/list', [MangaFavoritesController::class, 'updateList'])->name('manga.favorites.list');

    Route::post('/manga/comments', [MangaCommentsController::class, 'store'])->name('manga.comments.store');
    Route::delete('/manga/comments/{mangaComment}', [MangaCommentsController::class, 'destroy'])->name('manga.comments.destroy');

    Route::post('/manga/bookmark', [MangaFavoritesController::class, 'bookmark'])->name('manga.bookmark');
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

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:super_admin,admin'])
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('anime', AdminAnimeController::class);

        Route::prefix('anime/{anime}')->name('anime.')->group(function () {
            Route::resource('episodes', EpisodeController::class);
            Route::delete('episodes/{episode}/delete-video', [EpisodeController::class, 'deleteVideo'])->name('episodes.delete-video');
        });

        Route::resource('manga', AdminMangaController::class);

        Route::prefix('manga/{manga}')->name('manga.')->group(function () {
            Route::resource('chapters', MangaChapterController::class);
        });

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::put('/reports/{report}', [ReportController::class, 'update'])->name('reports.update');

        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    });