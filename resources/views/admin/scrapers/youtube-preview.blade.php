@extends('admin.layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">YouTube Import Preview</h1>

    <div class="bg-gray-900 rounded-lg p-6">
        <div class="aspect-video bg-black rounded-lg mb-4 overflow-hidden">
            <iframe src="https://www.youtube.com/embed/{{ $info['id'] }}" class="w-full h-full" allowfullscreen></iframe>
        </div>

        <div class="space-y-3 mb-6">
            <div>
                <span class="text-sm text-gray-400">Title:</span>
                <p class="text-white font-semibold">{{ $info['title'] }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-400">Channel:</span>
                <p class="text-white">{{ $info['author'] }}</p>
            </div>
            @if($info['duration'])
            <div>
                <span class="text-sm text-gray-400">Duration:</span>
                <p class="text-white">{{ gmdate('H:i:s', $info['duration']) }}</p>
            </div>
            @endif
            @if($info['thumbnail'])
            <div>
                <span class="text-sm text-gray-400">Thumbnail:</span>
                <img src="{{ $info['thumbnail'] }}" class="w-40 rounded mt-1" alt="">
            </div>
            @endif
        </div>

        <form action="{{ route('admin.youtube.import') }}" method="POST">
            @csrf
            <input type="hidden" name="anime_id" value="{{ $anime->id }}">
            <input type="hidden" name="video_id" value="{{ $info['id'] }}">
            <input type="hidden" name="title" value="{{ $info['title'] }}">
            <input type="hidden" name="duration" value="{{ $info['duration'] }}">
            <input type="hidden" name="thumbnail" value="{{ $info['thumbnail'] }}">

            <div class="mb-4">
                <label class="block text-sm text-gray-400 mb-1">Episode Number</label>
                <input type="number" name="episode_number" value="{{ $episodeNumber ?? $anime->episodes_count + 1 }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700" required>
            </div>

            <div class="flex space-x-3">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg">Import Episode</button>
                <a href="{{ route('admin.anime.episodes.index', $anime) }}" class="bg-gray-800 hover:bg-gray-700 text-white px-6 py-2 rounded-lg text-center">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
