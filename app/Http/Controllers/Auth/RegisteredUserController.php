<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | SHOW REGISTER FORM
    |--------------------------------------------------------------------------
    */
    public function create(): View
    {
        return view('auth.register');
    }

    /*
    |--------------------------------------------------------------------------
    | REGISTER USER
    |--------------------------------------------------------------------------
    */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'lowercase',
                'unique:' . User::class,
            ],

            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults(),
            ],
        ]);

        try {
            /*
            |--------------------------------------------------------------------------
            | Create user
            |--------------------------------------------------------------------------
            */
            $user = User::create([
                'name' => trim($validated['name']),
                'email' => strtolower($validated['email']),
                'password' => Hash::make($validated['password']),
                'role' => 'user', // ✅ enforce default role
            ]);

            /*
            |--------------------------------------------------------------------------
            | Fire registered event (email verification)
            |--------------------------------------------------------------------------
            */
            event(new Registered($user));

            /*
            |--------------------------------------------------------------------------
            | Login + secure session
            |--------------------------------------------------------------------------
            */
            Auth::login($user);
            $request->session()->regenerate();

            /*
            |--------------------------------------------------------------------------
            | Redirect
            |--------------------------------------------------------------------------
            */
            return redirect()->intended(
                route('admin.dashboard', absolute: false)
            );

        } catch (ValidationException $e) {
            throw $e;

        } catch (\Throwable $e) {

            $this->logError('User registration failed', $e, [
                'email' => $validated['email'] ?? null,
                'ip' => $request->ip(),
            ]);

            throw ValidationException::withMessages([
                'email' => 'Registration failed. Please try again.',
            ]);
        }
    }
}