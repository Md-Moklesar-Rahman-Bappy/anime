<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ConfirmablePasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | SHOW VIEW
    |--------------------------------------------------------------------------
    */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIRM PASSWORD
    |--------------------------------------------------------------------------
    */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

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
            | Validate password
            |--------------------------------------------------------------------------
            */
            if (!Auth::guard('web')->validate([
                'email' => $user->email,
                'password' => $request->input('password'),
            ])) {
                throw ValidationException::withMessages([
                    'password' => __('auth.password'),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Set confirmation timestamp
            |--------------------------------------------------------------------------
            */
            $request->session()->put('auth.password_confirmed_at', time());

            /*
            |--------------------------------------------------------------------------
            | Redirect safely
            |--------------------------------------------------------------------------
            */
            return redirect()->intended(
                route('admin.dashboard', absolute: false)
            );

        } catch (ValidationException $e) {
            throw $e;

        } catch (\Throwable $e) {

            $this->logError('Password confirmation failed', $e, [
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
            ]);

            throw ValidationException::withMessages([
                'password' => 'An unexpected error occurred. Please try again.',
            ]);
        }
    }
}