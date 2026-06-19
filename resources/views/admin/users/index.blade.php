@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-4">

    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 text-white mb-0">Users</h1>
        <span class="badge bg-secondary fs-6">{{ $users->total() }} total</span>
    </div>

    <!-- Search -->
    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control bg-dark text-white border-secondary"
                   placeholder="Search by name, email or role..."
                   value="{{ request('search') }}">
            <button class="btn btn-outline-secondary" type="submit">Search</button>
            @if(request('search'))
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Clear</a>
            @endif
        </div>
    </form>

    <!-- Users Table -->
    <div class="card bg-dark border-secondary">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td class="text-secondary">{{ $user->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=32"
                                         class="rounded-circle" width="24" height="24">
                                    <span>{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="text-secondary">{{ $user->email }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.users.role', $user) }}"
                                      class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <select name="role" onchange="this.form.submit()"
                                            class="form-select form-select-sm bg-dark text-white border-secondary"
                                            {{ auth()->user()->role !== 'super_admin' ? 'disabled' : '' }}>
                                        <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                        <option value="super_admin" {{ $user->role === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                    </select>
                                </form>
                            </td>
                            <td class="text-secondary">{{ $user->created_at->format('Y-m-d') }}</td>
                            <td>
                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Delete user {{ $user->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                @else
                                    <span class="text-secondary">You</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">No users found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4 d-flex justify-content-center">
        {{ $users->links() }}
    </div>

</div>
@endsection
