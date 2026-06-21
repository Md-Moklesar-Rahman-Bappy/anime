<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;

Route::prefix('auth')->name('auth.')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | GUEST ROUTES
    |--------------------------------------------------------------------------
    */
    Route::middleware('guest')->group(function () {

        Route::get('/register', [RegisteredUserController::class, 'create'])
            ->name('register');

        Route::post('/register', [RegisteredUserController::class, 'store'])
            ->middleware('throttle:register');

        Route::get('/login', [AuthenticatedSessionController::class, 'create'])
            ->name('login');

        Route::post('/login', [AuthenticatedSessionController::class, 'store'])
            ->middleware('throttle:login');

        Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
            ->name('password.request');

        Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
            ->name('password.email')
            ->middleware('throttle:passwords');

        Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
            ->name('password.reset');

        Route::post('/reset-password', [NewPasswordController::class, 'store'])
            ->name('password.store');
    });

    /*
    |--------------------------------------------------------------------------
    | AUTHENTICATED USERS
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Email Verification
        |--------------------------------------------------------------------------
        */
        Route::get('/verify-email', EmailVerificationPromptController::class)
            ->name('verification.notice');

        Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify')
            ->whereNumber('id');

        Route::post(
            '/email/verification-notification',
            [EmailVerificationNotificationController::class, 'store']
        )
            ->middleware('throttle:6,1')
            ->name('verification.send');

        /*
        |--------------------------------------------------------------------------
        | Password Management
        |--------------------------------------------------------------------------
        */
        Route::get('/confirm-password', [ConfirmablePasswordController::class, 'show'])
            ->name('password.confirm');

        Route::post('/confirm-password', [ConfirmablePasswordController::class, 'store']);

        Route::put('/password', [PasswordController::class, 'update'])
            ->name('password.update');

        /*
        |--------------------------------------------------------------------------
        | Logout
        |--------------------------------------------------------------------------
        */
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
            ->name('logout');
    });
});
