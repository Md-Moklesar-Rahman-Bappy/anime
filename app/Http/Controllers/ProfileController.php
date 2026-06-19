<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | EDIT PROFILE
    |--------------------------------------------------------------------------
    */

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE PROFILE
    |--------------------------------------------------------------------------
    */

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->redirectError('Authentication required.');
            }

            $user->fill($request->validated());

            // ✅ Reset email verification if email changed
            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();

            return redirect()
                ->route('profile.edit')
                ->with('status', 'profile-updated');

        } catch (\Throwable $e) {

            $this->logError('Profile update failed', $e, [
                'user_id' => $request->user()?->id,
            ]);

            return $this->redirectError('Failed to update profile.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE ACCOUNT
    |--------------------------------------------------------------------------
    */

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        try {
            $user = $request->user();

            if (!$user) {
                return $this->redirectError('Authentication required.');
            }

            /*
            |--------------------------------------------------------------------------
            | Prevent deleting last super admin
            |--------------------------------------------------------------------------
            */
            if (
                $user->role === User::ROLE_SUPER_ADMIN &&
                User::where('role', User::ROLE_SUPER_ADMIN)->count() <= 1
            ) {
                return $this->redirectError(
                    'Cannot delete the last super admin.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Logout + Delete
            |--------------------------------------------------------------------------
            */
            Auth::logout();

            $user->delete();

            /*
            |--------------------------------------------------------------------------
            | Session Security
            |--------------------------------------------------------------------------
            */
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/')
                ->with('success', 'Account deleted.');

        } catch (\Throwable $e) {

            $this->logError('Account deletion failed', $e, [
                'user_id' => $request->user()?->id,
            ]);

            return $this->redirectError('Failed to delete account.');
        }
    }
}