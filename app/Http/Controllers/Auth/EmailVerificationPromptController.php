<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | SHOW VERIFICATION PROMPT
    |--------------------------------------------------------------------------
    */
    public function __invoke(Request $request): RedirectResponse|View
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
                    route('admin.dashboard', absolute: false)
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Show verification page
            |--------------------------------------------------------------------------
            */
            return view('auth.verify-email');
        } catch (\Throwable $e) {

            $this->logError('Email verification prompt failed', $e, [
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
            ]);

            return redirect()
                ->route('login')
                ->with('error', 'Something went wrong. Please try again.');
        }
    }
}
