<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ConfirmablePasswordController extends Controller
{
    /**
     * Show the confirm password view.
     */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Confirm the user's password.
     */
    public function store(Request $request): RedirectResponse
    {
        // ✅ Validate input
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        try {
            $user = $request->user();

            if (!$user) {
                return redirect()
                    ->route('login')
                    ->with('error', 'Authentication required.');
            }

            // ✅ Validate password
            if (!Auth::guard('web')->validate([
                'email' => $user->email,
                'password' => $request->password,
            ])) {
                throw ValidationException::withMessages([
                    'password' => __('auth.password'),
                ]);
            }

            // ✅ Store confirmation timestamp
            $request->session()->put('auth.password_confirmed_at', time());

            // ✅ Flexible redirect (fallback to admin dashboard)
            return redirect()->intended(
                route('admin.dashboard', absolute: false)
            );
        } catch (\Throwable $e) {
            Log::error('Password confirmation failed', [
                'user_id' => $request->user()?->id,
                'error'   => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'password' => 'An unexpected error occurred. Please try again.',
            ]);
        }
    }
}
