<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        // ✅ Validate with named error bag (good UX)
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        try {
            $user = $request->user();

            if (!$user) {
                return redirect()
                    ->route('auth.login')
                    ->with('error', 'Authentication required.');
            }

            // ✅ Prevent same password reuse (optional security)
            if (Hash::check($validated['password'], $user->password)) {
                return back()->withErrors([
                    'password' => 'New password must be different from your current password.',
                ]);
            }

            // ✅ Update password
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);

            // ✅ Optional: regenerate session (extra security)
            $request->session()->regenerate();

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'password-updated',
                ]);
            }

            return back()->with('status', 'password-updated');

        } catch (\Throwable $e) {
            Log::error('Password update failed', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'password' => 'Failed to update password. Please try again.',
            ]);
        }
    }
}
