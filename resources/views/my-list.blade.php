@extends('layouts.main')

@section('title', 'My Anime List')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-white">My Anime List</h1>
            <p class="text-sm text-gray-400 mt-1">
                Manage your favorites and watch progress
            </p>
        </div>

        <span class="text-sm text-gray-500">
            {{ $favorites->total() }} items
        </span>
    </div>

    <!-- Categories -->
    <div class="flex flex-wrap gap-2 mb-6">

        <a href="{{ route('favorites.my-list') }}"
           class="tab {{ !$activeCategory ? 'active' : '' }}">
            All
        </a>

        @foreach($categories as $key => $label)
        <a href="{{ route('favorites.my-list', ['category'=>$key]) }}"
           class="tab {{ $activeCategory === $key ? 'active' : '' }}">
            {{ $label }}
        </a>
        @endforeach

        <a href="{{ route('favorites.my-list', ['category'=>'favorites']) }}"
           class="tab {{ $activeCategory === 'favorites' ? 'active' : '' }}">
            Favorites
        </a>

    </div>

    @if($favorites->count())

    <!-- Grid Layout -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">

        @foreach($favorites as $fav)
        <div class="anime-card">

            <a href="{{ route('anime.detail', $fav->anime->slug) }}" class="group block">

                <!-- Image -->
                <img
                    src="{{ $fav->anime->thumbnail_url }}"
                    alt="{{ $fav->anime->title }}"
                    class="anime-img"
                    loading="lazy"
                >

                <!-- Overlay -->
                <div class="anime-overlay">
                    ▶ View
                </div>

                <!-- Category Badge -->
                <span class="status-badge {{ $fav->category }}">
                    {{ $fav->category ? ($categories[$fav->category] ?? $fav->category) : 'Favorites' }}
                </span>

            </a>

            <!-- Title -->
            <h3 class="anime-title">
                {{ $fav->anime->title }}
            </h3>

            <!-- Meta -->
            <div class="anime-meta">
                {{ $fav->anime->type ?? 'N/A' }} · {{ $fav->anime->episodes_count ?? '?' }} eps
            </div>

        </div>
        @endforeach

    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $favorites->links() }}
    </div>

    @else

    <!-- Empty State -->
    <div class="text-center py-20">

        <div class="text-5xl mb-4">📺</div>

        <p class="text-lg text-gray-400">
            Your list is empty
        </p>

        <p class="text-sm text-gray-500 mt-2">
            Start adding anime to track your progress
        </p>

        <a href="{{ route('home') }}"
           class="inline-block mt-4 bg-indigo-600 hover:bg-indigo-500 px-6 py-2 rounded-lg text-white">
            Browse Anime
        </a>

    </div>

    @endif

</div>

<style>
.tab {
    @apply px-4 py-2 rounded-lg text-sm font-medium bg-[#1f2937] text-gray-400 hover:text-white hover:bg-[#2b3545] transition;
}
.tab.active {
    @apply bg-indigo-600 text-white;
}

/* Card */
.anime-card {
    @apply rounded-xl overflow-hidden;
}

.anime-img {
    @apply w-full h-[240px] object-cover rounded-xl group-hover:scale-105 transition duration-300;
}

.anime-overlay {
    @apply absolute inset-0 bg-black/70 flex items-center justify-center opacity-0 group-hover:opacity-100 transition text-white text-sm;
}

.anime-title {
    @apply text-sm text-gray-300 mt-2 truncate group-hover:text-white;
}

.anime-meta {
    @apply text-xs text-gray-500 mt-1;
}

/* Status Colors */
.status-badge {
    @apply absolute top-2 right-2 text-xs px-2 py-1 rounded;
}
.status-badge.watching { @apply bg-blue-600; }
.status-badge.completed { @apply bg-green-600; }
.status-badge.plan_to_watch { @apply bg-yellow-500; }
.status-badge.on_hold { @apply bg-orange-500; }
.status-badge.dropped { @apply bg-red-600; }
.status-badge.favorites { @apply bg-indigo-600; }
</style>
@endsection