<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     */
    public function store(Request $request): RedirectResponse
    {
        // ✅ Validate input
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            // ✅ Optional rate limiting (session-based)
            if ($request->session()->has('password_reset_last_sent')) {
                $lastSent = $request->session()->get('password_reset_last_sent');

                if (time() - $lastSent < 30) {
                    return back()->with('error', 'Please wait before requesting another reset link.');
                }
            }

            // ✅ Send reset link
            $status = Password::sendResetLink(
                $request->only('email')
            );

            // ✅ Success
            if ($status === Password::RESET_LINK_SENT) {
                $request->session()->put('password_reset_last_sent', time());

                return back()->with('status', __($status));
            }

            // ✅ Failure response
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => __($status),
                ]);

        } catch (\Throwable $e) {
            Log::error('Password reset link failed', [
                'email' => $request->input('email'),
                'error' => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'email' => 'Something went wrong. Please try again.',
            ]);
        }
    }
}