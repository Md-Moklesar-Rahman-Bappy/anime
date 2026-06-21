<div class="table-card">

    <div class="overflow-x-auto">

        <table class="w-full text-sm">

            <thead class="table-head">
                <tr>
                    <th class="p-4 text-left">Anime</th>
                    <th class="p-4 text-left">User</th>
                    <th class="p-4 text-left">Description</th>
                    <th class="p-4 text-left">Status</th>
                    <th class="p-4 text-left">Actions</th>
                </tr>
            </thead>

            <tbody>

            @forelse($requests as $req)

            <tr
                class="table-row"
                data-request-id="{{ $req->id }}"
                data-status="{{ $req->status }}"
            >

                {{-- ANIME --}}
                <td class="p-4 text-white">
                    {{ $req->anime_title }}
                </td>

                {{-- USER --}}
                <td class="p-4 text-gray-300">
                    {{ $req->user->name ?? '—' }}
                </td>

                {{-- DESCRIPTION --}}
                <td class="p-4 text-gray-400 max-w-[320px]">
                    <div class="truncate">
                        {{ \Illuminate\Support\Str::limit($req->description, 80) }}
                    </div>
                </td>

                {{-- STATUS --}}
                <td class="p-4">

                    @php
                        $statusClass = match($req->status) {
                            'pending' => 'badge-warning',
                            'fulfilled' => 'badge-success',
                            'rejected' => 'badge-danger',
                            default => 'bg-gray-700 text-gray-300 px-2 py-1 text-xs rounded',
                        };
                    @endphp

                    <span class="{{ $statusClass }}">
                        {{ ucfirst($req->status) }}
                    </span>

                </td>

                {{-- ACTIONS --}}
                <td class="p-4">

                    <select
                        class="form-input py-1 text-sm"
                        @change="updateStatus({{ $req->id }}, $event.target.value)"
                    >
                        <option value="pending" @selected($req->status === 'pending')>
                            Pending
                        </option>

                        <option value="fulfilled" @selected($req->status === 'fulfilled')>
                            Fulfilled
                        </option>

                        <option value="rejected" @selected($req->status === 'rejected')>
                            Rejected
                        </option>
                    </select>

                </td>

            </tr>

            @empty

            <tr>
                <td colspan="5" class="p-10 text-center text-gray-500">

                    <p class="text-white font-medium mb-1">
                        No requests found
                    </p>

                    <p class="text-sm">
                        User requests will appear here
                    </p>

                </td>
            </tr>

            @endforelse

            </tbody>

        </table>

    </div>

    <div class="p-4 border-t border-gray-700">
        {{ $requests->links() }}
    </div>

</div>