@extends('layouts.main')

@section('title', $title)
@section('description', $title . ' — Browse manga on ' . config('app.name', 'AniKoto'))

@section('content')
<div
    class="max-w-7xl mx-auto"
    x-data="{
        filterOpen: false,
        view: localStorage.getItem('manga_view') || 'grid',
        setView(v) { this.view = v; localStorage.setItem('manga_view', v); }
    }"
>

    {{-- ╔══════════════════════════════════════════╗
         ║         PAGE HEADER                      ║
         ╚══════════════════════════════════════════╝ --}}
    <div class="flex flex-wrap items-end justify-between gap-3 mb-6">

        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-white">{{ $title }}</h1>
            <p class="mt-1 text-sm text-gray-400">
                {{ number_format($mangaList->total()) }} {{ Str::plural('result', $mangaList->total()) }}
            </p>
        </div>

        <div class="flex items-center gap-2">

            {{-- View toggle --}}
            <div class="flex items-center bg-[#111827] border border-gray-800 rounded-lg p-1">
                <button @click="setView('grid')"
                        :class="view === 'grid' ? 'bg-emerald-600 text-white' : 'text-gray-400 hover:text-white'"
                        class="px-2.5 py-1.5 rounded text-xs transition"
                        aria-label="Grid view">
                    <i class="fas fa-th"></i>
                </button>
                <button @click="setView('list')"
                        :class="view === 'list' ? 'bg-emerald-600 text-white' : 'text-gray-400 hover:text-white'"
                        class="px-2.5 py-1.5 rounded text-xs transition"
                        aria-label="List view">
                    <i class="fas fa-list"></i>
                </button>
            </div>

            {{-- Mobile filter button --}}
            @php
                $activeCount = collect([
                    is_array(request('genres')) ? count(request('genres')) : 0,
                    request('type') ? 1 : 0,
                    request('status') ? 1 : 0,
                    request('year') ? 1 : 0,
                    request('q') ? 1 : 0,
                ])->sum();
            @endphp

            <button @click="filterOpen = true" class="lg:hidden btn-outline btn-sm">
                <i class="fas fa-sliders-h"></i>
                <span>Filters</span>
                @if($activeCount)
                    <span class="ml-1 px-1.5 py-0.5 rounded bg-emerald-500 text-white text-[10px] font-bold">
                        {{ $activeCount }}
                    </span>
                @endif
            </button>
        </div>
    </div>

    {{-- ╔══════════════════════════════════════════╗
         ║         ACTIVE FILTER CHIPS              ║
         ╚══════════════════════════════════════════╝ --}}
    @if($activeCount > 0)
        <div class="flex flex-wrap items-center gap-2 mb-5 pb-4 border-b border-gray-800">
            <span class="text-xs text-gray-500">Active filters:</span>

            @foreach((array) request('genres', []) as $slug)
                @php $genre = ($genres ?? collect())->firstWhere('slug', $slug); @endphp
                @if($genre)
                    {{ url()->current() }}?{{ http_build_query(array_merge(request()->except('genres', 'page'), ['genres' => array_diff((array) request('genres'), [$slug])])) }} class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs hover:bg-emerald-500/25 transition">
                        {{ $genre->name }} <i class="fas fa-times text-[10px]"></i>
                    </a>
                @endif
            @endforeach

            @if(request('type'))
                {{ url()->current() }}?{{ http_build_query(request()->except('type', 'page')) }} class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-pink-500/15 border border-pink-500/30 text-pink-300 text-xs hover:bg-pink-500/25 transition">
                    Type: {{ request('type') }} <i class="fas fa-times text-[10px]"></i>
                </a>
            @endif

            @if(request('status'))
                {{ url()->current() }}?{{ http_build_query(request()->except('status', 'page')) }} class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-indigo-500/15 border border-indigo-500/30 text-indigo-300 text-xs hover:bg-indigo-500/25 transition">
                    Status: {{ ucfirst(request('status')) }} <i class="fas fa-times text-[10px]"></i>
                </a>
            @endif

            @if(request('year'))
                {{ url()->current() }}?{{ http_build_query(request()->except('year', 'page')) }} class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-500/15 border border-amber-500/30 text-amber-300 text-xs hover:bg-amber-500/25 transition">
                    Year: {{ request('year') }} <i class="fas fa-times text-[10px]"></i>
                </a>
            @endif

            @if(request('q'))
                {{ url()->current() }}?{{ http_build_query(request()->except('q', 'page')) }} class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-sky-500/15 border border-sky-500/30 text-sky-300 text-xs hover:bg-sky-500/25 transition">
                    Search: "{{ request('q') }}" <i class="fas fa-times text-[10px]"></i>
                </a>
            @endif

            {{ route('manga.index') }} class="text-xs text-red-400 hover:text-red-300 ml-2 transition">
                Clear all
            </a>
        </div>
    @endif

    {{-- ╔══════════════════════════════════════════╗
         ║         LAYOUT GRID                      ║
         ╚══════════════════════════════════════════╝ --}}
    <div class="flex gap-6">

        {{-- ─────── MOBILE OVERLAY ─────── --}}
        <div x-show="filterOpen"
             x-cloak
             x-transition.opacity
             @click="filterOpen = false"
             class="lg:hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-40"></div>

        {{-- ─────── FILTERS SIDEBAR ─────── --}}
        <aside
            class="w-72 shrink-0 lg:block"
            :class="filterOpen
                ? 'fixed inset-y-0 left-0 z-50 overflow-y-auto w-72 lg:relative lg:inset-auto'
                : 'hidden'"
        >
            <div class="card p-5 lg:sticky lg:top-20">

                {{-- Header --}}
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-semibold text-white flex items-center gap-2">
                        <i class="fas fa-sliders-h text-emerald-400"></i> Filters
                    </h2>
                    <button @click="filterOpen = false" class="lg:hidden text-gray-400 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{ route('manga.index') }}" method="GET" class="space-y-5">

                    {{-- SEARCH --}}
                    <div>
                        <label class="form-label">Search</label>
                        <div class="relative">
                            <input type="search"
                                   name="q"
                                   value="{{ request('q') }}"
                                   placeholder="Manga title…"
                                   class="form-input pl-9">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                        </div>
                    </div>

                    {{-- SORT --}}
                    <div>
                        <label class="form-label">Sort By</label>
                        <select name="sort" class="form-select">
                            <option value="" @selected(!request('sort'))>Latest</option>
                            <option value="views" @selected(request('sort')==='views')>Most Read</option>
                            <option value="rating" @selected(request('sort')==='rating')>Highest Rated</option>
                            <option value="chapters" @selected(request('sort')==='chapters')>Most Chapters</option>
                            <option value="title" @selected(request('sort')==='title')>Title A-Z</option>
                            <option value="oldest" @selected(request('sort')==='oldest')>Oldest First</option>
                        </select>
                    </div>

                    {{-- TYPE --}}
                    <div>
                        <p class="form-label">Type</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach(['Manga', 'Manhwa', 'Manhua', 'One-shot', 'Doujinshi'] as $type)
                                <label class="cursor-pointer">
                                    <input type="radio"
                                           name="type"
                                           value="{{ $type }}"
                                           {{ request('type') === $type ? 'checked' : '' }}
                                           class="peer sr-only">
                                    <span class="block px-3 py-1 text-xs rounded-md border border-gray-700 text-gray-400
                                                 peer-checked:bg-emerald-600 peer-checked:border-emerald-500 peer-checked:text-white
                                                 hover:border-gray-600 transition">
                                        {{ $type }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- STATUS --}}
                    <div>
                        <p class="form-label">Status</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach([
                                'ongoing'   => 'Ongoing',
                                'completed' => 'Completed',
                                'hiatus'    => 'Hiatus',
                                'cancelled' => 'Cancelled',
                            ] as $key => $label)
                                <label class="cursor-pointer">
                                    <input type="radio"
                                           name="status"
                                           value="{{ $key }}"
                                           {{ request('status') === $key ? 'checked' : '' }}
                                           class="peer sr-only">
                                    <span class="block px-3 py-1 text-xs rounded-md border border-gray-700 text-gray-400
                                                 peer-checked:bg-indigo-600 peer-checked:border-indigo-500 peer-checked:text-white
                                                 hover:border-gray-600 transition">
                                        {{ $label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- YEAR --}}
                    <div>
                        <label class="form-label">Year</label>
                        <select name="year" class="form-select">
                            <option value="">Any year</option>
                            @for($y = (int) date('Y') + 1; $y >= 1950; $y--)
                                <option value="{{ $y }}" @selected(request('year') == $y)>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- GENRES --}}
                    @if(isset($genres) && $genres->count())
                        <div>
                            <p class="form-label">Genres</p>
                            <div class="grid grid-cols-2 gap-1.5 max-h-64 overflow-y-auto pr-1">
                                @foreach($genres as $genre)
                                    <label class="cursor-pointer">
                                        <input type="checkbox"
                                               name="genres[]"
                                               value="{{ $genre->slug }}"
                                               {{ in_array($genre->slug, (array) request('genres')) ? 'checked' : '' }}
                                               class="peer sr-only">
                                        <span class="block px-2 py-1 text-xs rounded-md border border-gray-700 text-gray-400 text-center truncate
                                                     peer-checked:bg-emerald-600 peer-checked:border-emerald-500 peer-checked:text-white
                                                     hover:border-gray-600 transition">
                                            {{ $genre->name }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- ACTIONS --}}
                    <div class="space-y-2 pt-2 border-t border-gray-800">
                        <button type="submit" class="btn-success w-full">
                            <i class="fas fa-filter"></i>
                            Apply Filters
                        </button>

                        @if($activeCount > 0)
                            {{ route('manga.index') }} class="btn-cancel w-full">
                                <i class="fas fa-times"></i>
                                Reset
                            </a>
                        @endif
                    </div>

                </form>
            </div>
        </aside>

        {{-- ─────── RESULTS ─────── --}}
        <div class="flex-1 min-w-0">

            @forelse($mangaList as $manga)
                @if($loop->first)
                    @php
                        $typeColors = [
                            'Manga'     => 'badge-success',
                            'Manhwa'    => 'badge-warning',
                            'Manhua'    => 'badge-sky',
                            'One-shot'  => 'badge-pink',
                            'Doujinshi' => 'badge-purple',
                        ];
                    @endphp

                    {{-- GRID VIEW --}}
                    <div x-show="view === 'grid'" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4">
                        @foreach($mangaList as $manga)
                            {{ route('manga.detail', $manga->slug) }} class="anime-card">

                                {{ $manga->thumbnail_url ?? 'https://via.placeholder.com/200x280?text=No+Image' }}
                                     alt="{{ $manga->title }}"
                                     class="w-full aspect-manga object-cover transition duration-500 group-hover:scale-105"
                                     loading="lazy">

                                {{-- Badges --}}
                                <div class="absolute top-2 left-2 flex flex-col gap-1">
                                    @if($manga->type)
                                        <span class="{{ $typeColors[$manga->type] ?? 'badge-success' }}">
                                            {{ strtoupper($manga->type) }}
                                        </span>
                                    @endif
                                </div>

                                <div class="absolute top-2 right-2 flex flex-col gap-1">
                                    @if($manga->chapters_count ?? null)
                                        <span class="badge bg-black/70 text-white">Ch. {{ $manga->chapters_count }}</span>
                                    @endif
                                </div>

                                {{-- Hover overlay --}}
                                <div class="anime-card-overlay flex flex-col justify-end p-3">
                                    <p class="text-white text-sm font-semibold clamp-2 mb-1">
                                        {{ $manga->title }}
                                    </p>
                                    <div class="flex items-center gap-2 text-xs text-gray-300">
                                        @if($manga->rating ?? null)
                                            <span class="text-amber-400">⭐ {{ number_format($manga->rating, 1) }}</span>
                                        @endif
                                        @if($manga->year)
                                            <span>{{ $manga->year }}</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    {{-- LIST VIEW --}}
                    <div x-show="view === 'list'" x-cloak class="card divide-y divide-gray-800">
                        @foreach($mangaList as $manga)
                            {{ route('manga.detail', $manga->slug) }}
                               class="flex gap-3 sm:gap-4 p-3 sm:p-4 hover:bg-white/[0.03] transition group">

                                {{-- Cover --}}
                                <div class="aspect-manga w-16 sm:w-20 rounded-md overflow-hidden bg-gray-900 shrink-0">
                                    {{ $manga->thumbnail_url ?? 'https://via.placeholder.com/200x280?text=No+Image' }}
                                         class="w-full h-full object-cover group-hover:scale-105 transition"
                                         alt="{{ $manga->title }}" loading="lazy">
                                </div>

                                {{-- Info --}}
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm sm:text-base text-white font-semibold clamp-1 group-hover:text-emerald-300 transition">
                                        {{ $manga->title }}
                                    </p>

                                    <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-gray-400">
                                        @if($manga->type)
                                            <span class="{{ $typeColors[$manga->type] ?? 'badge-success' }}">
                                                {{ strtoupper($manga->type) }}
                                            </span>
                                        @endif
                                        @if($manga->status)
                                            <span class="badge-gray">{{ ucfirst($manga->status) }}</span>
                                        @endif
                                        @if($manga->year)
                                            <span>{{ $manga->year }}</span>
                                        @endif
                                        @if($manga->chapters_count ?? null)
                                            <span>{{ $manga->chapters_count }} chapters</span>
                                        @endif
                                        @if($manga->rating ?? null)
                                            <span class="text-amber-400">⭐ {{ number_format($manga->rating, 1) }}</span>
                                        @endif
                                    </div>

                                    {{-- Genres --}}
                                    @if($manga->genres->count())
                                        <div class="flex flex-wrap gap-1 mt-2">
                                            @foreach($manga->genres->take(3) as $genre)
                                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-800 text-gray-400">
                                                    {{ $genre->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if($manga->description ?? null)
                                        <p class="text-xs text-gray-500 clamp-2 mt-1.5 leading-relaxed hidden sm:block">
                                            {{ $manga->description }}
                                        </p>
                                    @endif
                                </div>

                                <i class="fas fa-chevron-right text-gray-600 text-xs self-center hidden sm:block group-hover:text-emerald-400 transition"></i>
                            </a>
                        @endforeach
                    </div>
                @endif
                @break
            @empty
                {{-- EMPTY STATE --}}
                <div class="card p-12 text-center">
                    <div class="inline-flex w-16 h-16 rounded-full bg-gray-800/80 items-center justify-center mb-3">
                        <i class="fas fa-book text-gray-600 text-2xl"></i>
                    </div>
                    <p class="text-white font-medium">No manga found</p>
                    <p class="text-sm text-gray-500 mt-1">Try adjusting your filters or search again.</p>

                    @if($activeCount > 0)
                        {{ route('manga.index') }} class="btn-success btn-sm mt-4">
                            <i class="fas fa-times"></i> Reset Filters
                        </a>
                    @endif
                </div>
            @endforelse

            {{-- PAGINATION --}}
            @if($mangaList->hasPages())
                <div class="mt-6">
                    {{ $mangaList->withQueryString()->links() }}
                </div>
            @endif

        </div>
    </div>
</div>
@endsection