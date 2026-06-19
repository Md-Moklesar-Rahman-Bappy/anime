<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | SEND VERIFICATION EMAIL
    |--------------------------------------------------------------------------
    */
    public function store(Request $request): RedirectResponse
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
            | Rate limiting (session-based)
            |--------------------------------------------------------------------------
            */
            $lastSent = (int) $request->session()->get('verification_last_sent', 0);

            if ((time() - $lastSent) < 30) {
                return back()->with('error', 'Please wait before requesting another email.');
            }

            /*
            |--------------------------------------------------------------------------
            | Send email
            |--------------------------------------------------------------------------
            */
            $user->sendEmailVerificationNotification();

            /*
            |--------------------------------------------------------------------------
            | Store timestamp
            |--------------------------------------------------------------------------
            */
            $request->session()->put('verification_last_sent', time());

            return back()->with('status', 'verification-link-sent');
        } catch (\Throwable $e) {

            $this->logError('Email verification send failed', $e, [
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
            ]);

            return back()->with('error', 'Failed to send verification email.');
        }
    }
}
