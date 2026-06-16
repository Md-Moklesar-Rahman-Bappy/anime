@extends('layouts.main')

@section('title', $episode->anime->title . ' - Episode ' . $episode->number)

@push('styles')
<style>
    .player-shell {
        @apply bg-black rounded-2xl overflow-hidden border border-gray-800 shadow-2xl;
    }

    .player-surface {
        @apply relative w-full aspect-video bg-black;
    }

    .player-control-bar {
        @apply flex flex-wrap items-center justify-between gap-3 px-4 py-3 bg-[#111827] border-t border-gray-800;
    }

    .ctrl-group {
        @apply flex items-center gap-2 flex-wrap;
    }

    .ctrl-btn {
        @apply inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-[#1f2937] text-gray-300 hover:text-white hover:bg-indigo-600 transition text-sm border border-gray-700;
    }

    .ctrl-btn.active {
        @apply bg-indigo-600 text-white border-indigo-500;
    }

    .ctrl-btn .label {
        @apply hidden sm:inline;
    }

    .server-select {
        @apply bg-[#1f2937] text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500;
    }

    .player-dropdown {
        @apply absolute right-0 mt-2 w-64 bg-[#111827] border border-gray-800 rounded-xl shadow-2xl z-50 overflow-hidden;
    }

    .dropdown-item {
        @apply w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-300 hover:bg-[#1f2937] hover:text-white transition text-left;
    }

    .dropdown-item.active {
        @apply bg-indigo-600/20 text-white;
    }

    .dropdown-item .check {
        @apply w-4 text-center text-indigo-400;
    }

    .skip-overlay {
        @apply absolute bottom-4 right-4 z-20 flex gap-2;
    }

    .skip-btn {
        @apply px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-medium shadow-lg transition;
    }

    .section-card {
        @apply bg-[#111827] border border-gray-800 rounded-2xl p-4;
    }

    .episode-link {
        @apply flex items-center gap-3 p-2 rounded-xl hover:bg-[#1f2937] transition;
    }

    .episode-link.active {
        @apply bg-indigo-600/20 border border-indigo-600;
    }

    .comment-box {
        @apply w-full bg-[#1f2937] text-white rounded-lg px-4 py-3 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500;
    }

    .meta-badge {
        @apply inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-[#1f2937] text-gray-300;
    }

    .meta-badge-blue {
        @apply inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-blue-600 text-white;
    }

    .meta-badge-green {
        @apply inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-green-600 text-white;
    }

    .related-card {
        @apply flex items-center gap-3 p-2 rounded-xl hover:bg-[#1f2937] transition;
    }

    .thumb-cover {
        @apply object-cover rounded-lg bg-[#1f2937];
    }

    .player-empty {
        @apply absolute inset-0 flex items-center justify-center bg-[#0a0a0f] text-gray-500;
    }

    .player-panel-title {
        @apply text-lg font-semibold text-white mb-3;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT / PLAYER --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- PLAYER --}}
            <div class="player-shell"
                 x-data="player()"
                 x-init="init()"
                 x-cloak>

                <div class="player-surface">

                    @if($initialServer)
                        @php
                            $_mimeMap = [
                                'mp4'  => 'video/mp4',
                                'webm' => 'video/webm',
                                'm3u8' => 'application/x-mpegURL',
                            ];

                            $_mimeType = $_mimeMap[$initialServer['type']] ?? null;
                            $_isEmbedInit = $initialServer['type'] === 'embed';
                        @endphp

                        {{-- Embed Player --}}
                        <iframe
                            x-show="isEmbed"
                            :src="embedUrl"
                            class="w-full h-full"
                            frameborder="0"
                            allowfullscreen
                            allow="autoplay; encrypted-media; picture-in-picture"
                        ></iframe>

                        {{-- Native / HTML5 Player --}}
                        <div x-show="!isEmbed" class="w-full h-full">
                            <video
                                id="videoPlayer"
                                class="w-full h-full"
                                playsinline
                                controls
                                preload="metadata"
                                @if($episode->thumbnail_url)
                                    poster="{{ $episode->thumbnail_url }}"
                                @endif
                                @if($isYoutubeInit)
                                    data-plyr-provider="youtube"
                                    data-plyr-embed-id="{{ $youtubeVideoId ?? '' }}"
                                @endif
                            >
                                @if(!$isYoutubeInit && !$_isEmbedInit)
                                    <source src="{{ $initialServer['url'] }}" @if($_mimeType) type="{{ $_mimeType }}" @endif>
                                @endif
                            </video>
                        </div>
                    @else
                        <div class="player-empty">
                            <div class="text-center">
                                <svg class="w-16 h-16 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-sm">No video source available for this episode.</p>
                            </div>
                        </div>
                    @endif

                    {{-- Skip Buttons --}}
                    <div class="skip-overlay" x-show="showSkipIntro || showSkipOutro" x-cloak>
                        <button class="skip-btn" x-show="showSkipIntro" @click="skipIntro()">Skip Intro</button>
                        <button class="skip-btn" x-show="showSkipOutro" @click="skipOutro()">Skip Outro</button>
                    </div>
                </div>

                {{-- CONTROL BAR --}}
                <div class="player-control-bar">

                    {{-- LEFT CONTROLS --}}
                    <div class="ctrl-group">
                        <button class="ctrl-btn" @click="togglePlay()" title="Play / Pause (Space)">
                            <i class="fa-solid" :class="playing ? 'fa-pause' : 'fa-play'"></i>
                            <span class="label">Play</span>
                        </button>

                        <button class="ctrl-btn" @click="skip(-10)" title="Rewind 10s (J)">
                            <i class="fa-solid fa-backward"></i>
                            <span class="label">10s</span>
                        </button>

                        <button class="ctrl-btn" @click="skip(10)" title="Forward 10s (L)">
                            <i class="fa-solid fa-forward"></i>
                            <span class="label">10s</span>
                        </button>

                        <button class="ctrl-btn" :class="{ active: config.lightMode }" @click="toggleLight()" title="Light mode">
                            <i class="fa-solid fa-lightbulb"></i>
                            <span class="label">Light</span>
                        </button>
                    </div>

                    {{-- AUTO CONTROLS --}}
                    <div class="ctrl-group">
                        <button class="ctrl-btn" :class="{ active: config.autoPlay }" @click="toggleAutoPlay()">
                            <i class="fa-solid" :class="config.autoPlay ? 'fa-check-square' : 'fa-square'"></i>
                            <span class="label">Auto Play</span>
                        </button>

                        <button class="ctrl-btn" :class="{ active: config.autoNext }" @click="toggleAutoNext()">
                            <i class="fa-solid" :class="config.autoNext ? 'fa-check-square' : 'fa-square'"></i>
                            <span class="label">Auto Next</span>
                        </button>

                        <button class="ctrl-btn" :class="{ active: config.autoSkip }" @click="toggleAutoSkip()">
                            <i class="fa-solid" :class="config.autoSkip ? 'fa-check-square' : 'fa-square'"></i>
                            <span class="label">Auto Skip</span>
                        </button>
                    </div>

                    {{-- EPISODE NAV --}}
                    <div class="ctrl-group">
                        @if($prevEpisode)
                            <a href="{{ route('watch', ['slug' => $anime->slug, 'ep' => $prevEpisode->number]) }}" class="ctrl-btn" title="Previous Episode (B)">
                                <i class="fa-solid fa-backward-step"></i>
                                <span class="label">Prev</span>
                            </a>
                        @endif

                        @if($nextEpisode)
                            <a href="{{ route('watch', ['slug' => $anime->slug, 'ep' => $nextEpisode->number]) }}" class="ctrl-btn" title="Next Episode (N)">
                                <span class="label">Next</span>
                                <i class="fa-solid fa-forward-step"></i>
                            </a>
                        @endif
                    </div>

                    {{-- RIGHT ACTIONS --}}
                    <div class="ctrl-group">

                        @if(count($allServers) > 1)
                            <select class="server-select" @change="switchServer($event.target.selectedIndex)">
                                @foreach($allServers as $i => $s)
                                    <option value="{{ $s['server_id'] }}" @if($i === 0) selected @endif>
                                        {{ $s['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        @endif

                        {{-- List Dropdown --}}
                        <div class="relative" @click.outside="listOpen = false">
                            <button class="ctrl-btn" @click="toggleList()">
                                <i class="fa-solid fa-bookmark"></i>
                                <span class="label">List</span>
                            </button>

                            <div class="player-dropdown" x-show="listOpen" x-cloak>
                                <template x-for="cat in categories" :key="cat.value">
                                    <button class="dropdown-item"
                                            :class="{ active: favoriteCategory === cat.value }"
                                            @click="updateList(favoriteCategory === cat.value ? null : cat.value)">
                                        <span class="check">
                                            <i class="fa-solid fa-check" x-show="favoriteCategory === cat.value"></i>
                                        </span>
                                        <span x-text="cat.label"></span>
                                    </button>
                                </template>

                                <hr class="border-gray-700 my-1">

                                <button class="dropdown-item"
                                        :class="{ active: !favoriteCategory }"
                                        @click="updateList(null)">
                                    <span class="check">
                                        <i class="fa-solid fa-check" x-show="!favoriteCategory"></i>
                                    </span>
                                    <span>Not in list</span>
                                </button>
                            </div>
                        </div>

                        {{-- Report Dropdown --}}
                        <div class="relative" @click.outside="reportOpen = false">
                            <button class="ctrl-btn" @click="toggleReport()">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                <span class="label">Report</span>
                            </button>

                            <div class="player-dropdown" x-show="reportOpen" x-cloak>
                                <div class="p-3 space-y-3">

                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Issue type</label>
                                        <select x-model="reportType" class="server-select w-full">
                                            <template x-for="it in issueTypes" :key="it.value">
                                                <option :value="it.value" x-text="it.label"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Description</label>
                                        <textarea x-model="reportDesc"
                                                  rows="3"
                                                  class="comment-box text-sm"
                                                  placeholder="Describe the issue..."></textarea>
                                    </div>

                                    <button @click="submitReport()"
                                            :disabled="submitting"
                                            class="w-full bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg text-sm font-medium transition"
                                            x-text="submitting ? 'Submitting...' : 'Submit Report'">
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

            {{-- TOP NAV --}}
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div class="flex items-center gap-3">
                    @if($prevEpisode)
                        <a href="{{ route('watch', ['slug' => $anime->slug, 'ep' => $prevEpisode->number]) }}"
                           class="ctrl-btn">
                            <i class="fa-solid fa-arrow-left"></i>
                            <span class="label">Prev</span>
                        </a>
                    @endif

                    @if($nextEpisode)
                        <a href="{{ route('watch', ['slug' => $anime->slug, 'ep' => $nextEpisode->number]) }}"
                           class="ctrl-btn active">
                            <span class="label">Next</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    @endif
                </div>

                <select onchange="window.location.href=this.value" class="server-select">
                    @foreach($anime->episodes as $ep)
                        <option value="{{ route('watch', ['slug' => $anime->slug, 'ep' => $ep->number]) }}"
                                {{ $ep->id === $episode->id ? 'selected' : '' }}>
                            Episode {{ $ep->number }} {{ $ep->title ? '- ' . $ep->title : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- EPISODE DETAILS --}}
            <div class="section-card">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-semibold text-white">
                            {{ $anime->title }} — Episode {{ $episode->number }}
                        </h1>

                        @if($episode->title)
                            <p class="text-gray-400 text-sm mt-1">{{ $episode->title }}</p>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @if($episode->has_sub)
                            <span class="meta-badge-blue">SUB</span>
                        @endif
                        @if($episode->has_dub)
                            <span class="meta-badge-green">DUB</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- COMMENTS --}}
            <div class="section-card">
                <h3 class="player-panel-title">Comments</h3>

                @auth
                    <form action="{{ route('comments.store') }}" method="POST" class="mb-5">
                        @csrf
                        <input type="hidden" name="episode_id" value="{{ $episode->id }}">

                        <textarea name="body"
                                  rows="3"
                                  class="comment-box"
                                  placeholder="Write a comment..."
                                  required></textarea>

                        <button type="submit"
                                class="mt-3 bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                            Post Comment
                        </button>
                    </form>
                @else
                    <p class="text-sm text-gray-400 mb-4">
                        <a href="{{ route('auth.login') }}" class="text-indigo-400 hover:text-indigo-300">Login</a>
                        to comment.
                    </p>
                @endauth

                <div class="space-y-4">
                    @foreach($comments as $comment)
                        <div class="flex gap-3">
                            <img
                                src="https://ui-avatars.com/api/?name={{ urlencode($comment->user->name) }}&background=4f46e5&color=fff"
                                class="w-8 h-8 rounded-full"
                                alt="{{ $comment->user->name }}"
                            >

                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-white">{{ $comment->user->name }}</span>
                                    <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>

                                    @auth
                                        @if(auth()->user()->isSuperAdmin())
                                            <form action="{{ route('comments.destroy', $comment) }}"
                                                  method="POST"
                                                  class="ml-auto"
                                                  onsubmit="return confirm('Delete this comment?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-red-500 hover:text-red-400">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    @endauth
                                </div>

                                <p class="text-sm text-gray-300 mt-1">{{ $comment->body }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($comments->hasPages())
                    <div class="mt-4">
                        {{ $comments->links() }}
                    </div>
                @endif
            </div>

        </div>

        {{-- RIGHT SIDEBAR --}}
        <div class="space-y-4">

            {{-- EPISODES --}}
            <div class="section-card">
                <h3 class="player-panel-title">Episodes</h3>

                <div class="space-y-2 max-h-[500px] overflow-y-auto pr-1">
                    @foreach($anime->episodes as $ep)
                        <a href="{{ route('watch', ['slug' => $anime->slug, 'ep' => $ep->number]) }}"
                           class="episode-link {{ $ep->id === $episode->id ? 'active' : '' }}">
                            <img src="{{ $ep->thumbnail_url }}"
                                 class="w-20 h-12 thumb-cover"
                                 alt="Episode {{ $ep->number }}"
                                 loading="lazy">

                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-white truncate">Episode {{ $ep->number }}</p>
                                @if($ep->title)
                                    <p class="text-xs text-gray-500 truncate">{{ $ep->title }}</p>
                                @endif
                            </div>

                            <div class="flex flex-col gap-1">
                                @if($ep->has_sub)
                                    <span class="text-[10px] text-blue-400 font-medium">SUB</span>
                                @endif
                                @if($ep->has_dub)
                                    <span class="text-[10px] text-green-400 font-medium">DUB</span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- ANIME INFO --}}
            <div class="section-card">
                <h3 class="player-panel-title">{{ $anime->title }}</h3>
                <p class="text-sm text-gray-400">
                    {{ \Illuminate\Support\Str::limit($anime->description, 150) }}
                </p>
                <a href="{{ route('anime.detail', $anime->slug) }}"
                   class="inline-block mt-3 text-sm text-indigo-400 hover:text-indigo-300">
                    View Details
                </a>
            </div>

            {{-- RELATED --}}
            @if($related->count())
                <div class="section-card">
                    <h3 class="player-panel-title">Related</h3>

                    <div class="space-y-2">
                        @foreach($related as $rel)
                            <a href="{{ route('anime.detail', $rel->slug) }}" class="related-card">
                                <img src="{{ $rel->thumbnail_url }}"
                                     class="w-10 h-14 thumb-cover"
                                     alt="{{ $rel->title }}"
                                     loading="lazy">

                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-white truncate">{{ $rel->title }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ $rel->type }} @if($rel->year) | {{ $rel->year }} @endif
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.PLAYER_SERVERS = @json($allServers);
    window.PLAYER_LANGUAGES = @json($languages);
    window.PLAYER_IS_YOUTUBE = {{ $isYoutubeInit ? 'true' : 'false' }};
    window.PLAYER_IS_FAVORITED = {{ $isFavorited ? 'true' : 'false' }};
    window.PLAYER_FAV_CATEGORY = {!! $favCategory ? json_encode($favCategory) : 'null' !!};
    window.PLAYER_NEXT_URL = {!! $nextEpisode ? json_encode(route('watch', ['slug' => $anime->slug, 'ep' => $nextEpisode->number])) : 'null' !!};
    window.PLAYER_PREV_URL = {!! $prevEpisode ? json_encode(route('watch', ['slug' => $anime->slug, 'ep' => $prevEpisode->number])) : 'null' !!};
    window.PLAYER_ANIME_ID = {{ $anime->id }};
    window.PLAYER_EPISODE_ID = {{ $episode->id }};
    window.PLAYER_IS_AUTH = {{ auth()->check() ? 'true' : 'false' }};
    window.PLAYER_LOGIN_URL = '{{ route('auth.login') }}';
    window.PLAYER_SKIP_TIMES = @json($skipTimes);

    function player() {
        return {
            video: null,
            playing: false,
            isEmbed: false,
            embedUrl: '',
            currentServerIndex: 0,

            listOpen: false,
            reportOpen: false,
            submitting: false,

            showSkipIntro: false,
            showSkipOutro: false,

            favoriteCategory: window.PLAYER_FAV_CATEGORY,
            categories: [
                { value: 'watching', label: 'Watching' },
                { value: 'completed', label: 'Completed' },
                { value: 'plan_to_watch', label: 'Plan to Watch' },
                { value: 'on_hold', label: 'On Hold' },
                { value: 'dropped', label: 'Dropped' }
            ],

            issueTypes: [
                { value: 'broken_video', label: 'Broken video' },
                { value: 'wrong_episode', label: 'Wrong episode' },
                { value: 'subtitle_issue', label: 'Subtitle issue' },
                { value: 'audio_issue', label: 'Audio issue' },
                { value: 'other', label: 'Other' }
            ],

            reportType: 'broken_video',
            reportDesc: '',

            config: {
                autoPlay: true,
                autoNext: true,
                autoSkip: true,
                lightMode: false,
            },

            init() {
                const saved = localStorage.getItem('player_config');
                if (saved) {
                    try {
                        this.config = { ...this.config, ...JSON.parse(saved) };
                    } catch (e) {}
                }

                this.setupPlayer(window.PLAYER_SERVERS?.[0] ?? null);

                window.addEventListener('keydown', (e) => {
                    if (e.target.tagName === 'TEXTAREA' || e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT') return;

                    if (e.code === 'Space') {
                        e.preventDefault();
                        this.togglePlay();
                    }

                    if (e.key.toLowerCase() === 'j') this.skip(-10);
                    if (e.key.toLowerCase() === 'l') this.skip(10);

                    if (e.key.toLowerCase() === 'n' && window.PLAYER_NEXT_URL) {
                        window.location.href = window.PLAYER_NEXT_URL;
                    }

                    if (e.key.toLowerCase() === 'b' && window.PLAYER_PREV_URL) {
                        window.location.href = window.PLAYER_PREV_URL;
                    }
                });
            },

            saveConfig() {
                localStorage.setItem('player_config', JSON.stringify(this.config));
            },

            setupPlayer(server) {
                if (!server) return;

                this.isEmbed = server.type === 'embed';
                this.embedUrl = this.isEmbed ? server.url : '';

                this.$nextTick(() => {
                    this.video = document.getElementById('videoPlayer');

                    if (!this.video || this.isEmbed) return;

                    const source = this.video.querySelector('source');
                    if (source && server.url) {
                        source.src = server.url;
                        this.video.load();
                    }

                    this.video.addEventListener('play', () => this.playing = true);
                    this.video.addEventListener('pause', () => this.playing = false);

                    this.video.addEventListener('timeupdate', () => {
                        this.checkSkipSegments();
                        this.saveWatchHistory();
                    });

                    this.video.addEventListener('ended', () => {
                        if (this.config.autoNext && window.PLAYER_NEXT_URL) {
                            window.location.href = window.PLAYER_NEXT_URL;
                        }
                    });

                    if (this.config.autoPlay) {
                        this.video.play().catch(() => {});
                    }
                });
            },

            togglePlay() {
                if (!this.video || this.isEmbed) return;

                if (this.video.paused) {
                    this.video.play();
                } else {
                    this.video.pause();
                }
            },

            skip(seconds) {
                if (!this.video || this.isEmbed) return;
                this.video.currentTime = Math.max(0, this.video.currentTime + seconds);
            },

            toggleLight() {
                this.config.lightMode = !this.config.lightMode;
                document.body.classList.toggle('bg-white', this.config.lightMode);
                document.body.classList.toggle('text-black', this.config.lightMode);
                this.saveConfig();
            },

            toggleAutoPlay() {
                this.config.autoPlay = !this.config.autoPlay;
                this.saveConfig();
            },

            toggleAutoNext() {
                this.config.autoNext = !this.config.autoNext;
                this.saveConfig();
            },

            toggleAutoSkip() {
                this.config.autoSkip = !this.config.autoSkip;
                this.saveConfig();
            },

            switchServer(index) {
                if (!window.PLAYER_SERVERS || !window.PLAYER_SERVERS[index]) return;
                this.currentServerIndex = index;
                this.setupPlayer(window.PLAYER_SERVERS[index]);
            },

            toggleList() {
                this.listOpen = !this.listOpen;
                this.reportOpen = false;
            },

            toggleReport() {
                this.reportOpen = !this.reportOpen;
                this.listOpen = false;
            },

            updateList(category) {
                if (!window.PLAYER_IS_AUTH) {
                    window.location.href = window.PLAYER_LOGIN_URL;
                    return;
                }

                fetch('/favorites/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({
                        anime_id: window.PLAYER_ANIME_ID,
                        category: category
                    })
                })
                .then(r => r.json())
                .then(() => {
                    this.favoriteCategory = category;
                    this.listOpen = false;
                })
                .catch(() => {});
            },

            submitReport() {
                if (!window.PLAYER_IS_AUTH) {
                    window.location.href = window.PLAYER_LOGIN_URL;
                    return;
                }

                this.submitting = true;

                fetch('/reports', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({
                        episode_id: window.PLAYER_EPISODE_ID,
                        issue_type: this.reportType,
                        description: this.reportDesc
                    })
                })
                .then(r => r.json())
                .then(() => {
                    this.reportOpen = false;
                    this.reportDesc = '';
                })
                .catch(() => {})
                .finally(() => {
                    this.submitting = false;
                });
            },

            checkSkipSegments() {
                if (!this.video || this.isEmbed) return;

                const current = this.video.currentTime;
                const skip = window.PLAYER_SKIP_TIMES || {};

                this.showSkipIntro = false;
                this.showSkipOutro = false;

                if (skip?.intro_start !== null && skip?.intro_end !== null) {
                    if (current >= skip.intro_start && current <= skip.intro_end) {
                        this.showSkipIntro = true;

                        if (this.config.autoSkip) {
                            this.skipIntro();
                        }
                    }
                }

                if (skip?.outro_start !== null && skip?.outro_end !== null) {
                    if (current >= skip.outro_start && current <= skip.outro_end) {
                        this.showSkipOutro = true;

                        if (this.config.autoSkip) {
                            this.skipOutro();
                        }
                    }
                }
            },

            skipIntro() {
                const skip = window.PLAYER_SKIP_TIMES || {};
                if (!this.video || skip.intro_end == null) return;
                this.video.currentTime = skip.intro_end;
                this.showSkipIntro = false;
            },

            skipOutro() {
                const skip = window.PLAYER_SKIP_TIMES || {};
                if (!this.video || skip.outro_end == null) return;
                this.video.currentTime = skip.outro_end;
                this.showSkipOutro = false;
            },

            saveWatchHistory() {
                if (!window.PLAYER_IS_AUTH || !this.video || this.isEmbed) return;

                clearTimeout(this._historyTimer);
                this._historyTimer = setTimeout(() => {
                    fetch('/watch-history', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({
                            episode_id: window.PLAYER_EPISODE_ID,
                            progress: Math.floor(this.video.currentTime),
                            completed: this.video.duration ? (this.video.currentTime >= this.video.duration - 10) : false
                        })
                    }).catch(() => {});
                }, 1500);
            }
        }
    }
</script>
@endpush
