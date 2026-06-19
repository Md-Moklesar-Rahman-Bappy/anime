<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | SHOW RESET FORM
    |--------------------------------------------------------------------------
    */
    public function create(Request $request): View
    {
        return view('auth.reset-password', [
            'request' => $request,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | RESET PASSWORD
    |--------------------------------------------------------------------------
    */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),

                function (User $user) use ($request) {

                    /*
                    |--------------------------------------------------------------------------
                    | Set new password
                    |--------------------------------------------------------------------------
                    */
                    $user->forceFill([
                        'password' => Hash::make($request->input('password')),
                        'remember_token' => Str::random(60),
                    ])->save();

                    /*
                    |--------------------------------------------------------------------------
                    | Fire password reset event
                    |--------------------------------------------------------------------------
                    */
                    event(new PasswordReset($user));
                }
            );

            /*
            |--------------------------------------------------------------------------
            | SUCCESS
            |--------------------------------------------------------------------------
            */
            if ($status === Password::PASSWORD_RESET) {
                return redirect()
                    ->route('login')
                    ->with('status', __($status));
            }

            /*
            |--------------------------------------------------------------------------
            | FAILURE
            |--------------------------------------------------------------------------
            */
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => __($status),
                ]);

        } catch (\Throwable $e) {

            $this->logError('Password reset failed', $e, [
                'email' => $request->input('email'),
                'ip' => $request->ip(),
            ]);

            throw ValidationException::withMessages([
                'email' => 'Something went wrong. Please try again.',
            ]);
        }
    }
}