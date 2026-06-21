@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-white">Users</h1>

        <span class="px-3 py-1 text-sm bg-gray-700 text-gray-200 rounded-full">
            {{ $users->total() }} total
        </span>
    </div>

    {{-- SEARCH --}}
    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-6">
        <div class="flex gap-2">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search by name, email or role..."
                class="form-input flex-1"
            >

            <button type="submit" class="btn-primary">
                Search
            </button>

            @if(request('search'))
                <a href="{{ route('admin.users.index') }}" class="btn-cancel">
                    Clear
                </a>
            @endif
        </div>
    </form>

    {{-- TABLE --}}
    <div class="table-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-[#0a0a0f] text-gray-400 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">ID</th>
                        <th class="px-4 py-3 text-left">Name</th>
                        <th class="px-4 py-3 text-left">Email</th>
                        <th class="px-4 py-3 text-left">Role</th>
                        <th class="px-4 py-3 text-left">Registered</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-800">
                    @forelse($users as $user)
                        <tr class="hover:bg-[#15151c] transition">

                            {{-- ID --}}
                            <td class="px-4 py-3 text-gray-500">
                                {{ $user->id }}
                            </td>

                            {{-- NAME --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img
                                        src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=32&background=4f46e5&color=fff"
                                        class="w-8 h-8 rounded-full"
                                        alt="{{ $user->name }}"
                                    >
                                    <span class="text-white">{{ $user->name }}</span>
                                </div>
                            </td>

                            {{-- EMAIL --}}
                            <td class="px-4 py-3 text-gray-400">
                                {{ $user->email }}
                            </td>

                            {{-- ROLE --}}
                            <td class="px-4 py-3">
                                <form method="POST"
                                      action="{{ route('admin.users.role', $user) }}"
                                      class="inline-block">
                                    @csrf
                                    @method('PUT')

                                    <select
                                        name="role"
                                        onchange="this.form.submit()"
                                        class="form-input text-sm py-1 px-2"
                                        {{ auth()->user()->role !== 'super_admin' ? 'disabled' : '' }}
                                    >
                                        <option value="user"
                                            {{ $user->role === 'user' ? 'selected' : '' }}>
                                            User
                                        </option>
                                        <option value="admin"
                                            {{ $user->role === 'admin' ? 'selected' : '' }}>
                                            Admin
                                        </option>
                                        <option value="super_admin"
                                            {{ $user->role === 'super_admin' ? 'selected' : '' }}>
                                            Super Admin
                                        </option>
                                    </select>
                                </form>
                            </td>

                            {{-- REGISTERED --}}
                            <td class="px-4 py-3 text-gray-400">
                                {{ $user->created_at->format('Y-m-d') }}
                            </td>

                            {{-- ACTIONS --}}
                            <td class="px-4 py-3 text-right">
                                @if($user->id !== auth()->id())
                                    <form method="POST"
                                          action="{{ route('admin.users.destroy', $user) }}"
                                          class="inline-block"
                                          onsubmit="return confirm('Delete user {{ $user->name }}?')">
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="px-3 py-1 text-xs font-medium text-red-400 border border-red-500/40 rounded-md hover:bg-red-500/10 transition"
                                        >
                                            Delete
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-500 italic">You</span>
                                @endif
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-gray-500 py-8">
                                No users found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- PAGINATION --}}
    <div class="mt-6">
        {{ $users->onEachSide(1)->links() }}
    </div>

</div>
@endsection