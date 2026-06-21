<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\{
    CommentController,
    DashboardController,
    AnimeController as AdminAnimeController,
    EpisodeController,
    GenreController as AdminGenreController,
    FeaturedController,
    JikanController,
    MangaController as AdminMangaController,
    MangaChapterController,
    MangaDashboardController,
    MangaGenreController as AdminMangaGenreController,
    ReportController,
    RequestController as AdminRequestController,
    UserController,
    SettingController,
    ScraperController,
    UploadController
};

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:super_admin,admin'])
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */
        Route::redirect('/', '/admin/dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/manga-dashboard', [MangaDashboardController::class, 'index'])->name('manga.dashboard');

        /*
        |--------------------------------------------------------------------------
        | Anime
        |--------------------------------------------------------------------------
        */
        Route::get('/anime/search', [AdminAnimeController::class, 'index'])
            ->name('anime.search');

        Route::resource('anime', AdminAnimeController::class);

        Route::prefix('anime/{anime}')->name('anime.')->group(function () {

            Route::resource('episodes', EpisodeController::class);

            Route::delete(
                'episodes/{episode}/delete-video',
                [EpisodeController::class, 'deleteVideo']
            )->name('episodes.delete-video');
        });

        /*
        |--------------------------------------------------------------------------
        | Anime Genres
        |--------------------------------------------------------------------------
        */
        Route::prefix('genres')->name('genres.')->group(function () {
            Route::get('/', [AdminGenreController::class, 'index'])->name('index');
            Route::post('/', [AdminGenreController::class, 'store'])->name('store');
            Route::put('/{genre}', [AdminGenreController::class, 'update'])->name('update');
            Route::delete('/{genre}', [AdminGenreController::class, 'destroy'])->name('destroy');
            Route::post('/import-from-mal', [AdminGenreController::class, 'importFromMal'])->name('import');
        });

        /*
        |--------------------------------------------------------------------------
        | Manga
        |--------------------------------------------------------------------------
        */
        Route::resource('manga', AdminMangaController::class);

        Route::prefix('manga/{manga}')->name('manga.')->group(function () {
            Route::resource('chapters', MangaChapterController::class);
        });

        /*
        |--------------------------------------------------------------------------
        | Manga Genres
        |--------------------------------------------------------------------------
        */
        Route::prefix('manga-genres')->name('manga.genres.')->group(function () {
            Route::get('/', [AdminMangaGenreController::class, 'index'])->name('index');
            Route::post('/', [AdminMangaGenreController::class, 'store'])->name('store');
            Route::put('/{mangaGenre}', [AdminMangaGenreController::class, 'update'])->name('update');
            Route::delete('/{mangaGenre}', [AdminMangaGenreController::class, 'destroy'])->name('destroy');
        });

        /*
        |--------------------------------------------------------------------------
        | Featured
        |--------------------------------------------------------------------------
        */
        Route::prefix('featured')->name('featured.')->group(function () {
            Route::get('/', [FeaturedController::class, 'index'])->name('index');
            Route::post('/', [FeaturedController::class, 'update'])->name('update');
            Route::post('/auto-fill', [FeaturedController::class, 'autoFill'])->name('auto');
        });

        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::put('/{user}/role', [UserController::class, 'updateRole'])->name('role');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        });

        /*
        |--------------------------------------------------------------------------
        | Reports & Requests
        |--------------------------------------------------------------------------
        */
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::put('/{report}', [ReportController::class, 'update'])->name('update');
        });

        Route::prefix('requests')->name('requests.')->group(function () {
            Route::get('/', [AdminRequestController::class, 'index'])->name('index');
            Route::put('/{animeRequest}', [AdminRequestController::class, 'update'])->name('update');
        });

        /*
        |--------------------------------------------------------------------------
        | Comments
        |--------------------------------------------------------------------------
        */
        Route::prefix('comments')->name('comments.')->group(function () {
            Route::get('/', [CommentController::class, 'index'])->name('index');
            Route::delete('/anime/{comment}', [CommentController::class, 'destroyAnime'])->name('anime');
            Route::delete('/manga/{mangaComment}', [CommentController::class, 'destroyManga'])->name('manga');
        });

        /*
        |--------------------------------------------------------------------------
        | Settings
        |--------------------------------------------------------------------------
        */
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::post('/', [SettingController::class, 'update'])->name('update');
        });

        /*
        |--------------------------------------------------------------------------
        | Jikan Import
        |--------------------------------------------------------------------------
        */
        Route::prefix('jikan')->name('jikan.')->group(function () {
            Route::get('/', [JikanController::class, 'searchForm'])->name('search');
            Route::post('/search', [JikanController::class, 'search'])->name('results');
            Route::get('/preview/{malId}', [JikanController::class, 'preview'])
                ->whereNumber('malId')
                ->name('preview');

            Route::post('/import/{malId}', [JikanController::class, 'import'])
                ->whereNumber('malId')
                ->name('import');

            Route::post('/batch-import', [JikanController::class, 'batchImport'])->name('batch');
        });

        /*
        |--------------------------------------------------------------------------
        | Scrapers
        |--------------------------------------------------------------------------
        */
        Route::prefix('youtube')->name('youtube.')->group(function () {
            Route::post('/preview', [ScraperController::class, 'youtubePreview'])->name('preview');
            Route::post('/import', [ScraperController::class, 'youtubeImport'])->name('import');
        });

        Route::prefix('telegram')->name('telegram.')->group(function () {
            Route::post('/import', [ScraperController::class, 'telegramImport'])->name('import');
        });

        /*
        |--------------------------------------------------------------------------
        | Upload (Protected)
        |--------------------------------------------------------------------------
        */
        Route::prefix('upload')
            ->name('upload.')
            ->middleware('throttle:uploads')
            ->group(function () {

                Route::post('/file', [UploadController::class, 'store'])->name('file');
                Route::post('/initiate', [UploadController::class, 'initiate'])->name('initiate');
                Route::post('/chunk', [UploadController::class, 'chunk'])->name('chunk');
                Route::get('/status/{upload}', [UploadController::class, 'status'])->name('status');
                Route::delete('/cancel/{upload}', [UploadController::class, 'cancel'])->name('cancel');
            });
    });
