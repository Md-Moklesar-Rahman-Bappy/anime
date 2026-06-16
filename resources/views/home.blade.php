@extends('layouts.main')

@section('title', 'Home')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    {{-- Featured Hero Carousel --}}
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
                    this.progress += 0.5;
                    if (this.progress >= 100) { this.progress = 0; this.next(); }
                }, 50);
            },

            stopAutoplay() {
                if (this.interval) { clearInterval(this.interval); this.interval = null; }
                this.progress = 0;
            },

            next() { if (this.total <= 1) return; this.current = (this.current + 1) % this.total; if (this.autoplay) this.startAutoplay(); },
            prev() { if (this.total <= 1) return; this.current = (this.current - 1 + this.total) % this.total; if (this.autoplay) this.startAutoplay(); },
            goTo(index) { if (this.current === index) return; this.current = index; if (this.autoplay) this.startAutoplay(); },

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
        tabindex="0" role="region" aria-label="Featured Anime"
    >
        {{-- Progress bars --}}
        @if($featured->count() > 1)
        <div class="absolute top-0 left-0 right-0 z-30 flex space-x-0.5 p-2">
            @foreach($featured as $i => $anime)
            <div class="flex-1 h-0.5 bg-white/15 rounded-full overflow-hidden">
                <div class="h-full bg-purple-500 rounded-full transition-all duration-75 ease-linear"
                     :style="'width: ' + (current === {{ $i }} ? progress + '%' : (current > {{ $i }} ? '100%' : '0%'))"></div>
            </div>
            @endforeach
        </div>
        @endif

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
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('{{ $anime->banner_url ?? asset('fallback.jpg') }}')"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0f] via-[#0a0a0f]/70 to-transparent"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-[#0a0a0f]/60 to-transparent"></div>
            <div class="absolute bottom-0 left-0 right-0 p-6 md:p-10">
                <h2 class="text-2xl md:text-4xl font-bold text-white mb-2 drop-shadow-lg">{{ $anime->title }}</h2>
                <div class="flex flex-wrap items-center gap-2 text-sm text-gray-300 mb-3">
                    @if($anime->age_rating)
                        <span class="px-2 py-0.5 bg-white/10 rounded text-xs font-medium">{{ $anime->age_rating }}</span>
                    @endif
                    @if($anime->type)
                        <span class="px-2 py-0.5 bg-purple-600/80 rounded text-xs font-semibold">{{ $anime->type }}</span>
                    @endif
                    <span class="text-xs text-gray-400">{{ $anime->year ?? '' }}</span>
                    @if($anime->rating)
                        <span class="text-xs text-gray-400 flex items-center gap-1">
                            <svg class="w-3 h-3 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            {{ $anime->rating }}
                        </span>
                    @endif
                </div>
                <p class="text-gray-400 text-sm max-w-xl line-clamp-2 mb-4 drop-shadow-md">{{ Str::limit($anime->description, 200) }}</p>
                <a href="{{ route('watch', $anime->slug) }}" class="inline-flex items-center bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2.5 rounded-lg font-semibold transition transform hover:scale-105 active:scale-95 shadow-lg shadow-purple-600/30 text-sm">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"/></svg>
                    Play now
                </a>
            </div>
            @if($anime->episodes_count)
                <div class="absolute top-3 right-3 z-20 flex gap-1.5">
                    <span class="bg-purple-600 text-xs font-bold px-2 py-0.5 rounded">{{ $anime->episodes_count }} {{ Str::plural('Episode', $anime->episodes_count) }}</span>
                    <span class="bg-green-600/80 text-xs px-2 py-0.5 rounded">HD</span>
                </div>
            @endif
        </div>
        @endforeach

        @if($featured->count() > 1)
        <button @click="prev()" class="absolute left-3 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-black/50 hover:bg-purple-600 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 focus:outline-none" aria-label="Previous">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button @click="next()" class="absolute right-3 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-black/50 hover:bg-purple-600 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 focus:outline-none" aria-label="Next">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        </button>

        <div class="absolute bottom-4 left-0 right-0 z-30 flex items-center justify-center space-x-4">
            <div class="flex items-center space-x-2">
                @foreach($featured as $i => $anime)
                <button @click="goTo({{ $i }})" class="w-2 h-2 rounded-full transition-all duration-300 focus:outline-none"
                        :class="current === {{ $i }} ? 'bg-purple-600 w-5 rounded-full' : 'bg-white/40 hover:bg-white/70'"
                        :aria-label="'Go to slide ' + ({{ $i }} + 1)"></button>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- Main Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

        {{-- Left Column --}}
        <div class="lg:col-span-3 space-y-10">

            {{-- Latest Episode --}}
            <section>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="section-title">Latest Episode</h2>
                    <a href="{{ route('updated') }}" class="text-sm text-purple-500 hover:text-purple-400 font-medium">View all</a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    @foreach($latestEpisodes as $episode)
                    <a href="{{ route('watch', ['slug' => $episode->anime->slug, 'ep' => $episode->number]) }}" class="group">
                        <div class="anime-card">
                            <img src="{{ $episode->thumbnail_url }}" class="anime-img">

                            <div class="absolute top-2 left-2 bg-purple-600 text-xs font-bold px-2 py-0.5 rounded">Ep {{ $episode->number }}</div>
                            @if($episode->has_sub)<div class="absolute top-2 right-2 bg-blue-600 text-[10px] px-1.5 py-0.5 rounded font-medium">SUB</div>@endif
                            @if($episode->has_dub)<div class="absolute top-8 right-2 bg-green-600 text-[10px] px-1.5 py-0.5 rounded font-medium">DUB</div>@endif
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition flex items-end p-3">
                                <span class="text-white text-sm font-semibold">Watch Now</span>
                            </div>
                        </div>
                        <h3 class="text-sm text-gray-300 mt-2 line-clamp-1 group-hover:text-white">{{ $episode->anime->title }}</h3>
                        <p class="text-xs text-gray-600">Episode {{ $episode->number }}</p>
                    </a>
                    @endforeach
                </div>
            </section>

            {{-- Upcoming Anime --}}
            @if($upcoming->count())
            <section>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="section-title">Upcoming Anime</h2>
                    <a href="{{ route('filter') }}?status=not-yet-aired" class="text-sm text-purple-500 hover:text-purple-400 font-medium">View more</a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    @foreach($upcoming->take(8) as $anime)
                    <a href="{{ route('anime.detail', $anime->slug) }}" class="group">
                        <div class="relative rounded-lg overflow-hidden bg-[#111827] aspect-[2/3]">
                            <img src="{{ $anime->thumbnail_url }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" alt="">
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-3">
                                <span class="text-xs text-gray-400">Not Yet Aired</span>
                            </div>
                        </div>
                        <h3 class="text-sm text-gray-300 mt-2 line-clamp-1 group-hover:text-white">{{ $anime->title }}</h3>
                    </a>
                    @endforeach
                </div>
            </section>
            @endif

            {{-- New Release / Newly Added / Just Completed --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- New Release --}}
                <section>
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-bold text-white uppercase tracking-wider">New Release</h2>
                        <a href="{{ route('newest') }}" class="text-xs text-purple-500 hover:text-purple-400">View more</a>
                    </div>
                    <div class="space-y-2">
                        @foreach($newAnime->take(5) as $anime)
                        <a href="{{ route('anime.detail', $anime->slug) }}" class="flex items-start gap-2.5 group">
                            <img src="{{ $anime->thumbnail_url }}" class="w-10 h-14 object-cover rounded flex-shrink-0" alt="">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-gray-300 truncate group-hover:text-white leading-tight">{{ $anime->title }}</p>
                                <p class="text-xs text-gray-600 mt-0.5">{{ $anime->episodes_count ?? '?' }} {{ $anime->type ?? '' }} {{ $anime->year ? '| '.$anime->year : '' }}</p>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </section>

                {{-- Newly Added --}}
                <section>
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-bold text-white uppercase tracking-wider">Newly Added</h2>
                        <a href="{{ route('ongoing') }}" class="text-xs text-purple-500 hover:text-purple-400">View more</a>
                    </div>
                    <div class="space-y-2">
                        @foreach($newlyAdded as $anime)
                        <a href="{{ route('anime.detail', $anime->slug) }}" class="flex items-start gap-2.5 group">
                            <img src="{{ $anime->thumbnail_url }}" class="w-10 h-14 object-cover rounded flex-shrink-0" alt="">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-gray-300 truncate group-hover:text-white leading-tight">{{ $anime->title }}</p>
                                <p class="text-xs text-gray-600 mt-0.5">{{ $anime->episodes_count ?? '?' }} {{ $anime->type ?? '' }} {{ $anime->year ? '| '.$anime->year : '' }}</p>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </section>

                {{-- Just Completed --}}
                <section>
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-bold text-white uppercase tracking-wider">Just Completed</h2>
                        <a href="{{ route('filter') }}?status=completed" class="text-xs text-purple-500 hover:text-purple-400">View more</a>
                    </div>
                    <div class="space-y-2">
                        @foreach($justCompleted as $anime)
                        <a href="{{ route('anime.detail', $anime->slug) }}" class="flex items-start gap-2.5 group">
                            <img src="{{ $anime->thumbnail_url }}" class="w-10 h-14 object-cover rounded flex-shrink-0" alt="">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-gray-300 truncate group-hover:text-white leading-tight">{{ $anime->title }}</p>
                                <p class="text-xs text-gray-600 mt-0.5">{{ $anime->episodes_count ?? '?' }} {{ $anime->type ?? '' }} {{ $anime->year ? '| '.$anime->year : '' }}</p>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </section>
            </div>
        </div>

        {{-- Right Sidebar --}}
        <div class="space-y-6">
            {{-- Top Anime --}}
            <div x-data="{ tab: 'day' }" class="bg-[#141424] rounded-xl p-4 border border-gray-800/40">
                <h3 class="font-bold text-white mb-3 text-sm uppercase tracking-wider">Top anime</h3>
                <div class="flex space-x-1 mb-4">
                    <button @click="tab = 'day'" :class="tab === 'day' ? 'bg-purple-600 text-white' : 'text-gray-400 hover:text-white bg-white/5'" class="px-3 py-1 text-xs rounded-lg transition font-medium">Day</button>
                    <button @click="tab = 'week'" :class="tab === 'week' ? 'bg-purple-600 text-white' : 'text-gray-400 hover:text-white bg-white/5'" class="px-3 py-1 text-xs rounded-lg transition font-medium">Week</button>
                    <button @click="tab = 'month'" :class="tab === 'month' ? 'bg-purple-600 text-white' : 'text-gray-400 hover:text-white bg-white/5'" class="px-3 py-1 text-xs rounded-lg transition font-medium">Month</button>
                </div>
                <div class="space-y-2.5">
                    @foreach($topAnime as $i => $anime)
                    <a href="{{ route('anime.detail', $anime->slug) }}" class="flex items-center gap-3 group">
                        <span class="text-base font-bold text-gray-600 w-5 text-right {{ $i < 3 ? 'text-purple-500' : '' }}">{{ $i + 1 }}</span>
                        <img src="{{ $anime->thumbnail_url }}" class="w-9 h-12 object-cover rounded flex-shrink-0" alt="">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-gray-300 truncate group-hover:text-white leading-tight">{{ $anime->title }}</p>
                            <div class="flex items-center text-xs text-gray-600 mt-0.5">
                                <svg class="w-3 h-3 text-yellow-500 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                {{ $anime->rating ?? 'N/A' }}
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<style>
.section-title {
    @apply text-lg font-semibold text-white mb-4;
}

.anime-card {
    @apply relative rounded-xl overflow-hidden bg-[#111827] aspect-[2/3];
}

.anime-img {
    @apply w-full h-full object-cover group-hover:scale-105 transition duration-300;
}

.anime-overlay {
    @apply absolute inset-0 flex items-end p-3 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition;
}

.anime-title {
    @apply text-sm text-gray-300 mt-2 truncate group-hover:text-white;
}

.anime-meta {
    @apply text-xs text-gray-500 mt-1;
}
</style>