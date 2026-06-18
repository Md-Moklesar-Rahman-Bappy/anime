@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto"
     x-data="dashboard({
        userGrowthLabels: @json($userGrowth->pluck('label')),
        userGrowthData: @json($userGrowth->pluck('count')),
        typeLabels: @json($animeByType->keys()),
        typeData: @json($animeByType->values()),
        statusLabels: @json($animeByStatus->keys()),
        statusData: @json($animeByStatus->values()),
     })">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-white">Dashboard</h1>
            <p class="text-gray-400 text-sm">{{ now()->format('l, F j, Y') }}</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('admin.anime.create') }}"
               class="bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded-lg text-sm">
                + Anime
            </a>
            <a href="{{ route('admin.manga.create') }}"
               class="bg-emerald-600 hover:bg-emerald-500 px-4 py-2 rounded-lg text-sm">
                + Manga
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">

        @foreach([
            ['Anime',$totalAnime,'🎬','text-indigo-400'],
            ['Episodes',$totalEpisodes,'▶','text-blue-400'],
            ['Manga',$totalManga,'📖','text-pink-400'],
            ['Chapters',$totalChapters,'📄','text-green-400'],
            ['Users',$totalUsers,'👥','text-yellow-400'],
        ] as [$label,$value,$icon,$color])

        <div class="card">
            <div class="flex justify-between mb-2">
                <p class="text-xs text-gray-400 uppercase">{{ $label }}</p>
                <span class="{{ $color }}">{{ $icon }}</span>
            </div>
            <p class="text-xl font-bold">{{ number_format($value) }}</p>
        </div>

        @endforeach

    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        <div class="card">
            <h2 class="font-semibold mb-2">User Growth</h2>
            <div class="h-64"><canvas id="userGrowthChart"></canvas></div>
        </div>

        <div class="card">
            <h2 class="font-semibold mb-2">Anime Types</h2>
            <div class="h-64"><canvas id="animeByTypeChart"></canvas></div>
        </div>

        <div class="card">
            <h2 class="font-semibold mb-2">Anime Status</h2>
            <div class="h-64"><canvas id="animeByStatusChart"></canvas></div>
        </div>

    </div>

    <!-- Recent Episodes -->
    <div class="card mb-8">

        <h2 class="font-semibold mb-4">Recent Episodes</h2>

        @forelse($recentEpisodes as $episode)
        <div class="flex gap-3 py-2 border-b border-gray-800 last:border-none">

            <div class="w-12 h-7 bg-gray-800 rounded overflow-hidden">
                <img src="{{ $episode->thumbnail_url }}" class="w-full h-full object-cover">
            </div>

            <div class="flex-1">
                <p class="text-sm">{{ $episode->anime->title ?? '-' }}</p>
                <p class="text-xs text-gray-400">Ep {{ $episode->number }}</p>
            </div>

        </div>
        @empty
            <p class="text-gray-500 text-sm">No episodes yet</p>
        @endforelse

    </div>

</div>

<!-- Styles -->
<style>
.card {
    @apply bg-[#111827] border border-gray-800 rounded-2xl p-4;
}
</style>

@endsection


































{{-- @extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto" x-data="dashboard({
    userGrowthLabels: @json($userGrowth->pluck('label')),
    userGrowthData: @json($userGrowth->pluck('count')),
    typeLabels: @json($animeByType->keys()),
    typeData: @json($animeByType->values()),
    statusLabels: @json($animeByStatus->keys()),
    statusData: @json($animeByStatus->values()),
})">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Dashboard</h1>
            <p class="text-gray-400 text-sm">{{ now()->format('l, F j, Y') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.anime.create') }}" class="bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">+ Add Anime</a>
            <a href="{{ route('admin.manga.create') }}" class="bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">+ Add Manga</a>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
        <div class="bg-gray-900 rounded-lg p-4 border border-gray-800">
            <div class="flex items-center justify-between mb-2">
                <p class="text-gray-400 text-xs uppercase tracking-wide">Anime</p>
                <span class="text-purple-500 text-lg">&#127916;</span>
            </div>
            <p class="text-2xl font-bold">{{ number_format($totalAnime) }}</p>
        </div>
        <div class="bg-gray-900 rounded-lg p-4 border border-gray-800">
            <div class="flex items-center justify-between mb-2">
                <p class="text-gray-400 text-xs uppercase tracking-wide">Episodes</p>
                <span class="text-blue-500 text-lg">&#9654;</span>
            </div>
            <p class="text-2xl font-bold">{{ number_format($totalEpisodes) }}</p>
        </div>
        <div class="bg-gray-900 rounded-lg p-4 border border-gray-800">
            <div class="flex items-center justify-between mb-2">
                <p class="text-gray-400 text-xs uppercase tracking-wide">Manga</p>
                <span class="text-pink-500 text-lg">&#128214;</span>
            </div>
            <p class="text-2xl font-bold">{{ number_format($totalManga) }}</p>
        </div>
        <div class="bg-gray-900 rounded-lg p-4 border border-gray-800">
            <div class="flex items-center justify-between mb-2">
                <p class="text-gray-400 text-xs uppercase tracking-wide">Chapters</p>
                <span class="text-green-500 text-lg">&#128196;</span>
            </div>
            <p class="text-2xl font-bold">{{ number_format($totalChapters) }}</p>
        </div>
        <div class="bg-gray-900 rounded-lg p-4 border border-gray-800">
            <div class="flex items-center justify-between mb-2">
                <p class="text-gray-400 text-xs uppercase tracking-wide">Users</p>
                <span class="text-yellow-500 text-lg">&#128101;</span>
            </div>
            <p class="text-2xl font-bold">{{ number_format($totalUsers) }}</p>
        </div>
        <a href="{{ route('admin.comments.index') }}" class="bg-gray-900 rounded-lg p-4 border border-gray-800 hover:bg-gray-800 transition-colors block">
            <div class="flex items-center justify-between mb-2">
                <p class="text-gray-400 text-xs uppercase tracking-wide">Comments</p>
                <span class="text-cyan-500 text-lg">&#128172;</span>
            </div>
            <p class="text-2xl font-bold">{{ number_format($totalComments) }}</p>
        </a>
        <div class="bg-gray-900 rounded-lg p-4 border border-gray-800">
            <div class="flex items-center justify-between mb-2">
                <p class="text-gray-400 text-xs uppercase tracking-wide">Total Views</p>
                <span class="text-orange-500 text-lg">&#128065;</span>
            </div>
            <p class="text-2xl font-bold">{{ number_format($totalViews) }}</p>
        </div>
        <div class="bg-gray-900 rounded-lg p-4 border border-gray-800">
            <div class="flex items-center justify-between mb-2">
                <p class="text-gray-400 text-xs uppercase tracking-wide">Pending Reports</p>
                <span class="text-red-500 text-lg">&#9888;</span>
            </div>
            <p class="text-2xl font-bold">{{ $totalReports }}</p>
        </div>
        <div class="bg-gray-900 rounded-lg p-4 border border-gray-800">
            <div class="flex items-center justify-between mb-2">
                <p class="text-gray-400 text-xs uppercase tracking-wide">Requests</p>
                <span class="text-indigo-500 text-lg">&#128221;</span>
            </div>
            <p class="text-2xl font-bold">{{ $totalRequests }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-gray-900 rounded-lg p-5 border border-gray-800 lg:col-span-1">
            <h2 class="font-bold mb-1">User Growth</h2>
            <p class="text-gray-400 text-xs mb-4">Monthly registrations (12 months)</p>
            <div class="h-64">
                <canvas id="userGrowthChart"></canvas>
            </div>
        </div>
        <div class="bg-gray-900 rounded-lg p-5 border border-gray-800">
            <h2 class="font-bold mb-1">Anime by Type</h2>
            <p class="text-gray-400 text-xs mb-4">Distribution across types</p>
            <div class="h-64 flex items-center justify-center">
                <canvas id="animeByTypeChart" class="max-h-64"></canvas>
            </div>
        </div>
        <div class="bg-gray-900 rounded-lg p-5 border border-gray-800">
            <h2 class="font-bold mb-1">Anime by Status</h2>
            <p class="text-gray-400 text-xs mb-4">Current status distribution</p>
            <div class="h-64 flex items-center justify-center">
                <canvas id="animeByStatusChart" class="max-h-64"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-gray-900 rounded-lg p-5 border border-gray-800">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold">Recent Episodes</h2>
                <a href="{{ route('admin.anime.index') }}" class="text-purple-500 text-sm hover:text-purple-400">View All</a>
            </div>
            @if($recentEpisodes->count())
            <div class="space-y-3">
                @foreach($recentEpisodes as $episode)
                <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800 transition-colors">
                    <div class="w-12 h-7 rounded overflow-hidden bg-gray-800 flex-shrink-0">
                        <img src="{{ $episode->thumbnail_url }}" alt="" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ $episode->anime->title ?? 'Unknown' }}</p>
                        <p class="text-xs text-gray-400">Episode {{ $episode->number }}{{ $episode->title ? ' - '.$episode->title : '' }}</p>
                    </div>
                    <span class="text-xs text-gray-500 flex-shrink-0">{{ $episode->created_at->diffForHumans() }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 text-sm">No episodes yet</p>
            @endif
        </div>

        <div class="bg-gray-900 rounded-lg p-5 border border-gray-800">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold">Popular Anime</h2>
                <span class="text-xs text-gray-400">By views</span>
            </div>
            @if($popularAnime->count())
            <div class="space-y-3">
                @foreach($popularAnime as $i => $anime)
                <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800 transition-colors">
                    <span class="text-sm font-bold text-gray-500 w-5 flex-shrink-0">{{ $i + 1 }}</span>
                    <div class="w-10 h-14 rounded overflow-hidden bg-gray-800 flex-shrink-0">
                        <img src="{{ $anime->thumbnail_url }}" alt="" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ $anime->title }}</p>
                        <p class="text-xs text-gray-400">{{ $anime->type ?? 'N/A' }} &middot; {{ $anime->status ?? 'N/A' }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-semibold">{{ number_format($anime->views) }}</p>
                        <p class="text-xs text-gray-500">views</p>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 text-sm">No anime data yet</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-gray-900 rounded-lg p-5 border border-gray-800">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold">Recent Anime</h2>
                <a href="{{ route('admin.anime.index') }}" class="text-purple-500 text-sm hover:text-purple-400">View All</a>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-gray-400 border-b border-gray-800">
                        <th class="text-left py-2 font-medium">Title</th>
                        <th class="text-left py-2 font-medium">Type</th>
                        <th class="text-left py-2 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentAnime as $anime)
                    <tr class="border-b border-gray-800 hover:bg-gray-800 transition-colors">
                        <td class="py-2 truncate max-w-40">{{ $anime->title }}</td>
                        <td class="py-2 text-gray-400">{{ $anime->type ?? 'N/A' }}</td>
                        <td class="py-2">
                            <span class="px-2 py-0.5 rounded text-xs font-medium
                                @if($anime->status === 'Completed') bg-green-900 text-green-300
                                @elseif($anime->status === 'Ongoing') bg-blue-900 text-blue-300
                                @elseif($anime->status === 'Upcoming') bg-yellow-900 text-yellow-300
                                @else bg-gray-800 text-gray-400 @endif">
                                {{ $anime->status ?? 'N/A' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="bg-gray-900 rounded-lg p-5 border border-gray-800">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold">Recent Users</h2>
                <a href="{{ route('admin.users.index') }}" class="text-purple-500 text-sm hover:text-purple-400">View All</a>
            </div>
            @if($recentUsers->count())
            <div class="space-y-3">
                @foreach($recentUsers as $user)
                <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800 transition-colors">
                    <div class="w-8 h-8 rounded-full bg-purple-700 flex items-center justify-center text-xs font-bold flex-shrink-0">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ $user->name }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ $user->email }}</p>
                    </div>
                    <span class="text-xs text-gray-500 flex-shrink-0">{{ $user->created_at->diffForHumans() }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 text-sm">No users yet</p>
            @endif
        </div>

        <div class="bg-gray-900 rounded-lg p-5 border border-gray-800">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold">Pending Reports</h2>
                <a href="{{ route('admin.reports.index') }}" class="text-purple-500 text-sm hover:text-purple-400">View All</a>
            </div>
            @if($reportsByType->count())
            <div class="space-y-3">
                @foreach($reportsByType as $type => $count)
                <div class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-800 transition-colors">
                    <span class="text-sm capitalize">{{ str_replace('_', ' ', $type) }}</span>
                    <span class="text-sm font-semibold px-2.5 py-0.5 rounded-full bg-red-900 text-red-300">{{ $count }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 text-sm">No pending reports</p>
            @endif
        </div>
    </div>
</div>
@endsection --}}
