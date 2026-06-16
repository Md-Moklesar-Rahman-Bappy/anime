@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto" x-data="mangaDashboard({
    typeLabels: @json($mangaByType->keys()),
    typeData: @json($mangaByType->values()),
    statusLabels: @json($mangaByStatus->keys()),
    statusData: @json($mangaByStatus->values()),
})">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Manga Dashboard</h1>
            <p class="text-gray-400 text-sm">{{ now()->format('l, F j, Y') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.manga.create') }}" class="bg-emerald-600 hover:bg-emerald-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">+ Add Manga</a>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-gray-900 rounded-lg p-4 border border-gray-800">
            <div class="flex items-center justify-between mb-2">
                <p class="text-gray-400 text-xs uppercase tracking-wide">Total Manga</p>
                <span class="text-emerald-500 text-lg">&#128214;</span>
            </div>
            <p class="text-2xl font-bold">{{ number_format($totalManga) }}</p>
        </div>
        <div class="bg-gray-900 rounded-lg p-4 border border-gray-800">
            <div class="flex items-center justify-between mb-2">
                <p class="text-gray-400 text-xs uppercase tracking-wide">Chapters</p>
                <span class="text-blue-500 text-lg">&#128196;</span>
            </div>
            <p class="text-2xl font-bold">{{ number_format($totalChapters) }}</p>
        </div>
        <div class="bg-gray-900 rounded-lg p-4 border border-gray-800">
            <div class="flex items-center justify-between mb-2">
                <p class="text-gray-400 text-xs uppercase tracking-wide">Total Views</p>
                <span class="text-orange-500 text-lg">&#128065;</span>
            </div>
            <p class="text-2xl font-bold">{{ number_format($totalMangaViews) }}</p>
        </div>
        <div class="bg-gray-900 rounded-lg p-4 border border-gray-800">
            <div class="flex items-center justify-between mb-2">
                <p class="text-gray-400 text-xs uppercase tracking-wide">Users</p>
                <span class="text-yellow-500 text-lg">&#128101;</span>
            </div>
            <p class="text-2xl font-bold">{{ number_format($totalUsers) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-gray-900 rounded-lg p-5 border border-gray-800">
            <h2 class="font-bold mb-1">Manga by Type</h2>
            <p class="text-gray-400 text-xs mb-4">Distribution across types</p>
            <div class="h-64 flex items-center justify-center">
                <canvas id="mangaByTypeChart" class="max-h-64"></canvas>
            </div>
        </div>
        <div class="bg-gray-900 rounded-lg p-5 border border-gray-800">
            <h2 class="font-bold mb-1">Manga by Status</h2>
            <p class="text-gray-400 text-xs mb-4">Current status distribution</p>
            <div class="h-64 flex items-center justify-center">
                <canvas id="mangaByStatusChart" class="max-h-64"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-gray-900 rounded-lg p-5 border border-gray-800">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold">Recent Chapters</h2>
                <a href="{{ route('admin.manga.index') }}" class="text-emerald-500 text-sm hover:text-emerald-400">View All</a>
            </div>
            @if($recentChapters->count())
            <div class="space-y-3">
                @foreach($recentChapters as $chapter)
                <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800 transition-colors">
                    <div class="w-9 h-12 rounded overflow-hidden bg-gray-800 flex-shrink-0">
                        @if($chapter->manga && $chapter->manga->thumbnail)
                        <img src="{{ $chapter->manga->thumbnail_url }}" alt="" class="w-full h-full object-cover">
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ $chapter->manga->title ?? 'Unknown' }}</p>
                        <p class="text-xs text-gray-400">Chapter {{ $chapter->number }}{{ $chapter->title ? ' - '.$chapter->title : '' }}</p>
                    </div>
                    <span class="text-xs text-gray-500 flex-shrink-0">{{ $chapter->created_at->diffForHumans() }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 text-sm">No chapters yet</p>
            @endif
        </div>

        <div class="bg-gray-900 rounded-lg p-5 border border-gray-800">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold">Popular Manga</h2>
                <span class="text-xs text-gray-400">By views</span>
            </div>
            @if($popularManga->count())
            <div class="space-y-3">
                @foreach($popularManga as $i => $manga)
                <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800 transition-colors">
                    <span class="text-sm font-bold text-gray-500 w-5 flex-shrink-0">{{ $i + 1 }}</span>
                    <div class="w-10 h-14 rounded overflow-hidden bg-gray-800 flex-shrink-0">
                        @if($manga->thumbnail)
                        <img src="{{ $manga->thumbnail_url }}" alt="" class="w-full h-full object-cover">
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ $manga->title }}</p>
                        <p class="text-xs text-gray-400">{{ $manga->type ?? 'Manga' }} &middot; {{ $manga->status ?? 'N/A' }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-semibold">{{ number_format($manga->views) }}</p>
                        <p class="text-xs text-gray-500">views</p>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 text-sm">No manga data yet</p>
            @endif
        </div>
    </div>

    <div class="bg-gray-900 rounded-lg p-5 border border-gray-800">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold">Recent Manga</h2>
            <a href="{{ route('admin.manga.index') }}" class="text-emerald-500 text-sm hover:text-emerald-400">View All</a>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-gray-400 border-b border-gray-800">
                    <th class="text-left py-2 font-medium">Title</th>
                    <th class="text-left py-2 font-medium">Type</th>
                    <th class="text-left py-2 font-medium">Status</th>
                    <th class="text-left py-2 font-medium">Chapters</th>
                    <th class="text-left py-2 font-medium">Views</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentManga as $manga)
                <tr class="border-b border-gray-800 hover:bg-gray-800 transition-colors">
                    <td class="py-2 truncate max-w-40">{{ $manga->title }}</td>
                    <td class="py-2 text-gray-400">{{ $manga->type ?? 'Manga' }}</td>
                    <td class="py-2">
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            @if($manga->status === 'Completed') bg-green-900 text-green-300
                            @elseif($manga->status === 'Ongoing') bg-blue-900 text-blue-300
                            @elseif($manga->status === 'Hiatus') bg-yellow-900 text-yellow-300
                            @elseif($manga->status === 'Cancelled') bg-red-900 text-red-300
                            @else bg-gray-800 text-gray-400 @endif">
                            {{ $manga->status ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="py-2 text-gray-400">{{ $manga->chapters_count ?? 0 }}</td>
                    <td class="py-2 text-gray-400">{{ number_format($manga->views) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
function mangaDashboard(chartData) {
    return {
        init() {
            this.$nextTick(() => {
                this.initMangaTypeChart();
                this.initMangaStatusChart();
            });
        },
        initMangaTypeChart() {
            const ctx = document.getElementById('mangaByTypeChart');
            if (!ctx || !chartData.typeLabels?.length) return;
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: chartData.typeLabels,
                    datasets: [{
                        data: chartData.typeData,
                        backgroundColor: ['#10b981', '#f59e0b', '#3b82f6', '#ef4444', '#8b5cf6', '#ec4899'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: '#9ca3af', padding: 16, usePointStyle: true, pointStyle: 'circle' }
                        }
                    }
                }
            });
        },
        initMangaStatusChart() {
            const ctx = document.getElementById('mangaByStatusChart');
            if (!ctx || !chartData.statusLabels?.length) return;
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: chartData.statusLabels,
                    datasets: [{
                        data: chartData.statusData,
                        backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: '#9ca3af', padding: 16, usePointStyle: true, pointStyle: 'circle' }
                        }
                    }
                }
            });
        }
    };
}
</script>
@endpush
