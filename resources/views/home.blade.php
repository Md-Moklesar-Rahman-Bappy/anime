@extends('layouts.main')

@section('title', 'Watch Anime Online Free')
@section('description', 'Stream the latest anime episodes free in HD on ' . config('app.name', 'AniKoto') . '. Sub, Dub, Movies, OVAs — daily updates.')

@section('content')

{{-- ╔══════════════════════════════════════════╗
     ║         HERO SLIDER                      ║
     ╚══════════════════════════════════════════╝ --}}
@if(!empty($featured) && $featured->count())
    <section
        class="relative mb-10 -mx-4 sm:-mx-6 lg:-mx-8"
        x-data="{
            current: 0,
            total: {{ $featured->count() }},
            timer: null,
            next() { this.current = (this.current + 1) % this.total; },
            prev() { this.current = (this.current - 1 + this.total) % this.total; },
            start() {
                this.stop();
                this.timer = setInterval(() => this.next(), 7000);
            },
            stop() { clearInterval(this.timer); }
        }"
        x-init="start()"
        @mouseenter="stop()"
        @mouseleave="start()"
    >
        <div class="relative h-[420px] sm:h-[500px] lg:h-[560px] overflow-hidden">

            @foreach($featured as $idx => $hero)
                <div
                    x-show="current === {{ $idx }}"
                    x-transition:enter="transition ease-out duration-700"
                    x-transition:enter-start="opacity-0 scale-105"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-500"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="absolute inset-0"
                >
                    {{-- Backdrop --}}
                    {{ $hero->banner_url ?? $hero->thumbnail_url }}
                         class="absolute inset-0 w-full h-full object-cover"
                         alt="{{ $hero->title }}"
                         loading="{{ $idx === 0 ? 'eager' : 'lazy' }}"
                    >

                    {{-- Gradients --}}
                    <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0f] via-[#0a0a0f]/80 to-transparent"></div>
                    <div class="absolute inset-0 bg-gradient-to-r from-[#0a0a0f] via-transparent to-transparent"></div>

                    {{-- Content --}}
                    <div class="relative h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-end pb-12 sm:pb-16">
                        <div class="max-w-xl space-y-4">

                            {{-- Tag chips --}}
                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                @if($hero->type)
                                    <span class="badge-indigo uppercase">{{ $hero->type }}</span>
                                @endif
                                @if($hero->status)
                                    <span class="badge-success">{{ ucfirst($hero->status) }}</span>
                                @endif
                                @if($hero->rating)
                                    <span class="badge-warning">⭐ {{ number_format($hero->rating, 1) }}</span>
                                @endif
                                @if($hero->year)
                                    <span class="badge-gray">{{ $hero->year }}</span>
                                @endif
                            </div>

                            {{-- Title --}}
                            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white leading-tight">
                                {{ $hero->title }}
                            </h1>

                            {{-- Meta --}}
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-400">
                                @if($hero->episodes_count ?? $hero->episode_count ?? null)
                                    <span><i class="fas fa-play-circle text-indigo-400"></i> {{ $hero->episodes_count ?? $hero->episode_count }} Episodes</span>
                                @endif
                                @if($hero->duration ?? null)
                                    <span><i class="fas fa-clock text-gray-500"></i> {{ $hero->duration }} min</span>
                                @endif
                                @if(isset($hero->genres) && $hero->genres->count())
                                    <span class="hidden sm:inline">
                                        <i class="fas fa-tag text-gray-500"></i>
                                        {{ $hero->genres->pluck('name')->take(3)->join(', ') }}
                                    </span>
                                @endif
                            </div>

                            {{-- Description --}}
                            <p class="text-sm text-gray-300 clamp-3 leading-relaxed">
                                {{ $hero->description ?? $hero->synopsis ?? '' }}
                            </p>

                            {{-- Actions --}}
                            <div class="flex flex-wrap items-center gap-3 pt-2">
                                {{ route('watch', $hero->slug) }} class="btn-primary btn-lg">
                                    <i class="fas fa-play"></i> Watch Now
                                </a>
                                {{ route('anime.detail', $hero->slug) }} class="btn-outline btn-lg">
                                    <i class="fas fa-info-circle"></i> Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Prev / Next arrows --}}
            @if($featured->count() > 1)
                <button @click="prev()"
                        class="absolute left-2 sm:left-4 top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-black/40 hover:bg-black/70 text-white flex items-center justify-center transition backdrop-blur-sm"
                        aria-label="Previous slide">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button @click="next()"
                        class="absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-black/40 hover:bg-black/70 text-white flex items-center justify-center transition backdrop-blur-sm"
                        aria-label="Next slide">
                    <i class="fas fa-chevron-right"></i>
                </button>

                {{-- Dots --}}
                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-10 flex gap-2">
                    @foreach($featured as $idx => $_)
                        <button
                            @click="current = {{ $idx }}"
                            :class="current === {{ $idx }} ? 'w-8 bg-indigo-500' : 'w-2 bg-white/40 hover:bg-white/70'"
                            class="h-2 rounded-full transition-all"
                            aria-label="Go to slide {{ $idx + 1 }}"
                        ></button>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif


{{-- ╔══════════════════════════════════════════╗
     ║         GENRE QUICK FILTER               ║
     ╚══════════════════════════════════════════╝ --}}
@if(isset($genres) && $genres->count())
    <section class="mb-10">
        <div class="flex gap-2 overflow-x-auto no-scrollbar pb-2">
            {{ route('home') }}-cancel btn-sm shrink-0">
                All
            </a>
            @foreach($genres->take(15) as $genre)
                {{ route('genre', $genre->slug) }}-outline btn-sm shrink-0">
                    {{ $genre->name }}
                </a>
            @endforeach
        </div>
    </section>
@endif


{{-- ╔══════════════════════════════════════════╗
     ║         CONTINUE WATCHING (auth users)   ║
     ╚══════════════════════════════════════════╝ --}}
@auth
    @if(!empty($continueWatching) && $continueWatching->count())
        <section class="mb-10">
            <div class="section-title">
                <h2><i class="fas fa-history text-pink-400"></i> Continue Watching</h2>
                #
            </div>

            <div class="flex gap-4 overflow-x-auto no-scrollbar pb-2">
                @foreach($continueWatching as $item)
                    {{ route('watch', $item->anime->slug) }} class="shrink-0 w-60 group">
                        <div class="relative aspect-thumb rounded-lg overflow-hidden bg-gray-900">
                            {{ $item->thumbnail_url ?? $item->anime->thumbnail_url }}
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                                 alt="{{ $item->anime->title }}"
                                 loading="lazy"
                            >

                            {{-- Progress bar --}}
                            @if($item->progress ?? 0)
                                <div class="absolute bottom-0 inset-x-0 h-1 bg-black/40">
                                    <div class="h-full bg-indigo-500" style="width: {{ min(100, $item->progress) }}%"></div>
                                </div>
                            @endif

                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                <i class="fas fa-play text-white text-3xl"></i>
                            </div>

                            <span class="absolute top-2 left-2 badge-indigo">EP {{ $item->number ?? '?' }}</span>
                        </div>
                        <p class="text-sm text-gray-300 mt-2 clamp-1 group-hover:text-white">
                            {{ $item->anime->title }}
                        </p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
@endauth


{{-- ╔══════════════════════════════════════════╗
     ║         LATEST EPISODES                  ║
     ╚══════════════════════════════════════════╝ --}}
@if(!empty($latestEpisodes) && $latestEpisodes->count())
    <section class="mb-10">
        <div class="section-title">
            <h2><i class="fas fa-play-circle text-indigo-400"></i> Latest Episodes</h2>
            {{ route('updated') }}>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @foreach($latestEpisodes as $episode)
                {{ route('watch', $episode->anime->slug) }} class="group">
                    <div class="relative aspect-thumb rounded-lg overflow-hidden bg-gray-900">
                        {{ $episode->thumbnail_url }}
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                             alt="{{ $episode->anime->title }}"
                             loading="lazy"
                        >

                        {{-- Hover play --}}
                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                            <div class="w-12 h-12 rounded-full bg-indigo-600 flex items-center justify-center">
                                <i class="fas fa-play text-white"></i>
                            </div>
                        </div>

                        {{-- Episode badge --}}
                        <div class="absolute top-2 left-2 badge-indigo">
                            EP {{ $episode->number }}
                        </div>

                        {{-- SUB / DUB --}}
                        <div class="absolute top-2 right-2 flex flex-col gap-1">
                            @if($episode->has_sub)
                                <span class="badge-sky">SUB</span>
                            @endif
                            @if($episode->has_dub)
                                <span class="badge-success">DUB</span>
                            @endif
                        </div>

                        {{-- Duration --}}
                        @if($episode->duration ?? null)
                            <div class="absolute bottom-2 right-2 bg-black/70 px-1.5 py-0.5 rounded text-[10px] text-white font-medium">
                                {{ $episode->duration }}m
                            </div>
                        @endif
                    </div>

                    <p class="text-sm text-gray-300 mt-2 clamp-2 leading-snug group-hover:text-white transition">
                        {{ $episode->anime->title }}
                    </p>

                    @if($episode->created_at)
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $episode->created_at->diffForHumans() }}
                        </p>
                    @endif
                </a>
            @endforeach
        </div>
    </section>
@endif


{{-- ╔══════════════════════════════════════════╗
     ║         TRENDING TOP 10                  ║
     ╚══════════════════════════════════════════╝ --}}
@if(!empty($topAnime) && count($topAnime))
    <section class="mb-10">
        <div class="section-title">
            <h2><i class="fas fa-fire text-pink-400"></i> Trending Top 10</h2>
            {{ route('trending') }}>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
            @foreach(collect($topAnime)->take(10) as $i => $anime)
                {{ route('anime.detail', $anime->slug) }} class="flex items-center gap-3 group p-2 rounded-lg hover:bg-white/5 transition">

                    {{-- Big number --}}
                    <span class="text-3xl sm:text-4xl font-black text-transparent bg-clip-text bg-gradient-to-b from-indigo-400 to-purple-500 leading-none shrink-0 w-10 sm:w-12 text-center">
                        {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}
                    </span>

                    {{-- Poster --}}
                    <div class="aspect-poster w-12 sm:w-14 rounded overflow-hidden bg-gray-900 shrink-0">
                        {{ $anime->thumbnail_url ?? $anime->poster_url }}
                             class="w-full h-full object-cover group-hover:scale-105 transition"
                             alt="{{ $anime->title }}"
                             loading="lazy"
                        >
                    </div>

                    {{-- Info --}}
                    <div class="min-w-0 flex-1">
                        <p class="text-sm text-gray-200 group-hover:text-white clamp-2 leading-snug">
                            {{ $anime->title }}
                        </p>
                        <div class="flex items-center gap-2 text-xs text-gray-500 mt-1">
                            @if($anime->rating ?? null)
                                <span>⭐ {{ number_format($anime->rating, 1) }}</span>
                            @endif
                            @if($anime->type ?? null)
                                <span class="uppercase">{{ $anime->type }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endif


{{-- ╔══════════════════════════════════════════╗
     ║         3-COLUMN LISTS                   ║
     ╚══════════════════════════════════════════╝ --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">

    {{-- NEW RELEASE --}}
    @include('partials.home-list', [
        'title' => 'New Release',
        'icon'  => 'fa-bolt',
        'iconColor' => 'text-amber-400',
        'items' => $newAnime ?? collect(),
        'link'  => route('newest'),
    ])

    {{-- NEWLY ADDED --}}
    @include('partials.home-list', [
        'title' => 'Newly Added',
        'icon'  => 'fa-star',
        'iconColor' => 'text-emerald-400',
        'items' => $newlyAdded ?? collect(),
        'link'  => route('newest'),
    ])

    {{-- COMPLETED --}}
    @include('partials.home-list', [
        'title' => 'Just Completed',
        'icon'  => 'fa-circle-check',
        'iconColor' => 'text-sky-400',
        'items' => $justCompleted ?? collect(),
        'link'  => '#',
    ])

</div>


{{-- ╔══════════════════════════════════════════╗
     ║         JOIN COMMUNITY CTA (guests)      ║
     ╚══════════════════════════════════════════╝ --}}
@guest
    <section class="relative overflow-hidden rounded-2xl border border-indigo-500/30 bg-gradient-to-br from-indigo-600/20 via-purple-600/10 to-pink-600/20 p-6 sm:p-10 text-center">
        <h2 class="text-xl sm:text-2xl font-bold text-white mb-2">
            Ready to dive deeper?
        </h2>
        <p class="text-sm text-gray-300 max-w-md mx-auto mb-5">
            Create a free account to track watch history, build watchlists, and join the community.
        </p>
        <div class="flex flex-wrap items-center justify-center gap-3">
            {{ route('auth.register') }}>
                <i class="fas fa-user-plus"></i> Create Free Account
            </a>
            {{ route('auth.login') }}-outline">
                Login
            </a>
        </div>
    </section>
@endguest

@endsection