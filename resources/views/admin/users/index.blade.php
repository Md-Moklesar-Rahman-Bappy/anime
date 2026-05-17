@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Users</h1>
    <div class="bg-gray-900 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead><tr class="text-gray-400 border-b border-gray-800"><th class="text-left p-3">Name</th><th class="text-left p-3">Email</th><th class="text-left p-3">Role</th><th class="text-left p-3">Actions</th></tr></thead>
            <tbody>
                @foreach($users as $user)
                <tr class="border-b border-gray-800">
                    <td class="p-3">{{ $user->name }}</td>
                    <td class="p-3">{{ $user->email }}</td>
                    <td class="p-3">{{ $user->role }}</td>
                    <td class="p-3 flex space-x-2">
                        <form action="{{ route('admin.users.role', $user) }}" method="POST">
                            @csrf @method('PUT')
                            <select name="role" onchange="this.form.submit()" class="bg-gray-800 text-white rounded px-2 py-1 text-sm border border-gray-700">
                                <option value="user" @selected($user->role=='user')>User</option>
                                <option value="admin" @selected($user->role=='admin')>Admin</option>
                                <option value="super_admin" @selected($user->role=='super_admin')>Super Admin</option>
                            </select>
                        </form>
                        @if($user->id !== auth()->id())
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Delete user?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-400 text-sm">Delete</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
</div>
@endsection
