<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        // ✅ Validate input
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:' . User::class,
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            // ✅ Create user safely
            $user = User::create([
                'name' => trim($validated['name']),
                'email' => strtolower($validated['email']),
                'password' => Hash::make($validated['password']),
                'role' => 'user', // ✅ ensure default role
            ]);

            // ✅ Fire event (email verification etc.)
            event(new Registered($user));

            // ✅ Login user
            Auth::login($user);

            // ✅ Optional: regenerate session for security
            $request->session()->regenerate();

            // ✅ Support redirect flow
            return redirect()->intended(
                route('admin.dashboard', absolute: false)
            );

        } catch (\Throwable $e) {
            Log::error('User registration failed', [
                'email' => $validated['email'] ?? null,
                'error' => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'email' => 'Registration failed. Please try again.',
            ]);
        }
    }
}