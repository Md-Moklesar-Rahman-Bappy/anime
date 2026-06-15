@extends('layouts.main')

@section('title', $episode->anime->title . ' - Episode ' . $episode->number)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-black rounded-lg overflow-visible" x-data="player()" x-init="init()">
                <div class="plyr-wrapper overflow-hidden rounded-t-lg">
                    @if($initialServer)
                        <video id="videoPlayer" class="w-full aspect-video" playsinline
                            {{ $episode->thumbnail_url ? 'poster="'.$episode->thumbnail_url.'"' : '' }}
                            @if($isYoutubeInit) data-plyr-provider="youtube" data-plyr-embed-id="{{ $initialServer['url'] }}" @endif>
                            @if(!$isYoutubeInit)
                            <source src="{{ $initialServer['url'] }}" type="{{ $initialServer['type'] === 'm3u8' ? 'application/x-mpegURL' : 'video/mp4' }}">
                            @endif
                        </video>
                    @else
                        <div class="w-full aspect-video flex items-center justify-center bg-gray-900 text-gray-500">
                            <div class="text-center">
                                <svg class="w-16 h-16 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                <p class="text-sm">No video source available for this episode.</p>
                            </div>
                        </div>
                    @endif

                    <div class="skip-overlay" x-cloak x-show="showSkipIntro || showSkipOutro">
                        <button x-cloak x-show="showSkipIntro" @click="skipIntro()">Skip Intro</button>
                        <button x-cloak x-show="showSkipOutro" @click="skipOutro()">Skip Outro</button>
                    </div>
                </div>

                <div class="player-control-bar">
                    <div class="ctrl-group">
                        <button class="ctrl-btn" @click="togglePlay()" title="Play/Pause (Space)">
                            <i class="fa-solid" :class="playing ? 'fa-pause' : 'fa-play'"></i>
                        </button>
                        <button class="ctrl-btn" @click="skip(-config.skipSeconds)" title="Rewind 10s (J)">
                            <i class="fa-solid fa-backward"></i> <span class="label">10s</span>
                        </button>
                        <button class="ctrl-btn" @click="skip(config.skipSeconds)" title="Forward 10s (L)">
                            <i class="fa-solid fa-forward"></i> <span class="label">10s</span>
                        </button>
                        <button class="ctrl-btn" :class="{ active: config.isLight }" @click="toggleLight()" title="Light mode">
                            <i class="fa-solid fa-lightbulb"></i>
                        </button>
                    </div>
                    <div class="ctrl-group">
                        <button class="ctrl-btn" :class="{ active: config.autoPlay }" @click="toggleAutoPlay()" title="Auto Play">
                            <i class="fa-solid" :class="config.autoPlay ? 'fa-check-square' : 'fa-square'"></i>
                            <span class="label">Auto Play</span>
                        </button>
                        <button class="ctrl-btn" :class="{ active: config.autoNext }" @click="toggleAutoNext()" title="Auto Next">
                            <i class="fa-solid" :class="config.autoNext ? 'fa-check-square' : 'fa-square'"></i>
                            <span class="label">Auto Next</span>
                        </button>
                        <button class="ctrl-btn" :class="{ active: config.autoSkip }" @click="toggleAutoSkip()" title="Auto Skip Intro/Outro">
                            <i class="fa-solid" :class="config.autoSkip ? 'fa-check-square' : 'fa-square'"></i>
                            <span class="label">Auto Skip</span>
                        </button>
                    </div>
                    <div class="ctrl-group">
                        @if($prevEpisode)
                        <a href="{{ route('watch', ['slug' => $anime->slug, 'ep' => $prevEpisode->number]) }}" class="ctrl-btn" title="Previous episode (B)">
                            <i class="fa-solid fa-backward-step"></i> <span class="label">Prev</span>
                        </a>
                        @endif
                        @if($nextEpisode)
                        <a href="{{ route('watch', ['slug' => $anime->slug, 'ep' => $nextEpisode->number]) }}" class="ctrl-btn" title="Next episode (N)">
                            <span class="label">Next</span> <i class="fa-solid fa-forward-step"></i>
                        </a>
                        @endif
                    </div>
                    <div class="ctrl-group">
                        @if(count($allServers) > 1)
                        <select class="server-select" @change="switchServer($event.target.selectedIndex)">
                            @foreach($allServers as $i => $s)
                            <option value="{{ $s['server_id'] }}" @if($i === 0) selected @endif>{{ $s['label'] }}</option>
                            @endforeach
                        </select>
                        @endif
                        <div class="relative" @click.outside="listOpen = false">
                            <button class="ctrl-btn" @click="toggleList()" title="Add to list">
                                <i class="fa-solid fa-bookmark"></i> <span class="label">List</span>
                            </button>
                            <div class="player-dropdown" x-cloak x-show="listOpen">
                                <template x-for="cat in categories" :key="cat.value">
                                    <button class="dropdown-item" :class="{ active: favoriteCategory === cat.value }" @click="updateList(favoriteCategory === cat.value ? null : cat.value)">
                                        <span class="check">
                                            <i class="fa-solid fa-check" x-show="favoriteCategory === cat.value"></i>
                                        </span>
                                        <span x-text="cat.label"></span>
                                    </button>
                                </template>
                                <hr style="border-color:#374151; margin: 6px 0">
                                <button class="dropdown-item" @click="updateList(null)" :class="{ active: !favoriteCategory }">
                                    <span class="check">
                                        <i class="fa-solid fa-check" x-show="!favoriteCategory"></i>
                                    </span>
                                    <span>Not in list</span>
                                </button>
                            </div>
                        </div>
                        <div class="relative" @click.outside="reportOpen = false">
                            <button class="ctrl-btn" @click="toggleReport()" title="Report issue">
                                <i class="fa-solid fa-triangle-exclamation"></i> <span class="label">Report</span>
                            </button>
                            <div class="player-dropdown" x-cloak x-show="reportOpen">
                                <div style="padding: 8px 12px">
                                    <div style="margin-bottom: 10px">
                                        <label style="font-size:12px; color:#9ca3af; display:block; margin-bottom:4px">Issue type</label>
                                        <select x-model="reportType" class="server-select" style="width:100%">
                                            <template x-for="it in issueTypes" :key="it.value">
                                                <option :value="it.value" x-text="it.label"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div style="margin-bottom: 10px">
                                        <label style="font-size:12px; color:#9ca3af; display:block; margin-bottom:4px">Description</label>
                                        <textarea x-model="reportDesc" rows="3" style="width:100%; background:#111827; border:1px solid #374151; border-radius:4px; padding:6px 8px; color:#d1d5db; font-size:12px; resize:vertical" placeholder="Describe the issue..."></textarea>
                                    </div>
                                    <button @click="submitReport()" :disabled="submitting" style="width:100%; background:#7c3aed; color:#fff; border:none; border-radius:4px; padding:6px; font-size:12px; font-weight:600; cursor:pointer" x-text="submitting ? 'Submitting...' : 'Submit Report'"></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
                        <img src="{{ $ep->thumbnail_url }}" class="w-20 h-12 object-cover rounded" alt="">
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
                        <img src="{{ $rel->thumbnail_url }}" class="w-10 h-14 object-cover rounded" alt="">
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

@push('scripts')
<script>
window.PLAYER_SERVERS = @json($allServers);
window.PLAYER_IS_YOUTUBE = {{ $isYoutubeInit ? 'true' : 'false' }};
window.PLAYER_IS_FAVORITED = {{ $isFavorited ? 'true' : 'false' }};
window.PLAYER_FAV_CATEGORY = {{ $favCategory ? '"'.$favCategory.'"' : 'null' }};
window.PLAYER_NEXT_URL = {{ $nextEpisode ? '"'.route('watch', ['slug' => $anime->slug, 'ep' => $nextEpisode->number]).'"' : 'null' }};
window.PLAYER_PREV_URL = {{ $prevEpisode ? '"'.route('watch', ['slug' => $anime->slug, 'ep' => $prevEpisode->number]).'"' : 'null' }};
window.PLAYER_ANIME_ID = {{ $anime->id }};
window.PLAYER_EPISODE_ID = {{ $episode->id }};
window.PLAYER_IS_AUTH = {{ auth()->check() ? 'true' : 'false' }};
window.PLAYER_LOGIN_URL = '{{ route('login') }}';
window.PLAYER_SKIP_TIMES = @json($skipTimes);
</script>
@endpush
@endsection
