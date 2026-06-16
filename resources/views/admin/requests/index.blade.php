@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">

    <h1 class="text-2xl font-semibold text-white mb-6">
        Requests
    </h1>

    <div class="bg-[#111827] border border-gray-800 rounded-2xl overflow-hidden">

        <div class="overflow-x-auto">
            <table class="w-full text-sm">

                <thead>
                    <tr class="bg-[#0f172a] text-gray-400 border-b border-gray-800 text-left">
                        <th class="p-3">Anime</th>
                        <th class="p-3">User</th>
                        <th class="p-3">Description</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($requests as $req)

                    <tr class="border-b border-gray-800 hover:bg-[#1f2937] transition">

                        <!-- Anime -->
                        <td class="p-3 text-white">
                            {{ $req->anime_title }}
                        </td>

                        <!-- User -->
                        <td class="p-3 text-gray-300">
                            {{ $req->user->name }}
                        </td>

                        <!-- Description -->
                        <td class="p-3 text-gray-400">
                            {{ Str::limit($req->description, 60) }}
                        </td>

                        <!-- Status -->
                        <td class="p-3">
                            <span class="px-2 py-1 rounded-lg text-xs
                                @switch($req->status)
                                    @case('pending') bg-yellow-500/10 text-yellow-400 @break
                                    @case('fulfilled') bg-green-500/10 text-green-400 @break
                                    @case('rejected') bg-red-500/10 text-red-400 @break
                                    @default bg-gray-700 text-gray-400
                                @endswitch
                            ">
                                {{ ucfirst($req->status) }}
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="p-3">

                            <form action="{{ route('admin.requests.update', $req) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <select name="status"
                                        onchange="this.form.submit()"
                                        class="bg-[#1f2937] text-white rounded px-2 py-1 text-sm border border-gray-700 focus:ring-indigo-500">

                                    <option value="pending" @selected($req->status=='pending')>
                                        Pending
                                    </option>

                                    <option value="fulfilled" @selected($req->status=='fulfilled')>
                                        Fulfilled
                                    </option>

                                    <option value="rejected" @selected($req->status=='rejected')>
                                        Rejected
                                    </option>

                                </select>
                            </form>

                        </td>

                    </tr>

                    @empty
                    <tr>
                        <td colspan="5" class="p-10 text-center text-gray-500">
                            <p class="text-lg text-gray-300">No requests found</p>
                            <p class="text-sm mt-1">User requests will appear here</p>
                        </td>
                    </tr>
                    @endforelse

                </tbody>

            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-800">
            {{ $requests->links() }}
        </div>

    </div>

</div>
@endsection