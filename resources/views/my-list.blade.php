@extends('layouts.main')

@section('title', 'My Anime List')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
    <h1 class="text-2xl font-bold mb-6">My Anime List</h1>

    <div class="flex flex-wrap gap-2 mb-6">
        <a href="{{ route('favorites.my-list') }}"
           class="px-4 py-2 rounded-lg text-sm font-medium transition
           {{ !$activeCategory ? 'bg-purple-600 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-white' }}">
            All
        </a>
        @foreach($categories as $key => $label)
        <a href="{{ route('favorites.my-list', ['category' => $key]) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium transition
           {{ $activeCategory === $key ? 'bg-purple-600 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-white' }}">
            {{ $label }}
        </a>
        @endforeach
        <a href="{{ route('favorites.my-list', ['category' => 'favorites']) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium transition
           {{ $activeCategory === 'favorites' ? 'bg-purple-600 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-white' }}">
            Favorites
        </a>
    </div>

    @if($favorites->count())
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-gray-400 border-b border-gray-800">
                    <th class="text-left py-3 px-2">Anime</th>
                    <th class="text-left py-3 px-2 hidden sm:table-cell">Type</th>
                    <th class="text-left py-3 px-2 hidden md:table-cell">Episodes</th>
                    <th class="text-left py-3 px-2">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($favorites as $fav)
                <tr class="border-b border-gray-800 hover:bg-gray-900 transition-colors">
                    <td class="py-3 px-2">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('anime.detail', $fav->anime->slug) }}" class="flex-shrink-0">
                                <img src="{{ $fav->anime->thumbnail_url }}" alt="{{ $fav->anime->title }}" class="w-10 h-14 object-cover rounded">
                            </a>
                            <div>
                                <a href="{{ route('anime.detail', $fav->anime->slug) }}" class="text-white font-medium hover:text-purple-400 transition">
                                    {{ $fav->anime->title }}
                                </a>
                                @if($fav->anime->year)
                                <p class="text-xs text-gray-500">{{ $fav->anime->year }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-2 hidden sm:table-cell text-gray-400">{{ $fav->anime->type ?? 'N/A' }}</td>
                    <td class="py-3 px-2 hidden md:table-cell text-gray-400">{{ $fav->anime->episodes_count ?? '?' }}</td>
                    <td class="py-3 px-2">
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-medium
                            @if(!$fav->category) bg-purple-900 text-purple-300
                            @else
                                @switch($fav->category)
                                    @case('watching') bg-blue-900 text-blue-300 @break
                                    @case('completed') bg-green-900 text-green-300 @break
                                    @case('plan_to_watch') bg-yellow-900 text-yellow-300 @break
                                    @case('on_hold') bg-orange-900 text-orange-300 @break
                                    @case('dropped') bg-red-900 text-red-300 @break
                                    @default bg-gray-800 text-gray-400
                                @endswitch
                            @endif">
                            {{ $fav->category ? ($categories[$fav->category] ?? $fav->category) : 'Favorites' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $favorites->links() }}
    </div>
    @else
    <div class="text-center py-16">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
        </svg>
        <p class="text-gray-400 text-lg">Your list is empty</p>
        <p class="text-gray-500 text-sm mt-2">Add anime to your list while watching to see them here.</p>
        <a href="{{ route('home') }}" class="inline-block mt-4 bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition">Browse Anime</a>
    </div>
    @endif
</div>
@endsection