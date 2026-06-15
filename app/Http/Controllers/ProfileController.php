<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return Redirect::route('login')
                    ->with('error', 'Authentication required.');
            }

            $user->fill($request->validated());

            // ✅ If email changed → force re-verification
            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();

            return Redirect::route('profile.edit')
                ->with('status', 'profile-updated');

        } catch (\Throwable $e) {
            Log::error('Profile update failed', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to update profile.');
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        try {
            $user = $request->user();

            if (!$user) {
                return Redirect::route('login')
                    ->with('error', 'Authentication required.');
            }

            // ✅ Prevent deleting last super admin (important safety)
            if (
                $user->role === 'super_admin' &&
                \App\Models\User::where('role', 'super_admin')->count() <= 1
            ) {
                return back()->with('error', 'Cannot delete the last super admin.');
            }

            Auth::logout();

            $user->delete();

            // ✅ Session security
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return Redirect::to('/')
                ->with('success', 'Account deleted.');

        } catch (\Throwable $e) {
            Log::error('Account deletion failed', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to delete account.');
        }
    }
}