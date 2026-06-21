@extends('admin.layouts.app')

@section('content')
<div
    class="max-w-7xl mx-auto"
    x-data="dashboard({
        userGrowthLabels: @json($userGrowth->pluck('label')),
        userGrowthData: @json($userGrowth->pluck('count')),
        typeLabels: @json($animeByType->keys()),
        typeData: @json($animeByType->values()),
        statusLabels: @json($animeByStatus->keys()),
        statusData: @json($animeByStatus->values()),
    })"
    x-init="init()"
>

    {{-- HEADER --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-semibold text-white">Dashboard</h1>
            <p class="text-sm text-gray-400">
                {{ now()->format('l, F j, Y') }}
            </p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('admin.anime.create') }}"
               class="px-3 py-1.5 text-sm rounded-md bg-indigo-600 hover:bg-indigo-500 text-white transition">
                + Anime
            </a>
            <a href="{{ route('admin.manga.create') }}"
               class="px-3 py-1.5 text-sm rounded-md bg-emerald-600 hover:bg-emerald-500 text-white transition">
                + Manga
            </a>
        </div>
    </div>

    {{-- STAT CARDS --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
        @foreach([
            ['Anime',    $totalAnime,    '🎬', 'from-indigo-500/20 to-indigo-500/0'],
            ['Episodes', $totalEpisodes, '▶',  'from-pink-500/20 to-pink-500/0'],
            ['Manga',    $totalManga,    '📖', 'from-emerald-500/20 to-emerald-500/0'],
            ['Chapters', $totalChapters, '📄', 'from-amber-500/20 to-amber-500/0'],
            ['Users',    $totalUsers,    '👥', 'from-sky-500/20 to-sky-500/0'],
        ] as [$label, $value, $icon, $gradient])
            <div class="relative overflow-hidden rounded-2xl border border-gray-800 bg-[#111827] p-4">
                <div class="absolute inset-0 bg-gradient-to-br {{ $gradient }} pointer-events-none"></div>

                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs uppercase tracking-wider text-gray-400">
                            {{ $label }}
                        </p>
                        <span class="text-lg">{{ $icon }}</span>
                    </div>

                    <p class="text-2xl font-bold text-white">
                        {{ number_format($value) }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- CHARTS --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

        {{-- User Growth --}}
        <div class="rounded-2xl border border-gray-800 bg-[#111827] p-5">
            <h2 class="text-base font-semibold text-white mb-3">
                User Growth
            </h2>
            <div class="h-64">
                <canvas id="userGrowthChart"></canvas>
            </div>
        </div>

        {{-- Anime Types --}}
        <div class="rounded-2xl border border-gray-800 bg-[#111827] p-5">
            <h2 class="text-base font-semibold text-white mb-3">
                Anime Types
            </h2>
            <div class="h-64">
                <canvas id="animeByTypeChart"></canvas>
            </div>
        </div>

        {{-- Anime Status --}}
        <div class="rounded-2xl border border-gray-800 bg-[#111827] p-5">
            <h2 class="text-base font-semibold text-white mb-3">
                Anime Status
            </h2>
            <div class="h-64">
                <canvas id="animeByStatusChart"></canvas>
            </div>
        </div>

    </div>

    {{-- RECENT EPISODES --}}
    <div class="rounded-2xl border border-gray-800 bg-[#111827] p-5">

        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-white">
                Recent Episodes
            </h2>

            <a href="{{ route('admin.episodes.index') }}"
               class="text-xs text-indigo-400 hover:text-indigo-300">
                View all →
            </a>
        </div>

        @forelse($recentEpisodes as $episode)
            <div class="flex items-center gap-3 py-3 border-b border-gray-800 last:border-0">

                {{-- Thumbnail --}}
                <div class="w-16 h-10 bg-gray-800 rounded overflow-hidden flex-shrink-0">
                    @if($episode->thumbnail_url)
                        <img
                            src="{{ $episode->thumbnail_url }}"
                            alt="{{ $episode->anime->title ?? '' }}"
                            class="w-full h-full object-cover"
                        >
                    @endif
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-white truncate">
                        {{ $episode->anime->title ?? '-' }}
                    </p>
                    <p class="text-xs text-gray-400">
                        Episode {{ $episode->number }}
                    </p>
                </div>

                {{-- Action --}}
                @if($episode->anime)
                    <a href="{{ route('admin.anime.edit', $episode->anime) }}"
                       class="text-xs text-gray-400 hover:text-white">
                        Edit
                    </a>
                @endif

            </div>
        @empty
            <p class="text-sm text-gray-500 py-6 text-center">
                No episodes yet
            </p>
        @endforelse

    </div>

</div>
@endsection