@extends('layouts.main')

@section('title', 'My Anime List')
@section('description', 'Track your anime — watching, completed, plan to watch, and more.')

@section('content')
@php
    // Category styling map
    $categoryStyles = [
        'watching'      => ['label' => 'Watching',       'badge' => 'badge bg-blue-500/20 text-blue-300 border border-blue-500/30',       'icon' => 'fa-play',          'color' => 'blue'],
        'completed'     => ['label' => 'Completed',      'badge' => 'badge-success',                                                       'icon' => 'fa-circle-check',  'color' => 'emerald'],
        'plan_to_watch' => ['label' => 'Plan to Watch',  'badge' => 'badge-warning',                                                       'icon' => 'fa-clock',         'color' => 'amber'],
        'on_hold'       => ['label' => 'On Hold',        'badge' => 'badge bg-orange-500/20 text-orange-300 border border-orange-500/30','icon' => 'fa-pause',         'color' => 'orange'],
        'dropped'       => ['label' => 'Dropped',        'badge' => 'badge-danger',                                                        'icon' => 'fa-circle-xmark',  'color' => 'red'],
        'favorites'     => ['label' => 'Favorites',      'badge' => 'badge-indigo',                                                        'icon' => 'fa-heart',         'color' => 'indigo'],
    ];

    // Allow controller to override these counts via $stats
    $counts = $stats ?? [];
@endphp

<div class="max-w-7xl mx-auto"
     x-data="{
        view: localStorage.getItem('list_view') || 'grid',
        search: '',
        setView(v) { this.view = v; localStorage.setItem('list_view', v); }
     }"
>

    {{-- ╔══════════════════════════════════════════╗
         ║         HEADER                           ║
         ╚══════════════════════════════════════════╝ --}}
    <div class="flex flex-wrap items-end justify-between gap-3 mb-6">

        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-white flex items-center gap-2">
                <i class="fas fa-bookmark text-amber-400"></i>
                My Anime List
            </h1>
            <p class="mt-1 text-sm text-gray-400">
                {{ number_format($favorites->total()) }} {{ Str::plural('anime', $favorites->total()) }}
                @if($activeCategory)
                    in <strong class="text-indigo-300">{{ $categories[$activeCategory] ?? $categoryStyles[$activeCategory]['label'] ?? ucfirst($activeCategory) }}</strong>
                @endif
            </p>
        </div>

        <div class="flex items-center gap-2">

            {{-- Search --}}
            <div class="relative hidden sm:block">
                <input type="search"
                       x-model="search"
                       placeholder="Search your list…"
                       class="form-input pl-9 w-56 text-sm py-1.5">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xs"></i>
            </div>

            {{-- View toggle --}}
            <div class="flex items-center bg-[#111827] border border-gray-800 rounded-lg p-1">
                <button @click="setView('grid')"
                        :class="view === 'grid' ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:text-white'"
                        class="px-2.5 py-1.5 rounded text-xs transition"
                        aria-label="Grid view">
                    <i class="fas fa-th"></i>
                </button>
                <button @click="setView('list')"
                        :class="view === 'list' ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:text-white'"
                        class="px-2.5 py-1.5 rounded text-xs transition"
                        aria-label="List view">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- ╔══════════════════════════════════════════╗
         ║         CATEGORY TABS                    ║
         ╚══════════════════════════════════════════╝ --}}
    <div class="flex gap-2 overflow-x-auto no-scrollbar pb-2 mb-6 border-b border-gray-800">

        {{-- All --}}
        {{ route('favorites.my-list') }}
           class="flex items-center gap-2 px-4 py-2 rounded-t-lg text-sm font-medium whitespace-nowrap transition border-b-2
                  {{ !$activeCategory
                      ? 'border-indigo-500 text-white bg-indigo-600/10'
                      : 'border-transparent text-gray-400 hover:text-white hover:bg-white/5' }}">
            <i class="fas fa-list"></i>
            All
            <span class="text-xs px-1.5 py-0.5 rounded bg-gray-800 text-gray-400">
                {{ $counts['all'] ?? '' }}
            </span>
        </a>

        @foreach($categories as $key => $label)
            @php $style = $categoryStyles[$key] ?? ['icon' => 'fa-folder', 'color' => 'gray']; @endphp

            {{ route('favorites.my-list', ['category' => $key]) }}
               class="flex items-center gap-2 px-4 py-2 rounded-t-lg text-sm font-medium whitespace-nowrap transition border-b-2
                      {{ $activeCategory === $key
                          ? 'border-indigo-500 text-white bg-indigo-600/10'
                          : 'border-transparent text-gray-400 hover:text-white hover:bg-white/5' }}">
                <i class="fas {{ $style['icon'] }} text-{{ $style['color'] }}-400"></i>
                {{ $label }}
                @if(isset($counts[$key]))
                    <span class="text-xs px-1.5 py-0.5 rounded bg-gray-800 text-gray-400">
                        {{ $counts[$key] }}
                    </span>
                @endif
            </a>
        @endforeach

        {{ route('favorites.my-list', ['category' => 'favorites']) }}
           class="flex items-center gap-2 px-4 py-2 rounded-t-lg text-sm font-medium whitespace-nowrap transition border-b-2
                  {{ $activeCategory === 'favorites'
                      ? 'border-indigo-500 text-white bg-indigo-600/10'
                      : 'border-transparent text-gray-400 hover:text-white hover:bg-white/5' }}">
            <i class="fas fa-heart text-pink-400"></i>
            Favorites
            @if(isset($counts['favorites']))
                <span class="text-xs px-1.5 py-0.5 rounded bg-gray-800 text-gray-400">
                    {{ $counts['favorites'] }}
                </span>
            @endif
        </a>
    </div>

    {{-- ╔══════════════════════════════════════════╗
         ║         RESULTS                          ║
         ╚══════════════════════════════════════════╝ --}}
    @if($favorites->count())

        {{-- GRID VIEW --}}
        <div x-show="view === 'grid'"
             class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 sm:gap-4">

            @foreach($favorites as $fav)
                @php
                    $key   = $fav->category ?: 'favorites';
                    $style = $categoryStyles[$key] ?? $categoryStyles['favorites'];
                @endphp

                <div x-show="search === '' || @js(strtolower($fav->anime->title)).includes(search.toLowerCase())"
                     x-cloak
                     class="group relative">

                    {{ route('anime.detail', $fav->anime->slug) }} class="anime-card block">

                        {{ $fav->anime->thumbnail_url ?? $fav->anime->poster_url }}
                             alt="{{ $fav->anime->title }}"
                             class="anime-card-img"
                             loading="lazy">

                        {{-- Category badge --}}
                        <div class="absolute top-2 left-2">
                            <span class="{{ $style['badge'] }} text-[10px] font-semibold">
                                <i class="fas {{ $style['icon'] }} mr-1"></i>
                                {{ $style['label'] }}
                            </span>
                        </div>

                        {{-- Rating --}}
                        @if($fav->anime->rating ?? null)
                            <div class="absolute top-2 right-2">
                                <span class="badge bg-black/70 text-amber-400 text-[10px]">
                                    ⭐ {{ number_format($fav->anime->rating, 1) }}
                                </span>
                            </div>
                        @endif

                        {{-- Hover overlay --}}
                        <div class="anime-card-overlay flex flex-col justify-end p-3">
                            <p class="text-white text-sm font-semibold clamp-2 mb-1">
                                {{ $fav->anime->title }}
                            </p>
                            <div class="flex items-center gap-2 text-xs text-gray-300">
                                @if($fav->anime->type)
                                    <span class="uppercase">{{ $fav->anime->type }}</span>
                                @endif
                                @if($fav->anime->episodes_count ?? null)
                                    <span>{{ $fav->anime->episodes_count }} eps</span>
                                @endif
                            </div>
                        </div>
                    </a>

                    {{-- Quick actions overlay (top-right corner — visible on hover) --}}
                    <div class="absolute top-2 right-2 mt-7 opacity-0 group-hover:opacity-100 transition z-10"
                         x-data="{ menuOpen: false }"
                         @click.outside="menuOpen = false">

                        <button @click.prevent="menuOpen = !menuOpen"
                                class="w-7 h-7 rounded-full bg-black/70 hover:bg-black text-white flex items-center justify-center transition"
                                aria-label="Quick actions">
                            <i class="fas fa-ellipsis-vertical text-xs"></i>
                        </button>

                        <div x-show="menuOpen"
                             x-cloak
                             x-transition
                             class="absolute right-0 mt-1 w-44 rounded-lg bg-[#0f111a] border border-gray-800 shadow-xl py-1 z-20">

                            @foreach($categoryStyles as $catKey => $catStyle)
                                @if($catKey !== 'favorites' && $fav->category !== $catKey)
                                    {{ route('favorites.move', ['id' => $fav->id, 'category' => $catKey]) }}
                                          method="POST" class="block">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-300 hover:bg-gray-800 hover:text-white transition">
                                            <i class="fas {{ $catStyle['icon'] }} w-3 text-{{ $catStyle['color'] }}-400"></i>
                                            Move to {{ $catStyle['label'] }}
                                        </button>
                                    </form>
                                @endif
                            @endforeach

                            <div class="border-t border-gray-800 my-1"></div>

                            {{ route('favorites.remove', $fav->id) }} method="POST"
                                  onsubmit="return confirm('Remove this anime from your list?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-xs text-red-400 hover:bg-gray-800 hover:text-red-300 transition">
                                    <i class="fas fa-trash-can w-3"></i>
                                    Remove from list
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- LIST VIEW --}}
        <div x-show="view === 'list'" x-cloak class="card divide-y divide-gray-800">

            @foreach($favorites as $fav)
                @php
                    $key   = $fav->category ?: 'favorites';
                    $style = $categoryStyles[$key] ?? $categoryStyles['favorites'];
                @endphp

                <div x-show="search === '' || @js(strtolower($fav->anime->title)).includes(search.toLowerCase())"
                     x-cloak
                     class="flex gap-3 sm:gap-4 p-3 sm:p-4 hover:bg-white/[0.03] transition group">

                    {{-- Poster --}}
                    {{ route('anime.detail', $fav->anime->slug) }} class="aspect-poster w-16 sm:w-20 rounded-md overflow-hidden bg-gray-900 shrink-0">
                        {{ $fav->anime->thumbnail_url ?? $fav->anime->poster_url }}
                             class="w-full h-full object-cover group-hover:scale-105 transition"
                             alt="{{ $fav->anime->title }}" loading="lazy">
                    </a>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        {{ route('anime.detail', $fav->anime->slug) }} class="block">
                            <p class="text-sm sm:text-base text-white font-semibold clamp-1 group-hover:text-indigo-300 transition">
                                {{ $fav->anime->title }}
                            </p>
                        </a>

                        <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-gray-400">
                            <span class="{{ $style['badge'] }} text-[10px]">
                                <i class="fas {{ $style['icon'] }} mr-1"></i>
                                {{ $style['label'] }}
                            </span>
                            @if($fav->anime->type)
                                <span class="badge-gray">{{ strtoupper($fav->anime->type) }}</span>
                            @endif
                            @if($fav->anime->episodes_count ?? null)
                                <span>{{ $fav->anime->episodes_count }} eps</span>
                            @endif
                            @if($fav->anime->rating ?? null)
                                <span class="text-amber-400">⭐ {{ number_format($fav->anime->rating, 1) }}</span>
                            @endif
                            @if($fav->created_at)
                                <span>Added {{ $fav->created_at->diffForHumans() }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-1 self-start">
                        <div class="relative"
                             x-data="{ open: false }"
                             @click.outside="open = false">

                            <button @click="open = !open"
                                    class="w-8 h-8 rounded-full bg-gray-800/50 hover:bg-gray-700 text-gray-300 flex items-center justify-center transition"
                                    aria-label="Actions">
                                <i class="fas fa-ellipsis-vertical text-xs"></i>
                            </button>

                            <div x-show="open"
                                 x-cloak
                                 x-transition
                                 class="absolute right-0 mt-1 w-48 rounded-lg bg-[#0f111a] border border-gray-800 shadow-xl py-1 z-10">

                                @foreach($categoryStyles as $catKey => $catStyle)
                                    @if($catKey !== 'favorites' && $fav->category !== $catKey)
                                        {{ route('favorites.move', ['id' => $fav->id, 'category' => $catKey]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-300 hover:bg-gray-800 hover:text-white transition">
                                                <i class="fas {{ $catStyle['icon'] }} w-3 text-{{ $catStyle['color'] }}-400"></i>
                                                Move to {{ $catStyle['label'] }}
                                            </button>
                                        </form>
                                    @endif
                                @endforeach

                                <div class="border-t border-gray-800 my-1"></div>

                                {{ route('favorites.remove', $fav->id) }} method="POST"
                                      onsubmit="return confirm('Remove from list?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-xs text-red-400 hover:bg-gray-800 transition">
                                        <i class="fas fa-trash-can w-3"></i>
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- PAGINATION --}}
        @if($favorites->hasPages())
            <div class="mt-6">
                {{ $favorites->withQueryString()->links() }}
            </div>
        @endif

    @else

        {{-- EMPTY STATE --}}
        <div class="card p-12 text-center">
            <div class="inline-flex w-20 h-20 rounded-full bg-indigo-500/15 border border-indigo-500/30 items-center justify-center mb-4">
                <i class="fas fa-bookmark text-indigo-400 text-3xl"></i>
            </div>

            <h2 class="text-lg font-semibold text-white">
                @if($activeCategory)
                    No anime in <span class="text-indigo-400">{{ $categories[$activeCategory] ?? $categoryStyles[$activeCategory]['label'] ?? ucfirst($activeCategory) }}</span>
                @else
                    Your list is empty
                @endif
            </h2>

            <p class="text-sm text-gray-400 mt-2 max-w-md mx-auto">
                @if($activeCategory)
                    Add anime to this category to keep track of your progress.
                @else
                    Start adding anime to track your watching, completed, and favorites.
                @endif
            </p>

            <div class="flex flex-wrap items-center justify-center gap-3 mt-6">
                {{ route('home') }} class="btn-primary">
                    <i class="fas fa-compass"></i> Browse Anime
                </a>
                @if($activeCategory)
                    {{ route('favorites.my-list') }} class="btn-outline">
                        View All Categories
                    </a>
                @endif
            </div>
        </div>

    @endif

</div>
@endsection