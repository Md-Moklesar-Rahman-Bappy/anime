<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:super_admin,admin,user']);
        if (auth()->user()->role !== 'super_admin') {
            return back()->with('error', 'Only super admins can change roles.');
        }
        $user->update(['role' => $request->role]);

        return back()->with('success', 'User role updated.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        if ($user->role === 'super_admin' && User::where('role', 'super_admin')->count() <= 1) {
            return back()->with('error', 'Cannot delete the last super admin.');
        }

        $user->delete();

        return back()->with('success', 'User deleted.');
    }
}
