@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Dashboard</h1>
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        <div class="bg-gray-900 rounded-lg p-4">
            <p class="text-gray-400 text-sm">Total Anime</p>
            <p class="text-2xl font-bold">{{ $totalAnime }}</p>
        </div>
        <div class="bg-gray-900 rounded-lg p-4">
            <p class="text-gray-400 text-sm">Total Episodes</p>
            <p class="text-2xl font-bold">{{ $totalEpisodes }}</p>
        </div>
        <div class="bg-gray-900 rounded-lg p-4">
            <p class="text-gray-400 text-sm">Users</p>
            <p class="text-2xl font-bold">{{ $totalUsers }}</p>
        </div>
        <div class="bg-gray-900 rounded-lg p-4">
            <p class="text-gray-400 text-sm">Pending Reports</p>
            <p class="text-2xl font-bold">{{ $totalReports }}</p>
        </div>
        <div class="bg-gray-900 rounded-lg p-4">
            <p class="text-gray-400 text-sm">Pending Requests</p>
            <p class="text-2xl font-bold">{{ $totalRequests }}</p>
        </div>
    </div>
    <div class="bg-gray-900 rounded-lg p-4">
        <h2 class="font-bold mb-4">Recent Anime</h2>
        <table class="w-full text-sm">
            <thead><tr class="text-gray-400 border-b border-gray-800"><th class="text-left py-2">Title</th><th class="text-left py-2">Type</th><th class="text-left py-2">Status</th><th class="text-left py-2">Created</th></tr></thead>
            <tbody>
                @foreach($recentAnime as $anime)
                <tr class="border-b border-gray-800"><td class="py-2">{{ $anime->title }}</td><td class="py-2">{{ $anime->type }}</td><td class="py-2">{{ $anime->status }}</td><td class="py-2">{{ $anime->created_at->diffForHumans() }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
