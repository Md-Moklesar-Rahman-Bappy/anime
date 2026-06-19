<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | SHOW LOGIN
    |--------------------------------------------------------------------------
    */
    public function create(): View
    {
        return view('auth.login');
    }

    /*
    |--------------------------------------------------------------------------
    | LOGIN
    |--------------------------------------------------------------------------
    */
    public function store(LoginRequest $request): RedirectResponse
    {
        try {
            // ✅ Attempt login (handled in LoginRequest)
            $request->authenticate();

            // ✅ Prevent session fixation
            $request->session()->regenerate();

            return redirect()->intended(
                route('admin.dashboard', absolute: false)
            );
        } catch (\Throwable $e) {

            $this->logError('Login failed', $e, [
                'email' => $request->input('email'),
                'ip' => $request->ip(),
            ]);

            return back()->withErrors([
                'email' => 'Login failed. Please try again.',
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */
    public function destroy(Request $request): RedirectResponse
    {
        try {
            Auth::guard('web')->logout();

            // ✅ Invalidate session
            $request->session()->invalidate();

            // ✅ Prevent CSRF reuse
            $request->session()->regenerateToken();

            return redirect('/');
        } catch (\Throwable $e) {

            $this->logError('Logout failed', $e, [
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
            ]);

            return redirect('/')->with('error', 'Logout failed.');
        }
    }
}
