<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $user = $request->user();

            // ✅ Ensure user exists
            if (!$user) {
                return redirect()
                    ->route('login')
                    ->with('error', 'Authentication required.');
            }

            // ✅ Already verified
            if ($user->hasVerifiedEmail()) {
                return redirect()->intended(
                    route('admin.dashboard', absolute: false)
                );
            }

            // ✅ Rate limiting (extra protection)
            if ($request->session()->has('verification_last_sent')) {
                $lastSent = $request->session()->get('verification_last_sent');

                if (time() - $lastSent < 30) {
                    return back()->with('error', 'Please wait before requesting another email.');
                }
            }

            // ✅ Send notification
            $user->sendEmailVerificationNotification();

            // ✅ Save last sent time
            $request->session()->put('verification_last_sent', time());

            return back()->with('status', 'verification-link-sent');

        } catch (\Throwable $e) {
            Log::error('Email verification send failed', [
                'user_id' => $request->user()?->id,
                'error'   => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to send verification email.');
        }
    }
}