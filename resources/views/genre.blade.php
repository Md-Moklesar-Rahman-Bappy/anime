@extends('layouts.main')

@section('title', $genre->name . ' Anime')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
    <h1 class="text-2xl font-bold mb-6">{{ $genre->name }} Anime</h1>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
        @forelse($animeList as $anime)
        <a href="{{ route('anime.detail', $anime->slug) }}" class="group">
            <div class="relative rounded-lg overflow-hidden bg-gray-800 aspect-[2/3]">
                <img src="{{ $anime->thumbnail_url }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" alt="" loading="lazy">
                <div class="absolute top-2 left-2 bg-gray-900/80 text-xs px-2 py-1 rounded">{{ $anime->type }}</div>
            </div>
            <h3 class="text-sm text-gray-300 mt-2 line-clamp-1 group-hover:text-white">{{ $anime->title }}</h3>
        </a>
        @empty
        <div class="col-span-full text-center text-gray-500 py-12">No anime found in this genre.</div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $animeList->links() }}
    </div>
</div>
@endsection
