@extends('layouts.main')

@section('title', 'Dashboard')
@section('description', 'Your personal anime dashboard — continue watching, watchlist, recommendations, and stats.')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">

    {{-- ╔══════════════════════════════════════════╗
         ║         GREETING HEADER                  ║
         ╚══════════════════════════════════════════╝ --}}
    @php
        $hour = (int) now()->format('H');
        $greeting = match (true) {
            $hour < 12  => 'Good morning',
            $hour < 18  => 'Good afternoon',
            default     => 'Good evening',
        };

        $firstName = explode(' ', auth()->user()->name)[0];
    @endphp

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            ={{ urlencode(auth()->user()->name) }}&background=6366f1&color=fff&size=128"
                 class="w-14 h-14 rounded-full border-2 border-gray-800"
                 alt="{{ auth()->user()->name }}"
            >
            <div>
                <h1 class="text-2xl font-bold text-white">
                    {{ $greeting }}, {{ $firstName }} 👋
                </h1>
                <p class="text-sm text-gray-400 mt-0.5">
                    Ready to dive into your next anime adventure?
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            {{ route('home') }} class="btn-primary btn-sm">
                <i class="fas fa-compass"></i> Browse Anime
            </a>
            {{ route('profile.edit') }} class="btn-outline btn-sm">
                <i class="fas fa-user"></i> Profile
            </a>
        </div>
    </div>

    {{-- ╔══════════════════════════════════════════╗
         ║         STATS CARDS                      ║
         ╚══════════════════════════════════════════╝ --}}
    @php
        $stats = $stats ?? [
            'watching'   => 0,
            'completed'  => 0,
            'hours'      => 0,
            'favorites'  => 0,
        ];
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

        <div class="relative overflow-hidden rounded-2xl border border-gray-800 bg-[#111827] p-4">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/15 to-indigo-500/0 pointer-events-none"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs uppercase tracking-wider text-gray-400">Watching</p>
                    <i class="fas fa-play-circle text-indigo-400"></i>
                </div>
                <p class="text-2xl font-bold text-white">{{ number_format($stats['watching']) }}</p>
                <p class="text-xs text-gray-500 mt-1">Currently watching</p>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl border border-gray-800 bg-[#111827] p-4">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/15 to-emerald-500/0 pointer-events-none"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs uppercase tracking-wider text-gray-400">Completed</p>
                    <i class="fas fa-circle-check text-emerald-400"></i>
                </div>
                <p class="text-2xl font-bold text-white">{{ number_format($stats['completed']) }}</p>
                <p class="text-xs text-gray-500 mt-1">Series finished</p>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl border border-gray-800 bg-[#111827] p-4">
            <div class="absolute inset-0 bg-gradient-to-br from-pink-500/15 to-pink-500/0 pointer-events-none"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs uppercase tracking-wider text-gray-400">Hours</p>
                    <i class="fas fa-clock text-pink-400"></i>
                </div>
                <p class="text-2xl font-bold text-white">{{ number_format($stats['hours']) }}</p>
                <p class="text-xs text-gray-500 mt-1">Total watch time</p>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl border border-gray-800 bg-[#111827] p-4">
            <div class="absolute inset-0 bg-gradient-to-br from-amber-500/15 to-amber-500/0 pointer-events-none"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs uppercase tracking-wider text-gray-400">Favorites</p>
                    <i class="fas fa-bookmark text-amber-400"></i>
                </div>
                <p class="text-2xl font-bold text-white">{{ number_format($stats['favorites']) }}</p>
                <p class="text-xs text-gray-500 mt-1">Saved for later</p>
            </div>
        </div>

    </div>

    {{-- ╔══════════════════════════════════════════╗
         ║         CONTINUE WATCHING                ║
         ╚══════════════════════════════════════════╝ --}}
    <section>
        <div class="section-title">
            <h2><i class="fas fa-history text-pink-400"></i> Continue Watching</h2>
            #
        </div>

        @if(!empty($continueWatching) && $continueWatching->count())
            <div class="flex gap-4 overflow-x-auto no-scrollbar pb-2">
                @foreach($continueWatching as $item)
                    {{ route('watch', $item->anime->slug) }} class="shrink-0 w-60 group">
                        <div class="relative aspect-thumb rounded-lg overflow-hidden bg-gray-900 border border-gray-800">

                            {{ $item->thumbnail_url ?? $item->anime->thumbnail_url }}
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                                 alt="{{ $item->anime->title }}"
                                 loading="lazy">

                            {{-- Hover play --}}
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition">
                                <div class="w-12 h-12 rounded-full bg-indigo-600 flex items-center justify-center">
                                    <i class="fas fa-play text-white"></i>
                                </div>
                            </div>

                            {{-- Progress bar --}}
                            @if($item->progress ?? 0)
                                <div class="absolute bottom-0 inset-x-0 h-1.5 bg-black/60">
                                    <div class="h-full bg-indigo-500 transition-all"
                                         style="width: {{ min(100, $item->progress) }}%"></div>
                                </div>
                            @endif

                            <span class="absolute top-2 left-2 badge-indigo">EP {{ $item->number ?? '?' }}</span>
                        </div>

                        <p class="text-sm text-gray-300 mt-2 clamp-1 group-hover:text-white">
                            {{ $item->anime->title }}
                        </p>

                        @if($item->progress ?? 0)
                            <p class="text-xs text-gray-500">
                                {{ round($item->progress) }}% watched
                            </p>
                        @endif
                    </a>
                @endforeach
            </div>
        @else
            <div class="card p-8 text-center">
                <i class="fas fa-play-circle text-3xl text-gray-700 mb-2"></i>
                <p class="text-sm text-white">Nothing here yet</p>
                <p class="text-xs text-gray-500 mt-1">Start watching anime and your progress will appear here.</p>
                {{ route('home') }} class="btn-primary btn-sm mt-3">
                    <i class="fas fa-compass"></i> Browse Anime
                </a>
            </div>
        @endif
    </section>

    {{-- ╔══════════════════════════════════════════╗
         ║         WATCHLIST + RECOMMENDATIONS      ║
         ╚══════════════════════════════════════════╝ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- WATCHLIST --}}
        <section class="lg:col-span-2">
            <div class="section-title">
                <h2><i class="fas fa-bookmark text-amber-400"></i> My Watchlist</h2>
                #
            </div>

            @if(!empty($watchlist) && $watchlist->count())
                <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-4 gap-3">
                    @foreach($watchlist->take(8) as $anime)
                        {{ route('anime.detail', $anime->slug) }} class="anime-card">
                            {{ $anime->thumbnail_url ?? $anime->poster_url }}
                                 alt="{{ $anime->title }}"
                                 class="anime-card-img"
                                 loading="lazy">

                            <div class="anime-card-overlay flex items-end p-2">
                                <p class="text-white text-xs font-semibold clamp-2">{{ $anime->title }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="card p-8 text-center">
                    <i class="fas fa-bookmark text-3xl text-gray-700 mb-2"></i>
                    <p class="text-sm text-white">No saved anime</p>
                    <p class="text-xs text-gray-500 mt-1">Bookmark anime to watch later.</p>
                </div>
            @endif
        </section>

        {{-- RECOMMENDED --}}
        <section>
            <div class="section-title">
                <h2><i class="fas fa-sparkles text-purple-400"></i> For You</h2>
            </div>

            @if(!empty($recommended) && $recommended->count())
                <div class="card divide-y divide-gray-800">
                    @foreach($recommended->take(4) as $anime)
                        {{ route('anime.detail', $anime->slug) }} class="flex gap-3 p-3 group hover:bg-white/[0.02] transition">

                            <div class="aspect-poster w-12 rounded overflow-hidden bg-gray-900 shrink-0">
                                {{ $anime->thumbnail_url ?? $anime->poster_url }}
                                     class="w-full h-full object-cover group-hover:scale-105 transition"
                                     alt="{{ $anime->title }}" loading="lazy">
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-gray-200 group-hover:text-white clamp-2 leading-snug">
                                    {{ $anime->title }}
                                </p>
                                <div class="flex items-center gap-2 text-xs text-gray-500 mt-1">
                                    @if($anime->rating)
                                        <span>⭐ {{ number_format($anime->rating, 1) }}</span>
                                    @endif
                                    @if($anime->type)
                                        <span class="uppercase">{{ $anime->type }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="card p-6 text-center">
                    <i class="fas fa-sparkles text-2xl text-gray-700 mb-2"></i>
                    <p class="text-xs text-gray-500">Watch more anime to get personalized recommendations.</p>
                </div>
            @endif
        </section>

    </div>

    {{-- ╔══════════════════════════════════════════╗
         ║         RECENT ACTIVITY                  ║
         ╚══════════════════════════════════════════╝ --}}
    @if(!empty($recentActivity) && $recentActivity->count())
        <section>
            <div class="section-title">
                <h2><i class="fas fa-clock-rotate-left text-sky-400"></i> Recent Activity</h2>
            </div>

            <div class="card divide-y divide-gray-800">
                @foreach($recentActivity->take(5) as $activity)
                    <div class="flex items-center gap-3 p-4">
                        <div class="w-8 h-8 rounded-full bg-indigo-500/15 text-indigo-400 flex items-center justify-center text-xs shrink-0">
                            <i class="fas {{ $activity->icon ?? 'fa-play' }}"></i>
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-200">
                                {!! $activity->description ?? 'Watched something' !!}
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $activity->created_at?->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ╔══════════════════════════════════════════╗
         ║         QUICK LINKS                      ║
         ╚══════════════════════════════════════════╝ --}}
    <section>
        <h2 class="text-sm uppercase tracking-wider text-gray-500 font-semibold mb-3">
            Quick Links
        </h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">

            {{ route('home') }} class="card card-hover p-4 flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-lg bg-indigo-500/15 text-indigo-400 flex items-center justify-center">
                    <i class="fas fa-compass"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-white group-hover:text-indigo-300">Browse</p>
                    <p class="text-xs text-gray-500">Discover anime</p>
                </div>
            </a>

            #
                <div class="w-10 h-10 rounded-lg bg-pink-500/15 text-pink-400 flex items-center justify-center">
                    <i class="fas fa-fire"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-white group-hover:text-pink-300">Trending</p>
                    <p class="text-xs text-gray-500">What's hot</p>
                </div>
            </a>

            #
                <div class="w-10 h-10 rounded-lg bg-amber-500/15 text-amber-400 flex items-center justify-center">
                    <i class="fas fa-bookmark"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-white group-hover:text-amber-300">Watchlist</p>
                    <p class="text-xs text-gray-500">Saved anime</p>
                </div>
            </a>

            {{ route('profile.edit') }}-3 group">
                <div class="w-10 h-10 rounded-lg bg-emerald-500/15 text-emerald-400 flex items-center justify-center">
                    <i class="fas fa-cog"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-white group-hover:text-emerald-300">Settings</p>
                    <p class="text-xs text-gray-500">Manage account</p>
                </div>
            </a>

        </div>
    </section>

</div>
@endsection