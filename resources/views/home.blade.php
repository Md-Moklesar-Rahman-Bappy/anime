@extends('layouts.main')

@section('title', 'Home')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
    @if($featured->count())
    <div
        x-data="{
            current: 0,
            autoplay: true,
            interval: null,
            progress: 0,
            total: {{ $featured->count() }},
            touchStartX: 0,
            animKey: 0,

            init() {
                this.startAutoplay();
            },

            startAutoplay() {
                if (this.interval) clearInterval(this.interval);
                this.progress = 0;
                this.interval = setInterval(() => {
                    this.progress += 1;
                    if (this.progress >= 100) {
                        this.progress = 0;
                        this.next();
                    }
                }, 50);
            },

            stopAutoplay() {
                if (this.interval) {
                    clearInterval(this.interval);
                    this.interval = null;
                }
                this.progress = 0;
            },

            next() {
                if (this.total <= 1) return;
                this.current = (this.current + 1) % this.total;
                this.animKey++;
                if (this.autoplay) this.startAutoplay();
            },

            prev() {
                if (this.total <= 1) return;
                this.current = (this.current - 1 + this.total) % this.total;
                this.animKey++;
                if (this.autoplay) this.startAutoplay();
            },

            goTo(index) {
                if (this.current === index) return;
                this.current = index;
                this.animKey++;
                if (this.autoplay) this.startAutoplay();
            },

            toggleAutoplay() {
                this.autoplay = !this.autoplay;
                if (this.autoplay) this.startAutoplay();
                else this.stopAutoplay();
            },

            handleTouchStart(e) {
                this.touchStartX = e.touches[0].clientX;
            },

            handleTouchEnd(e) {
                let diff = this.touchStartX - e.changedTouches[0].clientX;
                if (Math.abs(diff) > 50) {
                    if (diff > 0) this.next();
                    else this.prev();
                }
            }
        }"
        x-init="init()"
        @keydown.left.window="prev()"
        @keydown.right.window="next()"
        @mouseenter="stopAutoplay()"
        @mouseleave="if (autoplay) startAutoplay()"
        @touchstart="handleTouchStart($event)"
        @touchend="handleTouchEnd($event)"
        class="relative rounded-2xl overflow-hidden mb-12 min-h-[420px] sm:min-h-[500px] lg:min-h-[600px] group focus:outline-none bg-gray-900 shadow-2xl"
        tabindex="0"
        role="region"
        aria-label="Featured Anime"
    >
        @foreach($featured as $i => $anime)
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
            <img src="{{ $anime->banner_url }}" class="w-full h-full object-cover" alt="{{ $anime->title }} banner">
            <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/50 to-transparent"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-gray-950/80 via-gray-950/20 to-transparent"></div>

            <div class="absolute bottom-0 left-0 right-0 p-6 md:p-10 lg:p-14">
                <div :key="'c-' + animKey"
                     x-transition:enter="transition ease-out duration-500"
                     x-transition:enter-start="opacity-0 translate-y-8"
                     x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="flex flex-wrap items-center gap-3 mb-4">
                        <span class="px-3 py-1 bg-purple-600/80 backdrop-blur-sm rounded-full text-xs font-semibold tracking-wide uppercase">{{ $anime->type }}</span>
                        <span class="text-sm text-gray-300">{{ $anime->year }}</span>
                        <span class="flex items-center text-sm text-gray-300">
                            <svg class="w-4 h-4 text-yellow-400 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            {{ $anime->rating ?? 'N/A' }}
                        </span>
                    </div>
                    <h2 class="text-3xl md:text-5xl lg:text-6xl font-extrabold text-white mb-3 leading-tight drop-shadow-2xl">{{ $anime->title }}</h2>
                    <p class="text-gray-300 text-sm md:text-base max-w-xl line-clamp-2 mb-6 drop-shadow-lg">{{ Str::limit($anime->description, 200) }}</p>
                    <a href="{{ route('watch', $anime->slug) }}" class="inline-flex items-center bg-gradient-to-r from-purple-600 to-purple-500 hover:from-purple-500 hover:to-purple-400 text-white px-8 py-3.5 rounded-xl font-bold transition-all duration-300 shadow-lg shadow-purple-600/25 hover:shadow-xl hover:shadow-purple-600/40 hover:scale-[1.03] active:scale-95">
                        <svg class="w-5 h-5 mr-2.5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"/></svg>
                        Watch Now
                    </a>
                </div>
            </div>
        </div>
        @endforeach

        @if($featured->count() > 1)
        <div class="absolute top-0 left-0 right-0 z-30 flex gap-1 p-3">
            @foreach($featured as $i => $anime)
            <div class="flex-1 h-[3px] bg-white/15 rounded-full overflow-hidden">
                <div class="h-full bg-white/80 rounded-full transition-all duration-100 ease-linear"
                     :style="'width: ' + (current === {{ $i }} ? progress + '%' : (current > {{ $i }} ? '100%' : '0%'))"></div>
            </div>
            @endforeach
        </div>

        <button @click="prev()" class="absolute left-3 md:left-5 top-1/2 -translate-y-1/2 z-20 w-10 h-10 md:w-12 md:h-12 rounded-full bg-black/30 backdrop-blur-sm border border-white/10 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 hover:bg-purple-600/70 hover:border-purple-500/50 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-purple-500" aria-label="Previous slide">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button @click="next()" class="absolute right-3 md:right-5 top-1/2 -translate-y-1/2 z-20 w-10 h-10 md:w-12 md:h-12 rounded-full bg-black/30 backdrop-blur-sm border border-white/10 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 hover:bg-purple-600/70 hover:border-purple-500/50 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-purple-500" aria-label="Next slide">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        </button>

        <div class="absolute bottom-4 md:bottom-6 left-0 right-0 z-30 flex items-center justify-between px-6 md:px-10 lg:px-14">
            <div class="flex items-center gap-2">
                @foreach($featured as $i => $anime)
                <button @click="goTo({{ $i }})"
                        class="h-1.5 rounded-full transition-all duration-500 focus:outline-none focus:ring-2 focus:ring-purple-500"
                        :class="current === {{ $i }} ? 'w-8 bg-white' : 'w-1.5 bg-white/40 hover:bg-white/70'"
                        :aria-label="'Go to slide ' + ({{ $i }} + 1)"></button>
                @endforeach
            </div>
            <span class="text-xs text-white/40 font-mono tracking-widest" x-text="(current + 1).toString().padStart(2, '0') + ' / ' + total.toString().padStart(2, '0')"></span>
        </div>
        @endif
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <div class="lg:col-span-3 space-y-8">
            <section>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold">Latest Episodes</h2>
                    <a href="{{ route('updated') }}" class="text-sm text-purple-500 hover:text-purple-400">View All</a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    @foreach($latestEpisodes as $episode)
                    <a href="{{ route('watch', ['slug' => $episode->anime->slug, 'ep' => $episode->number]) }}" class="group">
                        <div class="relative rounded-lg overflow-hidden bg-gray-800 aspect-[2/3]">
                            <img src="{{ $episode->thumbnail_url }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" alt="">
                            <div class="absolute top-2 left-2 bg-purple-600 text-xs font-bold px-2 py-1 rounded">Ep {{ $episode->number }}</div>
                            @if($episode->has_sub)<div class="absolute top-2 right-2 bg-blue-600 text-xs px-2 py-1 rounded">SUB</div>@endif
                            @if($episode->has_dub)<div class="absolute top-8 right-2 bg-green-600 text-xs px-2 py-1 rounded">DUB</div>@endif
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition flex items-end p-3">
                                <span class="text-white text-sm font-semibold">Watch Now</span>
                            </div>
                        </div>
                        <h3 class="text-sm text-gray-300 mt-2 line-clamp-1 group-hover:text-white">{{ $episode->anime->title }}</h3>
                        <p class="text-xs text-gray-500">Episode {{ $episode->number }}</p>
                    </a>
                    @endforeach
                </div>
            </section>

            <section>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold">Trending</h2>
                    <a href="{{ route('trending') }}" class="text-sm text-purple-500 hover:text-purple-400">View All</a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    @foreach($trending as $anime)
                    <a href="{{ route('anime.detail', $anime->slug) }}" class="group">
                        <div class="relative rounded-lg overflow-hidden bg-gray-800 aspect-[2/3]">
                            <img src="{{ $anime->thumbnail_url }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" alt="">
                            <div class="absolute top-2 left-2 bg-gray-900/80 text-xs px-2 py-1 rounded">{{ $anime->type }}</div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition flex items-end p-3">
                                <span class="text-white text-sm font-semibold">View Details</span>
                            </div>
                        </div>
                        <h3 class="text-sm text-gray-300 mt-2 line-clamp-1 group-hover:text-white">{{ $anime->title }}</h3>
                        <div class="flex items-center text-xs text-gray-500 mt-1">
                            <svg class="w-3 h-3 text-yellow-500 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            {{ $anime->rating ?? 'N/A' }}
                        </div>
                    </a>
                    @endforeach
                </div>
            </section>

            <section>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold">Ongoing</h2>
                    <a href="{{ route('ongoing') }}" class="text-sm text-purple-500 hover:text-purple-400">View All</a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    @foreach($ongoing as $anime)
                    <a href="{{ route('anime.detail', $anime->slug) }}" class="group">
                        <div class="relative rounded-lg overflow-hidden bg-gray-800 aspect-[2/3]">
                            <img src="{{ $anime->thumbnail_url }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" alt="">
                            <div class="absolute top-2 left-2 bg-red-600 text-xs px-2 py-1 rounded">ONGOING</div>
                        </div>
                        <h3 class="text-sm text-gray-300 mt-2 line-clamp-1 group-hover:text-white">{{ $anime->title }}</h3>
                    </a>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <div x-data="{ tab: 'day' }" class="bg-gray-900 rounded-lg p-4">
                <h3 class="font-bold text-lg mb-3">Top Anime</h3>
                <div class="flex space-x-2 mb-4">
                    <button @click="tab = 'day'" :class="tab === 'day' ? 'bg-purple-600' : 'bg-gray-800'" class="px-3 py-1 text-xs rounded transition">Day</button>
                    <button @click="tab = 'week'" :class="tab === 'week' ? 'bg-purple-600' : 'bg-gray-800'" class="px-3 py-1 text-xs rounded transition">Week</button>
                    <button @click="tab = 'month'" :class="tab === 'month' ? 'bg-purple-600' : 'bg-gray-800'" class="px-3 py-1 text-xs rounded transition">Month</button>
                </div>
                <div class="space-y-3">
                    @foreach($trending->take(5) as $i => $anime)
                    <a href="{{ route('anime.detail', $anime->slug) }}" class="flex items-center space-x-3 group">
                        <span class="text-lg font-bold text-gray-600 w-6">{{ $i + 1 }}</span>
                        <img src="{{ $anime->thumbnail_url }}" class="w-10 h-14 object-cover rounded" alt="">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-300 truncate group-hover:text-white">{{ $anime->title }}</p>
                            <div class="flex items-center text-xs text-gray-500">
                                <svg class="w-3 h-3 text-yellow-500 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                {{ $anime->rating ?? 'N/A' }}
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>

            <div class="bg-gray-900 rounded-lg p-4">
                <h3 class="font-bold text-lg mb-3">New Release</h3>
                <div class="space-y-3">
                    @foreach($newAnime->take(5) as $anime)
                    <a href="{{ route('anime.detail', $anime->slug) }}" class="flex items-center space-x-3 group">
                        <img src="{{ $anime->thumbnail_url }}" class="w-10 h-14 object-cover rounded" alt="">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-300 truncate group-hover:text-white">{{ $anime->title }}</p>
                            <p class="text-xs text-gray-500">{{ $anime->year }}</p>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
