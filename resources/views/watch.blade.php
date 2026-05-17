@extends('layouts.main')

@section('title', $episode->anime->title . ' - Episode ' . $episode->number)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-black rounded-lg overflow-hidden aspect-video relative" x-data="player()">
                @php
                    $youtubeServer = $episode->servers->firstWhere('type', 'youtube');
                    $videoServers = $episode->servers->where('type', '!=', 'youtube');
                    $hasServers = $videoServers->count() > 0;
                    $hasVideoPath = !empty($episode->video_path);
                    // Detect YouTube URLs stored directly in video_path (legacy data)
                    $ytInVideoPath = false;
                    $ytVideoId = null;
                    if ($hasVideoPath && !$youtubeServer) {
                        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]+)/', $episode->video_path, $m)) {
                            $ytInVideoPath = true;
                            $ytVideoId = $m[1];
                        }
                    }
                @endphp

                @if($youtubeServer || $ytInVideoPath)
                    @php $embedUrl = $youtubeServer ? $youtubeServer->url : 'https://www.youtube.com/embed/' . $ytVideoId; @endphp
                    @php $watchUrl = $episode->source_url ?: 'https://www.youtube.com/watch?v=' . ($youtubeServer ? basename($youtubeServer->url) : $ytVideoId); @endphp
                    <div class="relative w-full h-full">
                        <iframe src="{{ $embedUrl }}?rel=0" class="w-full h-full" allow="encrypted-media" allowfullscreen></iframe>
                        <a href="{{ $watchUrl }}" target="_blank" rel="noopener" class="absolute top-4 right-4 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition flex items-center space-x-2 shadow-lg z-10">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0C.488 3.45.029 5.804 0 12c.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0C23.512 20.55 23.971 18.196 24 12c-.029-6.185-.484-8.549-4.385-8.816zM9 16V8l8 4-8 4z"/></svg>
                            <span>Watch on YouTube</span>
                        </a>
                        <div class="absolute bottom-4 left-4 flex space-x-2 z-10">
                            <span class="bg-red-600/80 text-white text-xs px-2 py-1 rounded">YouTube</span>
                        </div>
                    </div>
                @elseif($hasServers || $hasVideoPath)
                    <video id="videoPlayer" class="w-full h-full" controls {{ $episode->thumbnail ? 'poster="'.$episode->thumbnail.'"' : '' }}>
                        @foreach($videoServers as $server)
                            <source src="{{ $server->url }}" type="{{ $server->type === 'm3u8' ? 'application/x-mpegURL' : 'video/mp4' }}">
                        @endforeach
                        @if(!$hasServers && $hasVideoPath)
                            @php $videoSrc = str_starts_with($episode->video_path, 'http') ? $episode->video_path : Storage::url($episode->video_path); @endphp
                            <source src="{{ $videoSrc }}" type="video/mp4">
                        @endif
                    </video>
                @else
                    <div class="w-full h-full flex items-center justify-center bg-gray-900 text-gray-500">
                        <div class="text-center">
                            <svg class="w-16 h-16 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <p class="text-sm">No video source available for this episode.</p>
                        </div>
                    </div>
                @endif

                @if($episode->skipTimes->count() && !$youtubeServer)
                <div class="absolute bottom-20 left-4 flex space-x-2">
                    @if($episode->skipTimes->first()->intro_start)
                    <button onclick="skipIntro()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">Skip Intro</button>
                    @endif
                    @if($episode->skipTimes->first()->outro_start)
                    <button onclick="skipOutro()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">Skip Outro</button>
                    @endif
                </div>
                @endif
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    @if($prevEpisode)
                    <a href="{{ route('watch', ['slug' => $anime->slug, 'ep' => $prevEpisode->number]) }}" class="bg-gray-800 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm transition flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z"/></svg>
                        Prev
                    </a>
                    @endif
                    @if($nextEpisode)
                    <a href="{{ route('watch', ['slug' => $anime->slug, 'ep' => $nextEpisode->number]) }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm transition flex items-center">
                        Next
                        <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20"><path d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z"/></svg>
                    </a>
                    @endif
                </div>
                <select onchange="window.location.href=this.value" class="bg-gray-800 text-white text-sm rounded-lg px-4 py-2 border border-gray-700">
                    @foreach($anime->episodes as $ep)
                    <option value="{{ route('watch', ['slug' => $anime->slug, 'ep' => $ep->number]) }}" {{ $ep->id === $episode->id ? 'selected' : '' }}>
                        Episode {{ $ep->number }} {{ $ep->title ? '- ' . $ep->title : '' }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="bg-gray-900 rounded-lg p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <h1 class="text-xl font-bold">{{ $anime->title }} - Episode {{ $episode->number }}</h1>
                        @if($episode->title)
                        <p class="text-gray-400 text-sm mt-1">{{ $episode->title }}</p>
                        @endif
                    </div>
                    @auth
                    <button onclick="toggleFavorite({{ $anime->id }})" class="text-gray-400 hover:text-red-500 transition">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"/></svg>
                    </button>
                    @endauth
                </div>
            </div>

            <div class="bg-gray-900 rounded-lg p-4">
                <h3 class="font-bold mb-4">Comments</h3>
                @auth
                <form action="{{ route('comments.store') }}" method="POST" class="mb-4">
                    @csrf
                    <input type="hidden" name="episode_id" value="{{ $episode->id }}">
                    <textarea name="body" rows="3" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Write a comment..." required></textarea>
                    <button type="submit" class="mt-2 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm transition">Post Comment</button>
                </form>
                @else
                <p class="text-gray-400 text-sm mb-4"><a href="{{ route('login') }}" class="text-purple-500 hover:text-purple-400">Login</a> to comment.</p>
                @endauth
                <div class="space-y-4">
                    @foreach($comments as $comment)
                    <div class="flex space-x-3">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($comment->user->name) }}&background=7c3aed&color=fff" class="w-8 h-8 rounded-full" alt="">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-semibold">{{ $comment->user->name }}</span>
                                <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-gray-300 mt-1">{{ $comment->body }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @if($comments->hasPages())
                <div class="mt-4">{{ $comments->links() }}</div>
                @endif
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-gray-900 rounded-lg p-4">
                <h3 class="font-bold mb-3">Episodes</h3>
                <div class="space-y-2 max-h-[500px] overflow-y-auto">
                    @foreach($anime->episodes as $ep)
                    <a href="{{ route('watch', ['slug' => $anime->slug, 'ep' => $ep->number]) }}" class="flex items-center space-x-3 p-2 rounded-lg transition {{ $ep->id === $episode->id ? 'bg-purple-600/20 border border-purple-600' : 'hover:bg-gray-800' }}">
                        <img src="{{ $ep->thumbnail ?? $anime->thumbnail ?? 'https://via.placeholder.com/80x45/1a1a2e/7c3aed' }}" class="w-20 h-12 object-cover rounded" alt="">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm truncate">Episode {{ $ep->number }}</p>
                            @if($ep->title)<p class="text-xs text-gray-500 truncate">{{ $ep->title }}</p>@endif
                        </div>
                        @if($ep->has_sub)<span class="text-xs text-blue-500">SUB</span>@endif
                    </a>
                    @endforeach
                </div>
            </div>

            <div class="bg-gray-900 rounded-lg p-4">
                <h3 class="font-bold mb-2">{{ $anime->title }}</h3>
                <p class="text-sm text-gray-400">{{ Str::limit($anime->description, 150) }}</p>
                <a href="{{ route('anime.detail', $anime->slug) }}" class="text-purple-500 hover:text-purple-400 text-sm mt-2 inline-block">View Details</a>
            </div>

            <div class="bg-gray-900 rounded-lg p-4">
                <h3 class="font-bold mb-3">Related</h3>
                <div class="space-y-2">
                    @foreach($related as $rel)
                    <a href="{{ route('anime.detail', $rel->slug) }}" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-800 transition">
                        <img src="{{ $rel->thumbnail ?? 'https://via.placeholder.com/40x56/1a1a2e/7c3aed' }}" class="w-10 h-14 object-cover rounded" alt="">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm truncate">{{ $rel->title }}</p>
                            <p class="text-xs text-gray-500">{{ $rel->type }} | {{ $rel->year }}</p>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function skipIntro() {
    const video = document.getElementById('videoPlayer');
    const skipTime = @json($episode->skipTimes->first());
    if (skipTime && skipTime.intro_end) video.currentTime = skipTime.intro_end;
}
function skipOutro() {
    const video = document.getElementById('videoPlayer');
    const skipTime = @json($episode->skipTimes->first());
    if (skipTime && skipTime.outro_end) video.currentTime = skipTime.outro_end;
}
function toggleFavorite(animeId) {
    fetch('/favorites/toggle', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ anime_id: animeId })
    }).then(r => r.json()).then(d => { if(d.status) location.reload(); });
}
</script>
@endsection
