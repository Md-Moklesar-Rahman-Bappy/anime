<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        try {
            $search = trim((string) $request->input('search'));

            $query = User::query()
                ->select('id', 'name', 'email', 'role', 'created_at')
                ->latest('created_at');

            /*
            |--------------------------------------------------------------------------
            | SEARCH
            |--------------------------------------------------------------------------
            */
            if ($search !== '') {
                $safe = '%' . addcslashes($search, '%_') . '%';

                $query->where(function ($q) use ($safe) {
                    $q->where('name', 'like', $safe)
                        ->orWhere('email', 'like', $safe)
                        ->orWhere('role', 'like', $safe);
                });
            }

            $users = $query->paginate(20)->withQueryString();

            /*
            |--------------------------------------------------------------------------
            | AJAX RESPONSE
            |--------------------------------------------------------------------------
            */
            if ($request->wantsJson()) {
                return response()->json([
                    'html' => view('admin.users._table', compact('users'))->render(),
                    'pagination' => view('admin.users._pagination', compact('users'))->render(),
                    'total' => $users->total(),
                ]);
            }

            return view('admin.users.index', compact('users'));
        } catch (\Throwable $e) {

            $this->logError('User index load failed', $e);

            return $this->redirectError('Failed to load users.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE ROLE
    |--------------------------------------------------------------------------
    */
    public function updateRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => 'required|in:super_admin,admin,user',
        ]);

        try {
            $authUser = $request->user();

            /*
            |--------------------------------------------------------------------------
            | Permission Check
            |--------------------------------------------------------------------------
            */
            if (!$authUser || $authUser->role !== 'super_admin') {
                return $this->redirectError('Only super admins can change roles.');
            }

            /*
            |--------------------------------------------------------------------------
            | Prevent self downgrade
            |--------------------------------------------------------------------------
            */
            if ($user->id === $authUser->id && $data['role'] !== 'super_admin') {
                return $this->redirectError('You cannot remove your own super admin role.');
            }

            /*
            |--------------------------------------------------------------------------
            | Prevent removing last super admin
            |--------------------------------------------------------------------------
            */
            if (
                $user->role === 'super_admin' &&
                $data['role'] !== 'super_admin' &&
                User::where('role', 'super_admin')->count() <= 1
            ) {
                return $this->redirectError('Cannot remove the last super admin.');
            }

            $user->update([
                'role' => $data['role'],
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User role updated successfully.',
                ]);
            }

            return back()->with('success', 'User role updated successfully.');
        } catch (\Throwable $e) {

            $this->logError('User role update failed', $e, [
                'user_id' => $user->id,
            ]);

            return $this->redirectError('Failed to update role.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE USER
    |--------------------------------------------------------------------------
    */
    public function destroy(Request $request, User $user)
    {
        try {
            $authUser = $request->user();

            /*
            |--------------------------------------------------------------------------
            | Prevent self deletion
            |--------------------------------------------------------------------------
            */
            if ($user->id === $authUser?->id) {
                return $this->redirectError('You cannot delete yourself.');
            }

            /*
            |--------------------------------------------------------------------------
            | Prevent deleting last super admin
            |--------------------------------------------------------------------------
            */
            if (
                $user->role === 'super_admin' &&
                User::where('role', 'super_admin')->count() <= 1
            ) {
                return $this->redirectError('Cannot delete the last super admin.');
            }

            $user->delete();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User deleted successfully.',
                ]);
            }

            return back()->with('success', 'User deleted successfully.');
        } catch (\Throwable $e) {

            $this->logError('User delete failed', $e, [
                'user_id' => $user->id,
            ]);

            return $this->redirectError('Failed to delete user.');
        }
    }
}
