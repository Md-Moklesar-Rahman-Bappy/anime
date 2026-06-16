@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">

    <h1 class="text-2xl font-semibold text-white mb-6">
        Reports
    </h1>

    <div class="bg-[#111827] border border-gray-800 rounded-2xl overflow-hidden">

        <div class="overflow-x-auto">
            <table class="w-full text-sm">

                <thead>
                    <tr class="bg-[#0f172a] text-gray-400 border-b border-gray-800 text-left">
                        <th class="p-3">Anime</th>
                        <th class="p-3">Episode</th>
                        <th class="p-3">User</th>
                        <th class="p-3">Issue</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($reports as $report)
                    <tr class="border-b border-gray-800 hover:bg-[#1f2937] transition">

                        <!-- Anime -->
                        <td class="p-3 text-white">
                            {{ $report->episode->anime->title ?? '—' }}
                        </td>

                        <!-- Episode -->
                        <td class="p-3 text-gray-300">
                            {{ 'Ep '.$report->episode->number }}
                        </td>

                        <!-- User -->
                        <td class="p-3 text-gray-300">
                            {{ $report->user->name }}
                        </td>

                        <!-- Issue -->
                        <td class="p-3 text-gray-400">
                            {{ $report->issue_type }}
                        </td>

                        <!-- Status -->
                        <td class="p-3">
                            <span class="px-2 py-1 rounded-lg text-xs
                                @switch($report->status)
                                    @case('pending') bg-yellow-500/10 text-yellow-400 @break
                                    @case('resolved') bg-green-500/10 text-green-400 @break
                                    @case('dismissed') bg-gray-700 text-gray-400 @break
                                @endswitch
                            ">
                                {{ ucfirst($report->status) }}
                            </span>
                        </td>

                        <!-- Action -->
                        <td class="p-3">

                             }}" method="POST">
                                @csrf
                                @method('PUT')

                                <select name="status"
                                        onchange="this.form.submit()"
                                        class="bg-[#1f2937] text-white text-sm px-2 py-1 rounded border border-gray-700 focus:ring-indigo-500">

                                    <option value="pending" @selected($report->status=='pending')>
                                        Pending
                                    </option>

                                    <option value="resolved" @selected($report->status=='resolved')>
                                        Resolved
                                    </option>

                                    <option value="dismissed" @selected($report->status=='dismissed')>
                                        Dismissed
                                    </option>

                                </select>
                            </form>

                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-10 text-center text-gray-500">
                            <p class="text-lg text-gray-300">No reports found</p>
                            <p class="text-sm mt-1">User reports will appear here</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-800">
            {{ $reports->links() }}
        </div>

    </div>

</div>
@endsection