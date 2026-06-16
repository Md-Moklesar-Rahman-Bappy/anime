<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        try {
            $user = $request->user();

            // ✅ Safety check
            if (!$user) {
                return redirect()
                    ->route('auth.login')
                    ->with('error', 'Authentication required.');
            }

            // ✅ Already verified
            if ($user->hasVerifiedEmail()) {
                return redirect()->intended(
                    route('admin.dashboard', absolute: false) . '?verified=1'
                );
            }

            // ✅ Verify email
            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }

            return redirect()->intended(
                route('admin.dashboard', absolute: false) . '?verified=1'
            );

        } catch (\Throwable $e) {
            Log::error('Email verification failed', [
                'user_id' => $request->user()?->id,
                'error'   => $e->getMessage(),
            ]);

            return redirect()
                ->route('auth.login')
                ->with('error', 'Email verification failed. Please try again.');
        }
    }
}