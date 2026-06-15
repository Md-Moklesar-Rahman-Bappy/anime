<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', [
            'request' => $request,
        ]);
    }

    /**
     * Handle an incoming new password request.
     */
    public function store(Request $request): RedirectResponse
    {
        // ✅ Validate input
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),

                function (User $user) use ($request) {

                    // ✅ Update password safely
                    $user->forceFill([
                        'password' => Hash::make($request->password),
                        'remember_token' => Str::random(60),
                    ])->save();

                    // ✅ Dispatch event
                    event(new PasswordReset($user));
                }
            );

            // ✅ Success
            if ($status === Password::PASSWORD_RESET) {
                return redirect()
                    ->route('auth.login')
                    ->with('status', __($status));
            }

            // ✅ Failure response
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => __($status),
                ]);
        } catch (\Throwable $e) {
            Log::error('Password reset failed', [
                'email' => $request->input('email'),
                'error' => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'email' => 'Something went wrong. Please try again.',
            ]);
        }
    }
}
