@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Reports</h1>
    <div class="bg-gray-900 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead><tr class="text-gray-400 border-b border-gray-800"><th>Anime</th><th>Episode</th><th>User</th><th>Issue</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($reports as $report)
                <tr class="border-b border-gray-800">
                    <td class="p-3">{{ $report->episode->anime->title ?? 'N/A' }}</td>
                    <td class="p-3">{{ 'Ep '.$report->episode->number }}</td>
                    <td class="p-3">{{ $report->user->name }}</td>
                    <td class="p-3">{{ $report->issue_type }}</td>
                    <td class="p-3">{{ $report->status }}</td>
                    <td class="p-3">
                        <form action="{{ route('admin.reports.update', $report) }}" method="POST">
                            @csrf @method('PUT')
                            <select name="status" onchange="this.form.submit()" class="bg-gray-800 text-white rounded px-2 py-1 text-sm border border-gray-700">
                                <option value="pending" @selected($report->status=='pending')>Pending</option>
                                <option value="resolved" @selected($report->status=='resolved')>Resolved</option>
                                <option value="dismissed" @selected($report->status=='dismissed')>Dismissed</option>
                            </select>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $reports->links() }}</div>
</div>
@endsection
