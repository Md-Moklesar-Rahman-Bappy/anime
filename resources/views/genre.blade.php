@extends('layouts.main')

@section('title', $genre->name . ' Anime')
@section('description', 'Browse the best ' . $genre->name . ' anime on ' . config('app.name', 'AniKoto') . '. ' . ($genre->description ?? 'Watch free in HD.'))
@section('og:title', $genre->name . ' Anime · ' . config('app.name', 'AniKoto'))
@section('og:description', 'Watch the best ' . $genre->name . ' anime free in HD.')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">

    {{-- ╔══════════════════════════════════════════╗
         ║         GENRE HERO                       ║
         ╚══════════════════════════════════════════╝ --}}
    <section class="relative overflow-hidden rounded-2xl border border-gray-800">

        {{-- Background pattern --}}
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/20 via-purple-600/10 to-pink-600/20"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_50%,rgba(99,102,241,0.15),transparent_50%)]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_80%_80%,rgba(236,72,153,0.15),transparent_50%)]"></div>

        <div class="relative p-6 sm:p-10">
            <div class="flex flex-wrap items-end justify-between gap-4">

                <div class="space-y-2">
                    <p class="text-xs uppercase tracking-wider text-indigo-300 font-semibold flex items-center gap-2">
                        <i class="fas fa-tag"></i> Genre
                    </p>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white">
                        {{ $genre->name }}
                        <span class="text-gradient">Anime</span>
                    </h1>

                    @if($genre->description ?? null)
                        <p class="text-sm text-gray-300 max-w-2xl leading-relaxed">
                            {{ $genre->description }}
                        </p>
                    @else
                        <p class="text-sm text-gray-400 max-w-2xl">
                            Discover the best {{ $genre->name }} anime — handpicked for fans like you.
                        </p>
                    @endif
                </div>

                <div class="text-right">
                    <p class="text-3xl sm:text-4xl font-bold text-white">
                        {{ number_format($animeList->total()) }}
                    </p>
                    <p class="text-xs uppercase tracking-wider text-gray-400 mt-1">
                        {{ Str::plural('title', $animeList->total()) }}
                    </p>
                </div>

            </div>
        </div>
    </section>


    {{-- ╔══════════════════════════════════════════╗
         ║         RELATED GENRES                   ║
         ╚══════════════════════════════════════════╝ --}}
    @if(isset($allGenres) && $allGenres->count())
        <section>
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs uppercase tracking-wider text-gray-500 font-semibold">
                    Explore More Genres
                </p>
            </div>

            <div class="flex gap-2 overflow-x-auto no-scrollbar pb-2">
                @foreach($allGenres->take(20) as $g)
                    {{ route('genre', $g->slug) }}-flex items-center px-4 py-1.5 rounded-full text-xs font-medium shrink-0 transition border
                              {{ $g->id === $genre->id
                                  ? 'bg-indigo-600 border-indigo-500 text-white shadow'
                                  : 'bg-gray-900 border-gray-800 text-gray-400 hover:bg-gray-800 hover:text-white hover:border-gray-700' }}">
                        {{ $g->name }}
                    </a>
                @endforeach

                #shrink-0 transition">
                    All genres →
                </a>
            </div>
        </section>
    @endif


    {{-- ╔══════════════════════════════════════════╗
         ║         SORT BAR                         ║
         ╚══════════════════════════════════════════╝ --}}
    <section class="flex flex-wrap items-center justify-between gap-3">

        <div class="flex items-center gap-2 text-sm">
            <span class="text-gray-500">Showing</span>
            <strong class="text-white">{{ $animeList->firstItem() ?? 0 }}–{{ $animeList->lastItem() ?? 0 }}</strong>
            <span class="text-gray-500">of {{ number_format($animeList->total()) }}</span>
        </div>

        {{ request()->url() }} method="GET" class="flex items-center gap-2">
            <label class="text-xs text-gray-500 hidden sm:block">Sort by:</label>

            <select name="sort" onchange="this.form.submit()" class="form-select text-sm py-1.5">
                <option value="" @selected(!request('sort'))>Latest</option>
                <option value="views" @selected(request('sort')==='views')>Most Popular</option>
                <option value="score" @selected(request('sort')==='score')>Highest Score</option>
                <option value="rating" @selected(request('sort')==='rating')>Top Rated</option>
                <option value="title" @selected(request('sort')==='title')>Title A-Z</option>
                <option value="year" @selected(request('sort')==='year')>Newest Year</option>
            </select>
        </form>
    </section>


    {{-- ╔══════════════════════════════════════════╗
         ║         ANIME GRID                       ║
         ╚══════════════════════════════════════════╝ --}}
    @forelse($animeList as $anime)
        @if($loop->first)
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 sm:gap-4">
                @foreach($animeList as $anime)
                    {{ route('anime.detail', $anime->slug) }} class="anime-card">

                        {{ $anime->thumbnail_url ?? $anime->poster_url }}
                             alt="{{ $anime->title }}"
                             class="anime-card-img"
                             loading="lazy">

                        {{-- Badges --}}
                        <div class="absolute top-2 left-2 flex flex-col gap-1">
                            @if($anime->type)
                                <span class="badge bg-black/70 text-white">{{ strtoupper($anime->type) }}</span>
                            @endif
                        </div>

                        <div class="absolute top-2 right-2 flex flex-col gap-1">
                            @if($anime->episodes_count)
                                <span class="badge-indigo">{{ $anime->episodes_count }} EP</span>
                            @endif
                        </div>

                        {{-- Hover overlay --}}
                        <div class="anime-card-overlay flex flex-col justify-end p-3">
                            <p class="text-white text-sm font-semibold clamp-2 mb-1">
                                {{ $anime->title }}
                            </p>
                            <div class="flex items-center gap-2 text-xs text-gray-300">
                                @if($anime->score ?? $anime->rating)
                                    <span class="text-amber-400">⭐ {{ number_format($anime->score ?? $anime->rating, 1) }}</span>
                                @endif
                                @if($anime->year)
                                    <span>{{ $anime->year }}</span>
                                @endif
                                @if($anime->status)
                                    <span class="capitalize">{{ $anime->status }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
        @break
    @empty
        {{-- EMPTY STATE --}}
        <div class="card p-12 text-center">
            <div class="inline-flex w-16 h-16 rounded-full bg-gray-800/80 items-center justify-center mb-3">
                <i class="fas fa-folder-open text-gray-600 text-2xl"></i>
            </div>
            <p class="text-white font-medium">No {{ $genre->name }} anime found yet</p>
            <p class="text-sm text-gray-500 mt-1">Check back soon — our library is constantly growing.</p>

            <div class="flex flex-wrap items-center justify-center gap-2 mt-5">
                {{ route('home') }} class="btn-primary btn-sm">
                    <i class="fas fa-home"></i> Back to Home
                </a>
                {{ route('filter') }} class="btn-outline btn-sm">
                    <i class="fas fa-th"></i> Browse All
                </a>
            </div>
        </div>
    @endforelse


    {{-- ╔══════════════════════════════════════════╗
         ║         PAGINATION                       ║
         ╚══════════════════════════════════════════╝ --}}
    @if($animeList->hasPages())
        <div>
            {{ $animeList->withQueryString()->links() }}
        </div>
    @endif


    {{-- ╔══════════════════════════════════════════╗
         ║         RELATED GENRE CTA                ║
         ╚══════════════════════════════════════════╝ --}}
    @if($animeList->total() > 0)
        <section class="relative overflow-hidden rounded-2xl border border-indigo-500/30 bg-gradient-to-br from-indigo-600/20 via-purple-600/10 to-pink-600/20 p-6 sm:p-8 text-center">
            <h2 class="text-lg sm:text-xl font-bold text-white mb-2">
                Looking for something different?
            </h2>
            <p class="text-sm text-gray-300 mb-4">
                Explore our full anime library or filter by your favorite genres.
            </p>
            <div class="flex flex-wrap items-center justify-center gap-3">
                {{ route('filter') }} class="btn-primary">
                    <i class="fas fa-compass"></i> Browse All Anime
                </a>
                {{ route('trending') }} class="btn-outline">
                    <i class="fas fa-fire"></i> Trending Now
                </a>
            </div>
        </section>
    @endif

</div>
@endsection