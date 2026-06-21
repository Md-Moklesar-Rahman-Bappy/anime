<div class="table-card">

    <div class="overflow-x-auto">

        <table class="w-full text-sm">

            <thead class="table-head">
                <tr>
                    <th class="p-4 text-left">Anime</th>
                    <th class="p-4 text-left">Episode</th>
                    <th class="p-4 text-left">User</th>
                    <th class="p-4 text-left">Issue</th>
                    <th class="p-4 text-left">Status</th>
                    <th class="p-4 text-left">Actions</th>
                </tr>
            </thead>

            <tbody>

            @forelse($reports as $report)

            <tr
                class="table-row"
                data-report-id="{{ $report->id }}"
                data-status="{{ $report->status }}"
            >

                <td class="p-4 text-white">
                    {{ $report->episode->anime->title ?? '—' }}
                </td>

                <td class="p-4 text-gray-300">
                    {{ $report->episode ? 'Ep ' . $report->episode->number : '—' }}
                </td>

                <td class="p-4 text-gray-300">
                    {{ $report->user->name ?? '—' }}
                </td>

                <td class="p-4 text-gray-400">
                    <div>{{ $report->issue_type }}</div>

                    @if($report->description)
                        <div class="text-xs text-gray-500 mt-1 max-w-[260px] truncate">
                            {{ $report->description }}
                        </div>
                    @endif
                </td>

                <td class="p-4">

                    @php
                        $statusClass = match($report->status) {
                            'pending' => 'badge-warning',
                            'resolved' => 'badge-success',
                            'dismissed' => 'bg-gray-700 text-gray-300 px-2 py-1 text-xs rounded',
                            default => 'bg-gray-700 text-gray-300 px-2 py-1 text-xs rounded',
                        };
                    @endphp

                    <span class="{{ $statusClass }}">
                        {{ ucfirst($report->status) }}
                    </span>

                </td>

                <td class="p-4">

                    <select
                        class="form-input py-1 text-sm"
                        @change="updateStatus({{ $report->id }}, $event.target.value)"
                    >
                        <option value="pending" @selected($report->status === 'pending')>Pending</option>
                        <option value="resolved" @selected($report->status === 'resolved')>Resolved</option>
                        <option value="dismissed" @selected($report->status === 'dismissed')>Dismissed</option>
                    </select>

                </td>

            </tr>

            @empty

            <tr>
                <td colspan="6" class="p-10 text-center text-gray-500">

                    <p class="text-white font-medium mb-1">
                        No reports found
                    </p>

                    <p class="text-sm">
                        User reports will appear here
                    </p>

                </td>
            </tr>

            @endforelse

            </tbody>

        </table>

    </div>

    <div class="p-4 border-t border-gray-700">
        {{ $reports->links() }}
    </div>

</div>