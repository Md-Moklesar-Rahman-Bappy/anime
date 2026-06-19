<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | SHOW FORGOT PASSWORD FORM
    |--------------------------------------------------------------------------
    */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /*
    |--------------------------------------------------------------------------
    | SEND RESET LINK
    |--------------------------------------------------------------------------
    */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            /*
            |--------------------------------------------------------------------------
            | Rate limiting (session-based)
            |--------------------------------------------------------------------------
            */
            $lastSent = (int) $request->session()->get('password_reset_last_sent', 0);

            if ((time() - $lastSent) < 30) {
                return back()->with('error', 'Please wait before requesting another reset link.');
            }

            /*
            |--------------------------------------------------------------------------
            | Send reset link
            |--------------------------------------------------------------------------
            */
            $status = Password::sendResetLink(
                $request->only('email')
            );

            /*
            |--------------------------------------------------------------------------
            | Success
            |--------------------------------------------------------------------------
            */
            if ($status === Password::RESET_LINK_SENT) {
                $request->session()->put('password_reset_last_sent', time());

                return back()->with('status', __($status));
            }

            /*
            |--------------------------------------------------------------------------
            | Failure
            |--------------------------------------------------------------------------
            */
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => __($status),
                ]);

        } catch (ValidationException $e) {
            throw $e;

        } catch (\Throwable $e) {

            $this->logError('Password reset link failed', $e, [
                'email' => $request->input('email'),
                'ip' => $request->ip(),
            ]);

            throw ValidationException::withMessages([
                'email' => 'Something went wrong. Please try again.',
            ]);
        }
    }
}
