<?php

use Illuminate\Support\Facades\Route;

// Core Controllers
use App\Http\Controllers\{
    HomeController,
    ProfileController,
    TgStreamController,
    StreamProxyController,
    StaticController,
    CommentsController,
    FavoritesController,
    MangaCommentsController,
    MangaFavoritesController
};

use App\Http\Controllers\Admin\ReportController;

/*
|--------------------------------------------------------------------------
| HOME
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');


/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';


/*
|--------------------------------------------------------------------------
| PROFILE
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


/*
|--------------------------------------------------------------------------
| STREAMING (IMPORTANT)
|--------------------------------------------------------------------------
*/
Route::prefix('stream')->name('stream.')->group(function () {

    Route::get('/tg/{messageId}', [TgStreamController::class, 'stream'])
        ->whereNumber('messageId')
        ->name('tg');

    Route::get('/proxy', [StreamProxyController::class, 'stream'])
        ->middleware('throttle:streams')
        ->name('proxy');
});


/*
|--------------------------------------------------------------------------
| CONTENT ROUTES
|--------------------------------------------------------------------------
*/
require __DIR__ . '/anime.php';
require __DIR__ . '/manga.php';


/*
|--------------------------------------------------------------------------
| USER FEATURES (AUTH)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | ANIME
    |--------------------------------------------------------------------------
    */
    Route::post('/comments', [CommentsController::class, 'store'])
        ->name('comments.store')
        ->middleware('throttle:comments');

    Route::delete('/comments/{comment}', [CommentsController::class, 'destroy'])
        ->name('comments.destroy');

    Route::post('/favorites/toggle', [FavoritesController::class, 'toggle'])
        ->name('favorites.toggle')
        ->middleware('throttle:favorites');

    Route::post('/favorites/list', [FavoritesController::class, 'updateList'])
        ->name('favorites.list')
        ->middleware('throttle:favorites');

    Route::get('/my-list', [FavoritesController::class, 'myList'])
        ->name('favorites.my-list');

    /*
    |--------------------------------------------------------------------------
    | MANGA
    |--------------------------------------------------------------------------
    */
    Route::post('/manga/comments', [MangaCommentsController::class, 'store'])
        ->name('manga.comments.store');

    Route::delete('/manga/comments/{mangaComment}', [MangaCommentsController::class, 'destroy'])
        ->name('manga.comments.destroy');

    Route::post('/manga/favorites/toggle', [MangaFavoritesController::class, 'toggle'])
        ->name('manga.favorites.toggle');

    Route::post('/manga/favorites/list', [MangaFavoritesController::class, 'updateList'])
        ->name('manga.favorites.list');

    Route::post('/manga/bookmark', [MangaFavoritesController::class, 'bookmark'])
        ->name('manga.bookmark');

    /*
    |--------------------------------------------------------------------------
    | REPORTS
    |--------------------------------------------------------------------------
    */
    Route::post('/reports', [ReportController::class, 'store'])
        ->name('reports.store');
});


/*
|--------------------------------------------------------------------------
| STATIC PAGES
|--------------------------------------------------------------------------
*/
Route::get('/{page}', [StaticController::class, 'show'])
    ->where('page', 'faq|about|contact|dmca|terms')
    ->name('static.page');


/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/
require __DIR__ . '/admin.php';
