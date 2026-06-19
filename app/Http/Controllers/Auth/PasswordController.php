<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | UPDATE PASSWORD
    |--------------------------------------------------------------------------
    */
    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
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
            | Prevent same password reuse
            |--------------------------------------------------------------------------
            */
            if (Hash::check($validated['password'], $user->password)) {
                throw ValidationException::withMessages([
                    'password' => 'New password must be different from your current password.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Update password
            |--------------------------------------------------------------------------
            */
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Regenerate session (security)
            |--------------------------------------------------------------------------
            */
            $request->session()->regenerate();

            /*
            |--------------------------------------------------------------------------
            | Response
            |--------------------------------------------------------------------------
            */
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'password-updated',
                ]);
            }

            return back()->with('status', 'password-updated');

        } catch (ValidationException $e) {
            throw $e;

        } catch (\Throwable $e) {

            $this->logError('Password update failed', $e, [
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
            ]);

            throw ValidationException::withMessages([
                'password' => 'Failed to update password. Please try again.',
            ]);
        }
    }
}