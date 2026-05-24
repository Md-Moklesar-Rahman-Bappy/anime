<?php

use App\Http\Controllers\Admin\AnimeController as AdminAnimeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EpisodeController;
use App\Http\Controllers\Admin\FeaturedController;
use App\Http\Controllers\Admin\GenreController as AdminGenreController;
use App\Http\Controllers\Admin\JikanController;
use App\Http\Controllers\Admin\MangaChapterController;
use App\Http\Controllers\Admin\MangaController as AdminMangaController;
use App\Http\Controllers\Admin\MangaGenreController as AdminMangaGenreController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RequestController as AdminRequestController;
use App\Http\Controllers\Admin\ScraperController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UploadController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AnimeController;
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\FavoritesController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\MangaCommentsController;
use App\Http\Controllers\MangaController;
use App\Http\Controllers\MangaFavoritesController;
use App\Http\Controllers\MangaGenreController;
use App\Http\Controllers\MangaListController;
use App\Http\Controllers\MangaRandomController;
use App\Http\Controllers\MangaReaderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RandomController;
use App\Http\Controllers\StaticController;
use App\Http\Controllers\TgStreamController;
use App\Http\Controllers\WatchController;
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

// Telegram streaming (public, used by Plyr player)
Route::get('/tg/{messageId}', [TgStreamController::class, 'stream'])->name('tg.stream');

// Anime pages
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

// Manga pages
Route::get('/manga', [MangaListController::class, 'index'])->name('manga.index');
Route::get('/manga/{slug}', MangaController::class)->name('manga.detail');
Route::get('/read/{slug}', MangaReaderController::class)->name('manga.read');
Route::get('/manga/genre/{slug}', MangaGenreController::class)->name('manga.genre');
Route::get('/manga/az-list/{letter?}', [MangaListController::class, 'azList'])->name('manga.az-list');
Route::get('/manga/filter', [MangaListController::class, 'filter'])->name('manga.filter');
Route::get('/manga/newest', [MangaListController::class, 'newest'])->name('manga.newest');
Route::get('/manga/updated', [MangaListController::class, 'updated'])->name('manga.updated');
Route::get('/manga/ongoing', [MangaListController::class, 'ongoing'])->name('manga.ongoing');
Route::get('/manga/trending', [MangaListController::class, 'trending'])->name('manga.trending');
Route::get('/manga/completed', [MangaListController::class, 'completed'])->name('manga.completed');
Route::get('/manga/random', MangaRandomController::class)->name('manga.random');

// Comments & Favorites (auth required)
Route::middleware('auth')->group(function () {
    Route::post('/comments', [CommentsController::class, 'store'])->name('comments.store')->middleware('throttle:comments');
    Route::post('/favorites/toggle', [FavoritesController::class, 'toggle'])->name('favorites.toggle')->middleware('throttle:favorites');
    Route::post('/favorites/list', [FavoritesController::class, 'updateList'])->name('favorites.list')->middleware('throttle:favorites');
    Route::post('/reports/submit', [ReportController::class, 'store'])->name('reports.submit')->middleware('throttle:reports');

    // Manga auth routes
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
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:super_admin,admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('anime/search', [AdminAnimeController::class, 'index'])->name('anime.search');
    Route::resource('anime', AdminAnimeController::class);
    Route::prefix('anime/{anime}')->name('anime.')->group(function () {
        Route::resource('episodes', EpisodeController::class);
        Route::delete('episodes/{episode}/delete-video', [EpisodeController::class, 'deleteVideo'])->name('episodes.delete-video');
    });

    Route::get('/genres', [AdminGenreController::class, 'index'])->name('genres.index');
    Route::post('/genres', [AdminGenreController::class, 'store'])->name('genres.store');
    Route::put('/genres/{genre}', [AdminGenreController::class, 'update'])->name('genres.update');
    Route::delete('/genres/{genre}', [AdminGenreController::class, 'destroy'])->name('genres.destroy');

    // Manga admin
    Route::resource('manga', AdminMangaController::class);
    Route::prefix('manga/{manga}')->name('manga.')->group(function () {
        Route::resource('chapters', MangaChapterController::class);
    });
    Route::get('/manga-genres', [AdminMangaGenreController::class, 'index'])->name('manga.genres.index');
    Route::post('/manga-genres', [AdminMangaGenreController::class, 'store'])->name('manga.genres.store');
    Route::put('/manga-genres/{mangaGenre}', [AdminMangaGenreController::class, 'update'])->name('manga.genres.update');
    Route::delete('/manga-genres/{mangaGenre}', [AdminMangaGenreController::class, 'destroy'])->name('manga.genres.destroy');

    Route::get('/featured', [FeaturedController::class, 'index'])->name('featured.index');
    Route::post('/featured', [FeaturedController::class, 'update'])->name('featured.update');
    Route::post('/featured/auto-fill', [FeaturedController::class, 'autoFill'])->name('featured.auto-fill');

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
        Route::post('/refresh-episodes/{malId}', [JikanController::class, 'refreshEpisodes'])->name('refresh-episodes');
        Route::post('/reset-progress', [JikanController::class, 'resetProgress'])->name('reset-progress');
    });

    // YouTube Import
    Route::prefix('youtube')->name('youtube.')->group(function () {
        Route::post('/preview', [ScraperController::class, 'youtubePreview'])->name('preview');
        Route::post('/import', [ScraperController::class, 'youtubeImport'])->name('import');
    });

    // Telegram Import
    Route::prefix('telegram')->name('telegram.')->group(function () {
        Route::post('/preview', [ScraperController::class, 'telegramPreview'])->name('preview');
        Route::post('/import', [ScraperController::class, 'telegramImport'])->name('import');
    });

    // Upload
    Route::prefix('upload')->name('upload.')->group(function () {
        Route::post('/file', [UploadController::class, 'store'])->name('file');
        Route::post('/initiate', [UploadController::class, 'initiate'])->name('initiate');
        Route::post('/chunk', [UploadController::class, 'chunk'])->name('chunk');
        Route::get('/status/{upload}', [UploadController::class, 'status'])->name('status');
        Route::delete('/cancel/{upload}', [UploadController::class, 'cancel'])->name('cancel');
    });
});
