@extends('layouts.main')

@section('title', $episode->anime->title . ' - Episode ' . $episode->number)

@push('styles')
<style>
    .player-shell {
        background:#000;border-radius:0.75rem;overflow:hidden;border:1px solid #374151;box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);
    }
    .player-surface {
        position:relative;width:100%;aspect-ratio:16/9;background:#000;
    }
    .player-control-bar {
        display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:0.75rem;padding:0.75rem 1rem;background:#111827;border-top:1px solid #374151;
    }
    .ctrl-group {
        display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;
    }
    .ctrl-btn {
        display:inline-flex;align-items:center;gap:0.5rem;padding:0.5rem 0.75rem;border-radius:0.5rem;background:#1f2937;color:#d1d5db;border:1px solid #374151;font-size:0.875rem;transition:all 0.2s;
    }
    .ctrl-btn.active {
        background:#4f46e5;color:#fff;border-color:#6366f1;
    }
    .ctrl-btn .label {
        display:none;
    }
    @media (min-width:576px) {
        .ctrl-btn .label { display:inline; }
    }
    .server-select {
        background:#1f2937;color:#fff;font-size:0.875rem;border-radius:0.5rem;padding:0.5rem 0.75rem;border:1px solid #374151;
    }
    .server-select:focus {
        outline:none;box-shadow:0 0 0 2px #4f46e5;
    }
    .player-dropdown {
        position:absolute;right:0;top:100%;margin-top:0.5rem;width:16rem;background:#111827;border:1px solid #374151;border-radius:0.75rem;box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);z-index:1050;overflow:hidden;
    }
    .skip-overlay {
        position:absolute;bottom:1rem;right:1rem;z-index:20;display:flex;gap:0.5rem;
    }
    .skip-btn {
        padding:0.5rem 1rem;border-radius:0.5rem;background:#4f46e5;color:#fff;font-weight:500;box-shadow:0 4px 6px rgba(0,0,0,0.3);border:none;cursor:pointer;transition:background 0.2s;
    }
    .skip-btn:hover { background:#6366f1; }
    .section-card {
        background:#111827;border:1px solid #374151;border-radius:0.75rem;padding:1rem;
    }
    .comment-box {
        width:100%;background:#1f2937;color:#fff;border-radius:0.5rem;padding:0.75rem 1rem;border:1px solid #374151;
    }
    .comment-box:focus {
        outline:none;box-shadow:0 0 0 2px #4f46e5;
    }
    .thumb-cover {
        object-fit:cover;border-radius:0.5rem;background:#1f2937;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 py-3" style="max-width:1280px">

    <div class="row">
        <div class="col-lg-8 d-flex flex-column gap-3">

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

                        <iframe
                            x-show="isEmbed"
                            :src="embedUrl"
                            style="width:100%;height:100%;border:0"
                            allowfullscreen
                            allow="autoplay; encrypted-media; picture-in-picture"
                        ></iframe>

                        <div x-show="!isEmbed" style="width:100%;height:100%">
                            <video
                                id="videoPlayer"
                                style="width:100%;height:100%"
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
                        <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:#0a0a0f;color:#6b7280">
                            <div class="text-center">
                                <svg style="width:4rem;height:4rem;margin:0 auto 0.75rem;display:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                <p style="font-size:0.875rem">No video source available for this episode.</p>
                            </div>
                        </div>
                    @endif

                    <div class="skip-overlay" x-show="showSkipIntro || showSkipOutro" x-cloak>
                        <button class="skip-btn" x-show="showSkipIntro" @click="skipIntro()">Skip Intro</button>
                        <button class="skip-btn" x-show="showSkipOutro" @click="skipOutro()">Skip Outro</button>
                    </div>
                </div>

                <div class="player-control-bar">

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

                        <div class="position-relative" @click.outside="listOpen = false">
                            <button class="ctrl-btn" @click="toggleList()">
                                <i class="fa-solid fa-bookmark"></i>
                                <span class="label">List</span>
                            </button>

                            <div class="player-dropdown" x-show="listOpen" x-cloak>
                                <template x-for="cat in categories" :key="cat.value">
                                    <button class="w-100 d-flex align-items-center gap-2 px-3 py-2 text-start" style="background:none;border:none;color:#d1d5db;font-size:0.875rem;transition:background 0.2s"
                                            :class="{ active: favoriteCategory === cat.value }"
                                            @click="updateList(favoriteCategory === cat.value ? null : cat.value)">
                                        <span style="width:1rem;text-align:center;color:#818cf8">
                                            <i class="fa-solid fa-check" x-show="favoriteCategory === cat.value"></i>
                                        </span>
                                        <span x-text="cat.label"></span>
                                    </button>
                                </template>

                                <hr style="border-color:#374151;margin:0.25rem 0">

                                <button class="w-100 d-flex align-items-center gap-2 px-3 py-2 text-start" style="background:none;border:none;color:#d1d5db;font-size:0.875rem;transition:background 0.2s"
                                        :class="{ active: !favoriteCategory }"
                                        @click="updateList(null)">
                                    <span style="width:1rem;text-align:center;color:#818cf8">
                                        <i class="fa-solid fa-check" x-show="!favoriteCategory"></i>
                                    </span>
                                    <span>Not in list</span>
                                </button>
                            </div>
                        </div>

                        <div class="position-relative" @click.outside="reportOpen = false">
                            <button class="ctrl-btn" @click="toggleReport()">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                <span class="label">Report</span>
                            </button>

                            <div class="player-dropdown" x-show="reportOpen" x-cloak>
                                <div style="padding:0.75rem" class="d-flex flex-column gap-2">

                                    <div>
                                        <label class="d-block" style="font-size:0.75rem;color:#6b7280;margin-bottom:0.25rem">Issue type</label>
                                        <select x-model="reportType" class="server-select w-100">
                                            <template x-for="it in issueTypes" :key="it.value">
                                                <option :value="it.value" x-text="it.label"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="d-block" style="font-size:0.75rem;color:#6b7280;margin-bottom:0.25rem">Description</label>
                                        <textarea x-model="reportDesc"
                                                  rows="3"
                                                  class="comment-box"
                                                  style="font-size:0.875rem"
                                                  placeholder="Describe the issue..."></textarea>
                                    </div>

                                    <button @click="submitReport()"
                                            :disabled="submitting"
                                            class="btn d-block w-100"
                                            style="background:#4f46e5;color:#fff;font-size:0.875rem"
                                            x-text="submitting ? 'Submitting...' : 'Submit Report'">
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                <div class="d-flex align-items-center gap-2">
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

            <div class="section-card">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div>
                        <h1 style="font-size:1.5rem;font-weight:600;color:#fff">
                            {{ $anime->title }} — Episode {{ $episode->number }}
                        </h1>

                        @if($episode->title)
                            <p style="color:#9ca3af;font-size:0.875rem;margin-top:0.25rem">{{ $episode->title }}</p>
                        @endif
                    </div>

                    <div class="d-flex flex-wrap gap-1">
                        @if($episode->has_sub)
                            <span style="display:inline-flex;align-items:center;padding:0.25rem 0.625rem;border-radius:0.5rem;font-size:0.75rem;font-weight:500;background:#2563eb;color:#fff">SUB</span>
                        @endif
                        @if($episode->has_dub)
                            <span style="display:inline-flex;align-items:center;padding:0.25rem 0.625rem;border-radius:0.5rem;font-size:0.75rem;font-weight:500;background:#16a34a;color:#fff">DUB</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="section-card">
                <h3 style="font-size:1.125rem;font-weight:600;color:#fff;margin-bottom:0.75rem">Comments</h3>

                @auth
                    <form action="{{ route('comments.store') }}" method="POST" style="margin-bottom:1.25rem">
                        @csrf
                        <input type="hidden" name="episode_id" value="{{ $episode->id }}">

                        <textarea name="body"
                                  rows="3"
                                  class="comment-box"
                                  placeholder="Write a comment..."
                                  required></textarea>

                        <button type="submit"
                                class="btn mt-2"
                                style="background:#4f46e5;color:#fff;font-size:0.875rem">
                            Post Comment
                        </button>
                    </form>
                @else
                    <p style="color:#9ca3af;font-size:0.875rem;margin-bottom:1rem">
                        <a href="{{ route('auth.login') }}" style="color:#818cf8">Login</a>
                        to comment.
                    </p>
                @endauth

                <div class="d-flex flex-column gap-3">
                    @foreach($comments as $comment)
                        <div class="d-flex gap-2">
                            <img
                                src="https://ui-avatars.com/api/?name={{ urlencode($comment->user->name) }}&background=4f46e5&color=fff"
                                style="width:2rem;height:2rem;border-radius:50%"
                                alt="{{ $comment->user->name }}"
                            >

                            <div style="flex:1">
                                <div class="d-flex align-items-center gap-1">
                                    <span style="font-size:0.875rem;font-weight:600;color:#fff">{{ $comment->user->name }}</span>
                                    <span style="font-size:0.75rem;color:#6b7280">{{ $comment->created_at->diffForHumans() }}</span>

                                    @auth
                                        @if(auth()->user()->isSuperAdmin())
                                            <form action="{{ route('comments.destroy', $comment) }}"
                                                  method="POST"
                                                  class="ms-auto"
                                                  onsubmit="return confirm('Delete this comment?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" style="font-size:0.75rem;color:#ef4444;background:none;border:none;cursor:pointer">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    @endauth
                                </div>

                                <p style="color:#d1d5db;font-size:0.875rem;margin-top:0.25rem">{{ $comment->body }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($comments->hasPages())
                    <div class="mt-3">
                        {{ $comments->links() }}
                    </div>
                @endif
            </div>

        </div>

        <div class="col-lg-4 d-flex flex-column gap-3">

            <div class="section-card">
                <h3 style="font-size:1.125rem;font-weight:600;color:#fff;margin-bottom:0.75rem">Episodes</h3>

                <div style="max-height:500px;overflow-y:auto;padding-right:0.25rem" class="d-flex flex-column gap-1">
                    @foreach($anime->episodes as $ep)
                        <a href="{{ route('watch', ['slug' => $anime->slug, 'ep' => $ep->number]) }}"
                           class="d-flex align-items-center gap-2 text-decoration-none" style="padding:0.5rem;border-radius:0.75rem;transition:background 0.2s;{{ $ep->id === $episode->id ? 'background:rgba(79,70,229,0.2);border:1px solid #4f46e5' : '' }}">
                            <img src="{{ $ep->thumbnail_url }}"
                                 style="width:5rem;height:3rem;object-fit:cover;border-radius:0.5rem;background:#1f2937"
                                 alt="Episode {{ $ep->number }}"
                                 loading="lazy">

                            <div style="flex:1;min-width:0">
                                <p style="color:#fff;font-size:0.875rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">Episode {{ $ep->number }}</p>
                                @if($ep->title)
                                    <p style="color:#6b7280;font-size:0.75rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $ep->title }}</p>
                                @endif
                            </div>

                            <div class="d-flex flex-column gap-0">
                                @if($ep->has_sub)
                                    <span style="font-size:0.625rem;color:#60a5fa;font-weight:500">SUB</span>
                                @endif
                                @if($ep->has_dub)
                                    <span style="font-size:0.625rem;color:#4ade80;font-weight:500">DUB</span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="section-card">
                <h3 style="font-size:1.125rem;font-weight:600;color:#fff;margin-bottom:0.75rem">{{ $anime->title }}</h3>
                <p style="color:#9ca3af;font-size:0.875rem">
                    {{ \Illuminate\Support\Str::limit($anime->description, 150) }}
                </p>
                <a href="{{ route('anime.detail', $anime->slug) }}"
                   class="d-inline-block mt-2" style="color:#818cf8;font-size:0.875rem">
                    View Details
                </a>
            </div>

            @if($related->count())
                <div class="section-card">
                    <h3 style="font-size:1.125rem;font-weight:600;color:#fff;margin-bottom:0.75rem">Related</h3>

                    <div class="d-flex flex-column gap-1">
                        @foreach($related as $rel)
                            <a href="{{ route('anime.detail', $rel->slug) }}" class="d-flex align-items-center gap-2 text-decoration-none" style="padding:0.5rem;border-radius:0.75rem;transition:background 0.2s">
                                <img src="{{ $rel->thumbnail_url }}"
                                     style="width:2.5rem;height:3.5rem;object-fit:cover;border-radius:0.5rem;background:#1f2937"
                                     alt="{{ $rel->title }}"
                                     loading="lazy">

                                <div style="flex:1;min-width:0">
                                    <p style="color:#fff;font-size:0.875rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $rel->title }}</p>
                                    <p style="color:#6b7280;font-size:0.75rem">
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
