@extends('layouts.main')

@section('title', 'Manga')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="sr-only">Manga Home</h1>
        <div class="flex items-center gap-3">
            <a href="{{ route('manga.browse') }}" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white px-5 py-2.5 rounded-lg font-semibold text-sm transition shadow-lg shadow-emerald-600/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                Browse All Manga
            </a>
            <a href="{{ route('manga.updated') }}" class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-700 text-gray-300 px-5 py-2.5 rounded-lg font-semibold text-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Recently Updated
            </a>
        </div>
    </div>

    @if($featured->count())
    <div
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
            goTo(index) {
                if (this.current === index) return;
                this.current = index;
                if (this.autoplay) this.startAutoplay();
            },
            toggleAutoplay() {
                this.autoplay = !this.autoplay;
                if (this.autoplay) this.startAutoplay();
                else this.stopAutoplay();
            },
            handleTouchStart(e) { this.touchStartX = e.touches[0].clientX; },
            handleTouchEnd(e) {
                let diff = this.touchStartX - e.changedTouches[0].clientX;
                if (Math.abs(diff) > 50) { if (diff > 0) this.next(); else this.prev(); }
            }
        }"
        x-init="init()"
        @keydown.left.window="prev()"
        @keydown.right.window="next()"
        @mouseenter="stopAutoplay()"
        @mouseleave="if (autoplay) startAutoplay()"
        @touchstart="handleTouchStart($event)"
        @touchend="handleTouchEnd($event)"
        class="relative rounded-xl overflow-hidden mb-8 h-[400px] md:h-[500px] group focus:outline-none"
        tabindex="0" role="region" aria-label="Featured Manga"
    >
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
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('{{ $manga->banner_url }}')"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/60 to-transparent"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-gray-950/40 to-transparent"></div>
            <div class="absolute bottom-0 left-0 right-0 p-6 md:p-10">
                <h2 class="text-2xl md:text-4xl font-bold text-white mb-2 drop-shadow-lg">{{ $manga->title }}</h2>
                <div class="flex flex-wrap items-center gap-3 text-sm text-gray-300 mb-3">
                    <span class="px-2 py-0.5 bg-emerald-600/80 rounded text-xs font-semibold">{{ $manga->type ?? 'Manga' }}</span>
                    <span>{{ $manga->year }}</span>
                    <span class="flex items-center">
                        <svg class="w-4 h-4 text-yellow-500 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        {{ $manga->rating ?? 'N/A' }}
                    </span>
                    <span class="px-2 py-0.5 {{ $manga->status === 'Ongoing' ? 'bg-red-600/80' : 'bg-gray-600/80' }} rounded text-xs font-semibold">{{ $manga->status ?? 'Unknown' }}</span>
                </div>
                <p class="text-gray-300 text-sm max-w-xl line-clamp-2 mb-4 drop-shadow-md">{{ Str::limit($manga->description, 200) }}</p>
                <a href="{{ route('manga.detail', $manga->slug) }}" class="inline-flex items-center bg-emerald-600 hover:bg-emerald-500 text-white px-6 py-3 rounded-lg font-semibold transition transform hover:scale-105 active:scale-95 shadow-lg shadow-emerald-600/30">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"/></svg>
                    Read Now
                </a>
            </div>
        </div>
        @endforeach

        @if($featured->count() > 1)
        <div class="absolute top-0 left-0 right-0 z-30 flex space-x-1 p-3">
            @foreach($featured as $i => $manga)
            <div class="flex-1 h-1 bg-white/20 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 rounded-full transition-all duration-100 ease-linear"
                     :style="'width: ' + (current === {{ $i }} ? progress + '%' : (current > {{ $i }} ? '100%' : '0%'))"></div>
            </div>
            @endforeach
        </div>

        <button @click="prev()" class="absolute left-3 top-1/2 -translate-y-1/2 z-20 w-10 h-10 md:w-12 md:h-12 rounded-full bg-black/50 hover:bg-emerald-600 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-emerald-500" aria-label="Previous slide">
            <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button @click="next()" class="absolute right-3 top-1/2 -translate-y-1/2 z-20 w-10 h-10 md:w-12 md:h-12 rounded-full bg-black/50 hover:bg-emerald-600 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-emerald-500" aria-label="Next slide">
            <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        </button>

        <div class="absolute bottom-4 left-0 right-0 z-30 flex items-center justify-center space-x-4">
            <div class="flex items-center space-x-2">
                @foreach($featured as $i => $manga)
                <button @click="goTo({{ $i }})" class="w-2.5 h-2.5 rounded-full transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        :class="current === {{ $i }} ? 'bg-emerald-600 w-6 rounded-full' : 'bg-white/50 hover:bg-white/80'"
                        :aria-label="'Go to slide ' + ({{ $i }} + 1)"></button>
                @endforeach
            </div>
            <span class="text-xs text-white/60 font-mono hidden sm:block" x-text="(current + 1).toString().padStart(2, '0') + ' / ' + total.toString().padStart(2, '0')"></span>
        </div>
        @endif
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <div class="lg:col-span-3 space-y-8">
            <section>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold">Trending</h2>
                    <a href="{{ route('manga.trending') }}" class="text-sm text-emerald-500 hover:text-emerald-400">View All</a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    @foreach($trending as $manga)
                    <a href="{{ route('manga.detail', $manga->slug) }}" class="group">
                        <div class="relative rounded-lg overflow-hidden bg-gray-800 aspect-[2/3]">
                            <img src="{{ $manga->thumbnail_url }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" alt="">
                            <div class="absolute top-2 left-2 bg-gray-900/80 text-xs px-2 py-1 rounded">{{ $manga->type ?? 'Manga' }}</div>
                            @if($manga->chapters_count > 0)
                            <div class="absolute top-2 right-2 bg-emerald-600/90 text-xs px-2 py-1 rounded font-bold">Ch. {{ $manga->chapters_count }}</div>
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition flex items-end p-3">
                                <span class="text-white text-sm font-semibold">View Details</span>
                            </div>
                        </div>
                        <h3 class="text-sm text-gray-300 mt-2 line-clamp-1 group-hover:text-white">{{ $manga->title }}</h3>
                        <div class="flex items-center text-xs text-gray-500 mt-1">
                            <svg class="w-3 h-3 text-yellow-500 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            {{ $manga->rating ?? 'N/A' }}
                        </div>
                    </a>
                    @endforeach
                </div>
            </section>

            <section>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold">Recently Updated</h2>
                    <a href="{{ route('manga.updated') }}" class="text-sm text-emerald-500 hover:text-emerald-400">View All</a>
                </div>
                <div class="space-y-2">
                    @foreach($recentChapters as $chapter)
                    <a href="{{ route('manga.read', ['slug' => $chapter->manga->slug, 'chapter' => $chapter->number]) }}" class="flex items-center justify-between bg-gray-900/60 hover:bg-gray-800/80 rounded-lg px-4 py-3 transition group">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <img src="{{ $chapter->manga->thumbnail_url }}" class="w-9 h-12 object-cover rounded flex-shrink-0" alt="">
                            <div class="min-w-0">
                                <p class="text-sm text-gray-300 truncate group-hover:text-white">{{ $chapter->manga->title }}</p>
                                <p class="text-xs text-gray-500">
                                    @if($chapter->manga->type === 'Manhwa')
                                    <span class="text-orange-400 font-medium">Manhwa</span>
                                    @elseif($chapter->manga->type === 'Manhua')
                                    <span class="text-blue-400 font-medium">Manhua</span>
                                    @elseif($chapter->manga->type === 'One-shot')
                                    <span class="text-pink-400 font-medium">One-shot</span>
                                    @elseif($chapter->manga->type === 'Doujinshi')
                                    <span class="text-purple-400 font-medium">Doujinshi</span>
                                    @else
                                    <span class="text-emerald-400 font-medium">Manga</span>
                                    @endif
                                    <span class="text-gray-600 mx-1">•</span>
                                    <span>Chap {{ $chapter->number }}</span>
                                    @if($chapter->title)
                                    <span class="text-gray-600 mx-1">•</span>
                                    <span class="truncate">{{ $chapter->title }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <span class="text-xs text-gray-600 flex-shrink-0 ml-4">{{ $chapter->created_at->diffForHumans() }}</span>
                    </a>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <div x-data="{ tab: 'day' }" class="bg-gray-900 rounded-lg p-4">
                <h3 class="font-bold text-lg mb-3">Most Viewed</h3>
                <div class="flex space-x-2 mb-4">
                    <button @click="tab = 'day'" :class="tab === 'day' ? 'bg-emerald-600' : 'bg-gray-800'" class="px-3 py-1 text-xs rounded transition">Day</button>
                    <button @click="tab = 'week'" :class="tab === 'week' ? 'bg-emerald-600' : 'bg-gray-800'" class="px-3 py-1 text-xs rounded transition">Week</button>
                    <button @click="tab = 'month'" :class="tab === 'month' ? 'bg-emerald-600' : 'bg-gray-800'" class="px-3 py-1 text-xs rounded transition">Month</button>
                </div>
                <div class="space-y-3">
                    @foreach($mostViewed as $i => $manga)
                    <a href="{{ route('manga.detail', $manga->slug) }}" class="flex items-center space-x-3 group">
                        <span class="text-lg font-bold text-gray-600 w-6">{{ $i + 1 }}</span>
                        <img src="{{ $manga->thumbnail_url }}" class="w-10 h-14 object-cover rounded" alt="">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-300 truncate group-hover:text-white">{{ $manga->title }}</p>
                            <div class="flex items-center text-xs text-gray-500">
                                <svg class="w-3 h-3 text-yellow-500 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                {{ $manga->rating ?? 'N/A' }}
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>

            <div class="bg-gray-900 rounded-lg p-4">
                <h3 class="font-bold text-lg mb-3">New Release</h3>
                <div class="space-y-3">
                    @foreach($newManga->take(5) as $manga)
                    <a href="{{ route('manga.detail', $manga->slug) }}" class="flex items-center space-x-3 group">
                        <img src="{{ $manga->thumbnail_url }}" class="w-10 h-14 object-cover rounded" alt="">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-300 truncate group-hover:text-white">{{ $manga->title }}</p>
                            <p class="text-xs text-gray-500">{{ $manga->year }}</p>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @if($newManga->count() > 5)
    <section class="mt-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold">Latest Manga</h2>
            <a href="{{ route('manga.newest') }}" class="text-sm text-emerald-500 hover:text-emerald-400">View All</a>
        </div>
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3">
            @foreach($newManga as $manga)
            <a href="{{ route('manga.detail', $manga->slug) }}" class="group">
                <div class="relative rounded-lg overflow-hidden bg-gray-800 aspect-[2/3]">
                    <img src="{{ $manga->thumbnail_url }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" alt="">
                </div>
                <p class="text-xs text-gray-400 mt-1.5 line-clamp-2 group-hover:text-white leading-tight">{{ $manga->title }}</p>
            </a>
            @endforeach
        </div>
    </section>
    @endif
</div>
@endsection
