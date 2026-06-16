<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        try {
            $user = $request->user();

            // ✅ Ensure user is authenticated
            if (!$user) {
                return redirect()
                    ->route('auth.login')
                    ->with('error', 'Authentication required.');
            }

            // ✅ Already verified → redirect
            if ($user->hasVerifiedEmail()) {
                return redirect()->intended(
                    route('admin.dashboard', absolute: false)
                );
            }

            // ✅ Show verification prompt
            return view('auth.verify-email');

        } catch (\Throwable $e) {
            Log::error('Email verification prompt failed', [
                'user_id' => $request->user()?->id,
                'error'   => $e->getMessage(),
            ]);

            return redirect()
                ->route('auth.login')
                ->with('error', 'Something went wrong. Please try again.');
        }
    }
}