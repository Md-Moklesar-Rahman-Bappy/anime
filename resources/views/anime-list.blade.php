@extends('layouts.main')

@section('title', $title)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-white">
                {{ $title }}
            </h1>
            <p class="text-sm text-gray-400 mt-1">
                {{ $animeList->total() }} results
            </p>
        </div>
    </div>

    <div x-data="{ filterOpen:false }" class="flex gap-6">

        <!-- MOBILE FILTER BUTTON -->
        <button @click="filterOpen = true"
            class="lg:hidden fixed bottom-5 right-5 bg-indigo-600 w-12 h-12 rounded-full flex items-center justify-center text-white shadow-lg z-50">
            ⚙
        </button>

        <!-- OVERLAY -->
        <div x-show="filterOpen" x-cloak
             class="fixed inset-0 bg-black/60 z-40 lg:hidden"
             @click="filterOpen=false"></div>

        <!-- FILTER SIDEBAR -->
        <aside class="w-72 shrink-0 hidden lg:block"
               :class="{'!block fixed inset-0 z-50 w-full': filterOpen}">

            <div class="bg-[#111827] border border-gray-800 rounded-2xl p-5 h-full lg:h-auto overflow-y-auto">

                <div class="flex justify-between items-center mb-4">
                    <span class="text-sm font-semibold text-gray-300">Filters</span>
                    <button @click="filterOpen=false" class="lg:hidden">✕</button>
                </div>

                <form action="{{ route('filter') }}" method="GET">

                    <!-- GENRES -->
                    <div class="filter-section">
                        <div class="filter-label">Genres</div>

                        <div class="grid grid-cols-3 gap-2">
                            @foreach($genres as $genre)
                            <label class="filter-tag {{ in_array($genre->slug, (array)request('genres')) ? 'active' : '' }}">
                                <input type="checkbox" name="genres[]" value="{{ $genre->slug }}"
                                       {{ in_array($genre->slug, (array)request('genres')) ? 'checked' : '' }}
                                       class="hidden" onchange="this.form.submit()">
                                {{ $genre->name }}
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- TYPE -->
                    <div class="filter-section">
                        <div class="filter-label">Type</div>

                        <div class="flex flex-wrap gap-2">
                            @foreach(['TV','Movie','OVA','ONA'] as $type)
                            <label class="filter-tag {{ request('type') === $type ? 'active' : '' }}">
                                <input type="radio" name="type" value="{{ $type }}"
                                       {{ request('type') === $type ? 'checked' : '' }}
                                       class="hidden" onchange="this.form.submit()">
                                {{ $type }}
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- SORT -->
                    <div class="filter-section">
                        <div class="filter-label">Sort</div>

                        <select name="sort"
                            onchange="this.form.submit()"
                            class="w-full bg-[#1f2937] border border-gray-700 text-white rounded-lg p-2 text-sm">
                            <option value="">Latest</option>
                            <option value="views" @selected(request('sort')==='views')>Popular</option>
                            <option value="score" @selected(request('sort')==='score')>Score</option>
                        </select>
                    </div>

                </form>
            </div>
        </aside>

        <!-- CONTENT -->
        <div class="flex-1 min-w-0">

            <!-- GRID -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">

                @forelse($animeList as $anime)

                <a href="{{ route('anime.detail', $anime->slug) }}" class="group">

                    <div class="anime-card">

                        {{ $anime->thumbnail_url }}

                        <!-- Overlay -->
                        <div class="anime-overlay">
                            ▶ View
                        </div>

                        <!-- Meta -->
                        <span class="anime-type">{{ $anime->type }}</span>

                        @if($anime->episodes_count)
                        <span class="anime-ep">{{ $anime->episodes_count }}</span>
                        @endif

                    </div>

                    <h3 class="anime-title">{{ $anime->title }}</h3>

                    <div class="anime-score">
                        ⭐ {{ $anime->score ?? 'N/A' }}
                    </div>

                </a>

                @empty
                <div class="col-span-full text-center text-gray-500 py-12">
                    No anime found
                </div>
                @endforelse

            </div>

            <!-- PAGINATION -->
            <div class="mt-8">
                {{ $animeList->links() }}
            </div>

        </div>

    </div>
</div>

<style>
.filter-section { @apply border-b border-gray-800 pb-4 mb-4; }
.filter-label { @apply text-xs font-semibold text-gray-500 uppercase mb-3; }

.filter-tag {
    @apply px-3 py-1 text-xs rounded-lg border border-gray-700 text-gray-400 hover:text-white hover:border-indigo-500 cursor-pointer transition;
}
.filter-tag.active {
    @apply bg-indigo-600 border-indigo-500 text-white;
}

.anime-card {
    @apply relative rounded-xl overflow-hidden bg-[#111827] aspect-[2/3];
}
.anime-overlay {
    @apply absolute inset-0 flex items-center justify-center bg-black/70 opacity-0 group-hover:opacity-100 text-white text-sm transition;
}
.anime-type {
    @apply absolute top-2 left-2 bg-black/70 text-xs px-2 py-1 rounded;
}
.anime-ep {
    @apply absolute top-2 right-2 bg-indigo-600 text-xs px-2 py-1 rounded;
}
.anime-title {
    @apply text-sm text-gray-300 mt-2 truncate group-hover:text-white;
}
.anime-score {
    @apply text-xs text-gray-500 mt-1;
}
</style>

@endsection
