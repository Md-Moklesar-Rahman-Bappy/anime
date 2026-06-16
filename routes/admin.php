<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AnimeController as AdminAnimeController;
use App\Http\Controllers\Admin\EpisodeController;
use App\Http\Controllers\Admin\GenreController as AdminGenreController;
use App\Http\Controllers\Admin\FeaturedController;
use App\Http\Controllers\Admin\JikanController;
use App\Http\Controllers\Admin\MangaController as AdminMangaController;
use App\Http\Controllers\Admin\MangaChapterController;
use App\Http\Controllers\Admin\MangaGenreController as AdminMangaGenreController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RequestController as AdminRequestController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ScraperController;
use App\Http\Controllers\Admin\UploadController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:super_admin,admin'])
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | Anime
        |--------------------------------------------------------------------------
        */
        Route::get('anime/search', [AdminAnimeController::class, 'index'])->name('anime.search');
        Route::resource('anime', AdminAnimeController::class);

        Route::prefix('anime/{anime}')->name('anime.')->group(function () {
            Route::resource('episodes', EpisodeController::class);
            Route::delete('episodes/{episode}/delete-video', [EpisodeController::class, 'deleteVideo'])
                ->name('episodes.delete-video');
        });


        /*
        |--------------------------------------------------------------------------
        | Genres
        |--------------------------------------------------------------------------
        */
        Route::get('/genres', [AdminGenreController::class, 'index'])->name('genres.index');
        Route::post('/genres', [AdminGenreController::class, 'store'])->name('genres.store');
        Route::put('/genres/{genre}', [AdminGenreController::class, 'update'])->name('genres.update');
        Route::delete('/genres/{genre}', [AdminGenreController::class, 'destroy'])->name('genres.destroy');


        /*
        |--------------------------------------------------------------------------
        | Manga
        |--------------------------------------------------------------------------
        */
        Route::resource('manga', AdminMangaController::class);

        Route::prefix('manga/{manga}')->name('manga.')->group(function () {
            Route::resource('chapters', MangaChapterController::class);
        });

        Route::get('/manga-genres', [AdminMangaGenreController::class, 'index'])->name('manga.genres.index');
        Route::post('/manga-genres', [AdminMangaGenreController::class, 'store'])->name('manga.genres.store');
        Route::put('/manga-genres/{mangaGenre}', [AdminMangaGenreController::class, 'update'])->name('manga.genres.update');
        Route::delete('/manga-genres/{mangaGenre}', [AdminMangaGenreController::class, 'destroy'])->name('manga.genres.destroy');


        /*
        |--------------------------------------------------------------------------
        | Featured
        |--------------------------------------------------------------------------
        */
        Route::get('/featured', [FeaturedController::class, 'index'])->name('featured.index');
        Route::post('/featured', [FeaturedController::class, 'update'])->name('featured.update');
        Route::post('/featured/auto-fill', [FeaturedController::class, 'autoFill'])->name('featured.auto-fill');


        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::put('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.role');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');


        /*
        |--------------------------------------------------------------------------
        | Reports & Requests
        |--------------------------------------------------------------------------
        */
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::put('/reports/{report}', [ReportController::class, 'update'])->name('reports.update');

        Route::get('/requests', [AdminRequestController::class, 'index'])->name('requests.index');
        Route::put('/requests/{animeRequest}', [AdminRequestController::class, 'update'])->name('requests.update');


        /*
        |--------------------------------------------------------------------------
        | Settings
        |--------------------------------------------------------------------------
        */
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');


        /*
        |--------------------------------------------------------------------------
        | Jikan MAL Import
        |--------------------------------------------------------------------------
        */
        Route::prefix('jikan')->name('jikan.')->group(function () {
            Route::get('/', [JikanController::class, 'searchForm'])->name('search');
            Route::post('/search', [JikanController::class, 'search'])->name('search.results');
            Route::get('/preview/{malId}', [JikanController::class, 'preview'])->name('preview');
            Route::post('/import/{malId}', [JikanController::class, 'import'])->name('import');
            Route::post('/batch-import', [JikanController::class, 'batchImport'])->name('batch-import');
            Route::post('/refresh-episodes/{malId}', [JikanController::class, 'refreshEpisodes'])->name('refresh-episodes');
            Route::post('/reset-progress', [JikanController::class, 'resetProgress'])->name('reset-progress');
        });


        /*
        |--------------------------------------------------------------------------
        | YouTube Import
        |--------------------------------------------------------------------------
        */
        Route::prefix('youtube')->name('youtube.')->group(function () {
            Route::post('/preview', [ScraperController::class, 'youtubePreview'])->name('preview');
            Route::post('/import', [ScraperController::class, 'youtubeImport'])->name('import');
        });


        /*
        |--------------------------------------------------------------------------
        | Telegram Import
        |--------------------------------------------------------------------------
        */
        Route::prefix('telegram')->name('telegram.')->group(function () {
            Route::post('/preview', [ScraperController::class, 'telegramPreview'])->name('preview');
            Route::post('/import', [ScraperController::class, 'telegramImport'])->name('import');
        });


        /*
        |--------------------------------------------------------------------------
        | Upload (Chunk + File)
        |--------------------------------------------------------------------------
        */
        Route::prefix('upload')->name('upload.')->group(function () {
            Route::post('/file', [UploadController::class, 'store'])->name('file');
            Route::post('/initiate', [UploadController::class, 'initiate'])->name('initiate');
            Route::post('/chunk', [UploadController::class, 'chunk'])->name('chunk');
            Route::get('/status/{upload}', [UploadController::class, 'status'])->name('status');
            Route::delete('/cancel/{upload}', [UploadController::class, 'cancel'])->name('cancel');
        });

    });
