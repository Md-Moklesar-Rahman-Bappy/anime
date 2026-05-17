@extends('layouts.main')

@section('title', $title)

@push('styles')
<style>
.filter-tag {
    @apply px-3 py-1.5 text-xs rounded-lg border transition cursor-pointer select-none;
}
.filter-tag.active {
    @apply bg-purple-600 border-purple-500 text-white;
}
.filter-tag:not(.active) {
    @apply border-gray-700 text-gray-400 hover:border-purple-500 hover:text-white;
}
.filter-section {
    @apply border-b border-gray-800 pb-4 mb-4;
}
.filter-section:last-child {
    @apply border-b-0 pb-0 mb-0;
}
.filter-label {
    @apply text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3;
}
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">{{ $title }}</h1>
        <span class="text-sm text-gray-500">{{ $mangaList->total() }} Items</span>
    </div>

    <div class="flex gap-6">
        <div class="hidden lg:block w-72 shrink-0">
            <form action="{{ route('manga.filter') }}" method="GET" id="filter-form">
                @if(request('q'))
                    <input type="hidden" name="q" value="{{ request('q') }}">
                @endif

                <div class="bg-gray-900 rounded-xl p-5 border border-gray-800 sticky top-24">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm font-semibold text-gray-300">Quick Access</span>
                        <a href="{{ route('manga.filter') }}" class="text-xs text-purple-400 hover:text-purple-300">Clear</a>
                    </div>

                    @if(isset($genres))
                    <div class="filter-section">
                        <div class="filter-label">Genre</div>
                        <div class="grid grid-cols-2 gap-1.5">
                            @php $selectedGenres = (array) request('genres', []); @endphp
                            @foreach($genres as $genre)
                                <label class="filter-tag text-center text-[10px] leading-tight px-1 py-1 {{ in_array($genre->slug, $selectedGenres) ? 'active' : '' }}">
                                    <input type="checkbox" name="genres[]" value="{{ $genre->slug }}"
                                        {{ in_array($genre->slug, $selectedGenres) ? 'checked' : '' }}
                                        class="hidden" onchange="this.form.submit()">
                                    {{ $genre->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="filter-section">
                        <div class="filter-label">Type</div>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['Manga', 'Manhwa', 'Manhua', 'One-shot', 'Doujinshi'] as $t)
                                <label class="filter-tag {{ request('type') === $t ? 'active' : '' }}">
                                    <input type="radio" name="type" value="{{ $t }}"
                                        {{ request('type') === $t ? 'checked' : '' }}
                                        class="hidden" onchange="this.form.submit()">
                                    {{ $t }}
                                </label>
                            @endforeach
                            @if(request('type'))
                                <label class="filter-tag border-gray-600 text-gray-500">
                                    <input type="radio" name="type" value="" class="hidden" onchange="this.form.submit()">
                                    All
                                </label>
                            @endif
                        </div>
                    </div>

                    <div class="filter-section">
                        <div class="filter-label">Status</div>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['Ongoing', 'Completed', 'Hiatus', 'Cancelled'] as $st)
                                <label class="filter-tag {{ request('status') === $st ? 'active' : '' }}">
                                    <input type="radio" name="status" value="{{ $st }}"
                                        {{ request('status') === $st ? 'checked' : '' }}
                                        class="hidden" onchange="this.form.submit()">
                                    {{ $st }}
                                </label>
                            @endforeach
                            @if(request('status'))
                                <label class="filter-tag border-gray-600 text-gray-500">
                                    <input type="radio" name="status" value="" class="hidden" onchange="this.form.submit()">
                                    All
                                </label>
                            @endif
                        </div>
                    </div>

                    <div class="filter-section">
                        <div class="filter-label">Year</div>
                        <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto">
                            @php $currentYear = date('Y'); @endphp
                            @foreach(range($currentYear, 2000) as $y)
                                <label class="filter-tag {{ request('year') == $y ? 'active' : '' }}">
                                    <input type="radio" name="year" value="{{ $y }}"
                                        {{ request('year') == $y ? 'checked' : '' }}
                                        class="hidden" onchange="this.form.submit()">
                                    {{ $y }}
                                </label>
                            @endforeach
                            @foreach([1990, 1980, 1970] as $decade)
                                <label class="filter-tag {{ request('year') == $decade.'0s' ? 'active' : '' }}">
                                    <input type="radio" name="year" value="{{ $decade }}s"
                                        {{ request('year') == $decade.'s' ? 'checked' : '' }}
                                        class="hidden" onchange="this.form.submit()">
                                    {{ $decade }}s
                                </label>
                            @endforeach
                            @if(request('year'))
                                <label class="filter-tag border-gray-600 text-gray-500">
                                    <input type="radio" name="year" value="" class="hidden" onchange="this.form.submit()">
                                    All
                                </label>
                            @endif
                        </div>
                    </div>

                    <div class="filter-section">
                        <div class="filter-label">Sort by</div>
                        <select name="sort" onchange="this.form.submit()"
                            class="w-full bg-gray-800 text-sm text-gray-300 rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">Recently added</option>
                            <option value="updated" {{ request('sort') === 'updated' ? 'selected' : '' }}>Recently updated</option>
                            <option value="views" {{ request('sort') === 'views' ? 'selected' : '' }}>Most viewed</option>
                            <option value="score" {{ request('sort') === 'score' ? 'selected' : '' }}>Score</option>
                            <option value="rating" {{ request('sort') === 'rating' ? 'selected' : '' }}>Rating</option>
                            <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Name A-Z</option>
                            <option value="release" {{ request('sort') === 'release' ? 'selected' : '' }}>Release year</option>
                            <option value="chapters" {{ request('sort') === 'chapters' ? 'selected' : '' }}>Chapters</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full mt-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold py-2.5 rounded-lg transition">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <div class="flex-1 min-w-0">
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                @forelse($mangaList as $manga)
                <a href="{{ route('manga.detail', $manga->slug) }}" class="group">
                    <div class="relative rounded-lg overflow-hidden bg-gray-800 aspect-[2/3]">
                        <img src="{{ $manga->thumbnail ?? 'https://via.placeholder.com/200x280/1a1a2e/7c3aed' }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" alt="">
                        <div class="absolute top-2 left-2 bg-gray-900/80 text-xs px-2 py-1 rounded">{{ $manga->type ?? 'Manga' }}</div>
                        @if($manga->chapters_count > 0)
                            <div class="absolute top-2 right-2 bg-purple-600/90 text-xs px-2 py-1 rounded">{{ $manga->chapters_count }}</div>
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
                @empty
                <div class="col-span-full text-center text-gray-500 py-12">No manga found.</div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $mangaList->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
