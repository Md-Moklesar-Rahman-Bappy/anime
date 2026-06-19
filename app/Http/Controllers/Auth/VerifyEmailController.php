<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | VERIFY EMAIL
    |--------------------------------------------------------------------------
    */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        try {
            $user = $request->user();

            /*
            |--------------------------------------------------------------------------
            | Ensure authenticated
            |--------------------------------------------------------------------------
            */
            if (!$user) {
                return redirect()
                    ->route('login')
                    ->with('error', 'Authentication required.');
            }

            /*
            |--------------------------------------------------------------------------
            | Already verified
            |--------------------------------------------------------------------------
            */
            if ($user->hasVerifiedEmail()) {
                return redirect()->intended(
                    route('admin.dashboard', absolute: false) . '?verified=1'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Verify email
            |--------------------------------------------------------------------------
            */
            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }

            /*
            |--------------------------------------------------------------------------
            | Redirect after verification
            |--------------------------------------------------------------------------
            */
            return redirect()->intended(
                route('admin.dashboard', absolute: false) . '?verified=1'
            );

        } catch (\Throwable $e) {

            $this->logError('Email verification failed', $e, [
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
            ]);

            return redirect()
                ->route('login')
                ->with('error', 'Email verification failed. Please try again.');
        }
    }
}