@extends('layouts.main')

@section('title', 'Manga')
@section('description', 'Read the latest manga, manhwa, and manhua free in HD on ' . config('app.name', 'AniKoto') . '.')

@section('content')

{{-- ╔══════════════════════════════════════════╗
     ║         PAGE HEADER (quick actions)      ║
     ╚══════════════════════════════════════════╝ --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <h1 class="sr-only">Manga Home</h1>

    <div class="flex flex-wrap items-center gap-2">
        {{ route('manga.index') }} class="btn-success btn-sm">
            <i class="fas fa-bars"></i> Browse All Manga
        </a>
        {{ route('manga.updated') }} class="btn-cancel btn-sm">
            <i class="fas fa-arrows-rotate"></i> Recently Updated
        </a>
        {{ route('manga.trending') }} class="btn-cancel btn-sm">
            <i class="fas fa-fire"></i> Trending
        </a>
    </div>
</div>


{{-- ╔══════════════════════════════════════════╗
     ║         HERO SLIDER                      ║
     ╚══════════════════════════════════════════╝ --}}
@if($featured->count())
    <section
        x-data="{
            current: 0,
            autoplay: true,
            interval: null,
            progress: 0,
            total: {{ $featured->count() }},
            touchStartX: 0,
            init() { this.startAutoplay(); },
            startAutoplay() {
                if (this.interval) clearInterval(this.interval);
                this.progress = 0;
                this.interval = setInterval(() => {
                    this.progress += 1;
                    if (this.progress >= 100) { this.progress = 0; this.next(); }
                }, 50);
            },
            stopAutoplay() {
                if (this.interval) { clearInterval(this.interval); this.interval = null; }
                this.progress = 0;
            },
            next() {
                if (this.total <= 1) return;
                this.current = (this.current + 1) % this.total;
                if (this.autoplay) this.startAutoplay();
            },
            prev() {
                if (this.total <= 1) return;
                this.current = (this.current - 1 + this.total) % this.total;
                if (this.autoplay) this.startAutoplay();
            },
            goTo(i) {
                if (this.current === i) return;
                this.current = i;
                if (this.autoplay) this.startAutoplay();
            },
            handleTouchStart(e) { this.touchStartX = e.touches[0].clientX; },
            handleTouchEnd(e) {
                let diff = this.touchStartX - e.changedTouches[0].clientX;
                if (Math.abs(diff) > 50) { diff > 0 ? this.next() : this.prev(); }
            }
        }"
        x-init="init()"
        @keydown.left.window="prev()"
        @keydown.right.window="next()"
        @mouseenter="stopAutoplay()"
        @mouseleave="if (autoplay) startAutoplay()"
        @touchstart="handleTouchStart($event)"
        @touchend="handleTouchEnd($event)"
        class="relative overflow-hidden rounded-2xl border border-gray-800 mb-8 group"
        style="height: 420px"
        tabindex="0" role="region" aria-label="Featured Manga"
    >
        {{-- ── SLIDES ── --}}
        @foreach($featured as $i => $manga)
            <div
                x-show="current === {{ $i }}"
                x-transition:enter="transition ease-out duration-700"
                x-transition:enter-start="opacity-0 scale-105"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-500"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute inset-0"
                :aria-hidden="current !== {{ $i }}"
            >
                {{ $manga->banner_url ?? $manga->thumbnail_url ?? asset('fallback.jpg') }}
                     alt="{{ $manga->title }}"
                     class="absolute inset-0 w-full h-full object-cover"
                     loading="{{ $i === 0 ? 'eager' : 'lazy' }}">

                {{-- Gradients --}}
                <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0f] via-[#0a0a0f]/70 to-transparent"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-[#0a0a0f]/90 via-transparent to-transparent"></div>

                {{-- Content --}}
                <div class="absolute bottom-0 inset-x-0 p-6 sm:p-10">
                    <div class="max-w-2xl space-y-3">

                        {{-- Badges --}}
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <span class="badge-success">{{ $manga->type ?? 'Manga' }}</span>
                            @if($manga->status)
                                @if($manga->status === 'Ongoing')
                                    <span class="badge-danger">{{ $manga->status }}</span>
                                @else
                                    <span class="badge-gray">{{ $manga->status }}</span>
                                @endif
                            @endif
                            @if($manga->rating)
                                <span class="badge-warning">⭐ {{ number_format($manga->rating, 1) }}</span>
                            @endif
                            @if($manga->year)
                                <span class="badge-gray">{{ $manga->year }}</span>
                            @endif
                        </div>

                        {{-- Title --}}
                        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-white leading-tight drop-shadow-lg">
                            {{ $manga->title }}
                        </h2>

                        {{-- Description --}}
                        @if($manga->description)
                            <p class="text-sm text-gray-300 clamp-2 leading-relaxed">
                                {{ $manga->description }}
                            </p>
                        @endif

                        {{-- Actions --}}
                        <div class="flex flex-wrap items-center gap-3 pt-2">
                            {{ route('manga.detail', $manga->slug) }} class="btn-success btn-lg">
                                <i class="fas fa-book-open"></i> Read Now
                            </a>
                            {{ route('manga.detail', $manga->slug) }}-outline btn-lg">
                                <i class="fas fa-info-circle"></i> Details
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        @endforeach

        @if($featured->count() > 1)
            {{-- Top progress bars --}}
            <div class="absolute top-0 inset-x-0 z-10 flex gap-1 p-2">
                @foreach($featured as $i => $_)
                    <div class="flex-1 h-1 bg-white/20 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full transition-all duration-100"
                             :style="'width: ' + (current === {{ $i }} ? progress + '%' : (current > {{ $i }} ? '100%' : '0%'))"></div>
                    </div>
                @endforeach
            </div>

            {{-- Prev / Next arrows --}}
            <button @click="prev()"
                    class="absolute left-2 sm:left-4 top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-black/40 hover:bg-black/70 text-white flex items-center justify-center backdrop-blur-sm opacity-0 group-hover:opacity-100 transition"
                    aria-label="Previous slide">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button @click="next()"
                    class="absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-black/40 hover:bg-black/70 text-white flex items-center justify-center backdrop-blur-sm opacity-0 group-hover:opacity-100 transition"
                    aria-label="Next slide">
                <i class="fas fa-chevron-right"></i>
            </button>

            {{-- Bottom dots + counter --}}
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-10 flex items-center gap-3">
                <div class="flex gap-1.5">
                    @foreach($featured as $i => $_)
                        <button @click="goTo({{ $i }})"
                                :class="current === {{ $i }} ? 'w-8 bg-emerald-500' : 'w-2 bg-white/40 hover:bg-white/70'"
                                class="h-2 rounded-full transition-all"
                                aria-label="Go to slide {{ $i + 1 }}"></button>
                    @endforeach
                </div>
                <span class="hidden sm:inline text-xs text-white/60 font-mono"
                      x-text="(current + 1).toString().padStart(2,'0') + ' / ' + total.toString().padStart(2,'0')"></span>
            </div>
        @endif
    </section>
@endif


{{-- ╔══════════════════════════════════════════╗
     ║         GENRE QUICK STRIP                ║
     ╚══════════════════════════════════════════╝ --}}
@if(isset($genres) && $genres->count())
    <section class="mb-8">
        <div class="flex gap-2 overflow-x-auto no-scrollbar pb-2">
            {{ route('manga.index') }}-cancel btn-sm shrink-0">All</a>
            @foreach($genres->take(15) as $g)
                {{ route('manga.genre', $g->slug) }}-outline btn-sm shrink-0">
                    {{ $g->name }}
                </a>
            @endforeach
        </div>
    </section>
@endif


{{-- ╔══════════════════════════════════════════╗
     ║         MAIN GRID (3/9 split)            ║
     ╚══════════════════════════════════════════╝ --}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">

    {{-- ─────── LEFT (main content) ─────── --}}
    <div class="lg:col-span-9 space-y-8">

        {{-- TRENDING --}}
        <section>
            <div class="section-title">
                <h2><i class="fas fa-fire text-pink-400"></i> Trending</h2>
                {{ route('manga.trending') }}>
            </div>

            @if($trending->count())
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    @foreach($trending as $manga)
                        {{ route('manga.detail', $manga->slug) }} class="anime-card">

                            {{ $manga->thumbnail_url }}
                                 alt="{{ $manga->title }}"
                                 class="anime-card-img"
                                 loading="lazy">

                            <div class="absolute top-2 left-2">
                                <span class="badge bg-black/70 text-white">{{ $manga->type ?? 'Manga' }}</span>
                            </div>

                            @if($manga->chapters_count > 0)
                                <div class="absolute top-2 right-2">
                                    <span class="badge-success">Ch. {{ $manga->chapters_count }}</span>
                                </div>
                            @endif

                            <div class="anime-card-overlay flex items-end p-3">
                                <div>
                                    <p class="text-white text-sm font-semibold clamp-2 mb-1">{{ $manga->title }}</p>
                                    @if($manga->rating)
                                        <p class="text-xs text-amber-400">⭐ {{ number_format($manga->rating, 1) }}</p>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="card p-8 text-center">
                    <i class="fas fa-fire text-3xl text-gray-700 mb-2"></i>
                    <p class="text-sm text-gray-400">No trending manga yet.</p>
                </div>
            @endif
        </section>

        {{-- RECENTLY UPDATED --}}
        <section>
            <div class="section-title">
                <h2><i class="fas fa-arrows-rotate text-emerald-400"></i> Recently Updated</h2>
                {{ route('manga.updated') }}>
            </div>

            @if($recentChapters->count())
                @php
                    $typeColors = [
                        'Manhwa'    => 'text-orange-400',
                        'Manhua'    => 'text-sky-400',
                        'One-shot'  => 'text-pink-400',
                        'Doujinshi' => 'text-purple-400',
                    ];
                @endphp

                <div class="card divide-y divide-gray-800">
                    @foreach($recentChapters as $chapter)
                        {{ route('manga.read', ['slug' => $chapter->manga->slug, 'chapter' => $chapter->number]) }}
                           class="flex items-center gap-3 p-3 sm:p-4 hover:bg-white/[0.03] transition group">

                            {{-- Cover --}}
                            <div class="aspect-manga w-10 sm:w-12 rounded overflow-hidden bg-gray-900 shrink-0">
                                {{ $chapter->manga->thumbnail_url }}
                                     alt=""
                                     class="w-full h-full object-cover group-hover:scale-105 transition"
                                     loading="lazy">
                            </div>

                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-white clamp-1 group-hover:text-emerald-300 transition">
                                    {{ $chapter->manga->title }}
                                </p>
                                <div class="flex items-center gap-2 text-xs text-gray-500 mt-0.5 flex-wrap">
                                    <span class="font-medium {{ $typeColors[$chapter->manga->type] ?? 'text-emerald-400' }}">
                                        {{ $chapter->manga->type ?? 'Manga' }}
                                    </span>
                                    <span class="text-gray-700">•</span>
                                    <span>Ch. {{ rtrim(rtrim($chapter->number, '0'), '.') }}</span>
                                    @if($chapter->title)
                                        <span class="text-gray-700">•</span>
                                        <span class="clamp-1 max-w-[200px]">{{ $chapter->title }}</span>
                                    @endif
                                </div>
                            </div>

                            <span class="text-xs text-gray-500 shrink-0 ml-2">
                                {{ $chapter->created_at->diffForHumans() }}
                            </span>

                            <i class="fas fa-chevron-right text-gray-600 text-xs group-hover:text-emerald-400 transition hidden sm:block"></i>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="card p-8 text-center">
                    <i class="fas fa-arrows-rotate text-3xl text-gray-700 mb-2"></i>
                    <p class="text-sm text-gray-400">No recent updates yet.</p>
                </div>
            @endif
        </section>

    </div>

    {{-- ─────── RIGHT (sidebar) ─────── --}}
    <aside class="lg:col-span-3 space-y-4">

        {{-- MOST VIEWED --}}
        <div x-data="{ tab: 'day' }" class="card p-4">
            <h3 class="text-base font-semibold text-white mb-3 flex items-center gap-2">
                <i class="fas fa-eye text-pink-400"></i> Most Viewed
            </h3>

            <div class="flex gap-1 mb-3">
                @foreach(['day' => 'Day', 'week' => 'Week', 'month' => 'Month'] as $key => $label)
                    <button @click="tab = '{{ $key }}'"
                            :class="tab === '{{ $key }}' ? 'bg-emerald-600 text-white' : 'bg-gray-800/60 text-gray-400 hover:text-white'"
                            class="flex-1 px-2 py-1 text-xs rounded transition">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            @if($mostViewed->count())
                <div class="space-y-2">
                    @foreach($mostViewed->take(5) as $i => $manga)
                        {{ route('manga.detail', $manga->slug) }}
                           class="flex items-center gap-2 group p-1.5 -m-1.5 rounded-lg hover:bg-white/5 transition">

                            <span class="text-lg font-bold w-5 text-center shrink-0 {{ $i < 3 ? 'text-emerald-400' : 'text-gray-600' }}">
                                {{ $i + 1 }}
                            </span>

                            <div class="aspect-manga w-10 rounded overflow-hidden bg-gray-900 shrink-0">
                                {{ $manga->thumbnail_url }}
                                     alt=""
                                     class="w-full h-full object-cover"
                                     loading="lazy">
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-200 group-hover:text-white clamp-1">
                                    {{ $manga->title }}
                                </p>
                                <div class="flex items-center gap-1 text-xs text-gray-500 mt-0.5">
                                    <i class="fas fa-star text-amber-400 text-[10px]"></i>
                                    <span>{{ number_format($manga->rating ?? 0, 1) }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-gray-500 text-center py-4">No data yet.</p>
            @endif
        </div>

        {{-- NEW RELEASE --}}
        <div class="card p-4">
            <h3 class="text-base font-semibold text-white mb-3 flex items-center gap-2">
                <i class="fas fa-sparkles text-amber-400"></i> New Release
            </h3>

            @if($newManga->count())
                <div class="space-y-2">
                    @foreach($newManga->take(5) as $manga)
                        {{ route('manga.detail', $manga->slug) }}
                           class="flex items-center gap-2 group p-1.5 -m-1.5 rounded-lg hover:bg-white/5 transition">

                            <div class="aspect-manga w-10 rounded overflow-hidden bg-gray-900 shrink-0">
                                {{ $manga->thumbnail_url }}
                                     alt=""
                                     class="w-full h-full object-cover"
                                     loading="lazy">
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-200 group-hover:text-white clamp-1">
                                    {{ $manga->title }}
                                </p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $manga->year }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-gray-500 text-center py-4">No new releases.</p>
            @endif
        </div>

    </aside>
</div>


{{-- ╔══════════════════════════════════════════╗
     ║         LATEST MANGA (bottom grid)       ║
     ╚══════════════════════════════════════════╝ --}}
@if($newManga->count() > 5)
    <section>
        <div class="section-title">
            <h2><i class="fas fa-clock text-sky-400"></i> Latest Manga</h2>
            {{ route('manga.newest') }}>
        </div>

        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3">
            @foreach($newManga as $manga)
                {{ route('manga.detail', $manga->slug) }} class="anime-card">
                    {{ $manga->thumbnail_url }}
                         alt="{{ $manga->title }}"
                         class="anime-card-img"
                         loading="lazy">

                    <div class="anime-card-overlay flex items-end p-2">
                        <p class="text-white text-xs font-semibold clamp-2">{{ $manga->title }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endif


{{-- ╔══════════════════════════════════════════╗
     ║         GUEST CTA                        ║
     ╚══════════════════════════════════════════╝ --}}
@guest
    <section class="mt-10 relative overflow-hidden rounded-2xl border border-emerald-500/30 bg-gradient-to-br from-emerald-600/20 via-emerald-600/10 to-sky-600/20 p-6 sm:p-10 text-center">
        <h2 class="text-xl sm:text-2xl font-bold text-white mb-2">
            Build your manga library
        </h2>
        <p class="text-sm text-gray-300 max-w-md mx-auto mb-5">
            Create a free account to track reading progress, save favorites, and join the community.
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