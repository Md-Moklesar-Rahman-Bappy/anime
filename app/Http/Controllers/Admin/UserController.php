<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = User::query()->latest('created_at');

            // ✅ Optional search filter
            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $safeSearch = '%' . addcslashes($search, '%_') . '%';

                    $q->where('name', 'like', $safeSearch)
                        ->orWhere('email', 'like', $safeSearch)
                        ->orWhere('role', 'like', $safeSearch);
                });
            }

            $users = $query->paginate(20);

            // ✅ AJAX support
            if ($request->wantsJson()) {
                return response()->json([
                    'html' => view('admin.users._table', compact('users'))->render(),
                    'pagination' => view('admin.users._pagination', compact('users'))->render(),
                    'total' => $users->total(),
                ]);
            }

            return view('admin.users.index', compact('users'));
        } catch (\Throwable $e) {
            Log::error('User index load failed', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to load users.');
        }
    }

    public function updateRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => 'required|in:super_admin,admin,user',
        ]);

        try {
            $authUser = auth()->user();

            // ✅ Only super admin can change roles
            if (!$authUser || $authUser->role !== 'super_admin') {
                return back()->with('error', 'Only super admins can change roles.');
            }

            // ✅ Prevent self role downgrade
            if ($user->id === $authUser->id && $data['role'] !== 'super_admin') {
                return back()->with('error', 'You cannot remove your own super admin role.');
            }

            // ✅ Prevent removing last super admin
            if (
                $user->role === 'super_admin' &&
                $data['role'] !== 'super_admin' &&
                User::where('role', 'super_admin')->count() <= 1
            ) {
                return back()->with('error', 'Cannot remove the last super admin.');
            }

            $user->update([
                'role' => $data['role'],
            ]);

            if ($request->wantsJson()) {
                return response()->json(['success' => true]);
            }

            return back()->with('success', 'User role updated.');
        } catch (\Throwable $e) {
            Log::error('User role update failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to update role.');
        }
    }

    public function destroy(Request $request, User $user)
    {
        try {
            $authUser = auth()->user();

            // ✅ Cannot delete yourself
            if ($user->id === $authUser?->id) {
                return back()->with('error', 'You cannot delete yourself.');
            }

            // ✅ Prevent deleting last super admin
            if (
                $user->role === 'super_admin' &&
                User::where('role', 'super_admin')->count() <= 1
            ) {
                return back()->with('error', 'Cannot delete the last super admin.');
            }

            $user->delete();

            if ($request->wantsJson()) {
                return response()->json(['success' => true]);
            }

            return back()->with('success', 'User deleted.');
        } catch (\Throwable $e) {
            Log::error('User delete failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to delete user.');
        }
    }
}
