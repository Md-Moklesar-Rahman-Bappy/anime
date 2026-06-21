@extends('layouts.main')

@section('title', $anime->title . ' · Episode ' . $episode->number)
@section('description', 'Watch ' . $anime->title . ' Episode ' . $episode->number . ' ' . ($episode->title ? '— ' . $episode->title : '') . ' free in HD.')
@section('og:title', $anime->title . ' · Episode ' . $episode->number)
@section('og:image', $episode->thumbnail_url ?? $anime->banner_url ?? $anime->thumbnail_url)
@section('og:type', 'video.episode')

@section('content')
<div class="max-w-[1400px] mx-auto" x-data="watchPage()" x-init="init()">

    {{-- ╔══════════════════════════════════════════╗
         ║         MAIN GRID                        ║
         ╚══════════════════════════════════════════╝ --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

        {{-- ─────── LEFT: PLAYER + INFO ─────── --}}
        <div class="lg:col-span-8 space-y-4">

            {{-- ╔══════════════════════════════════════════╗
                 ║         PLAYER                           ║
                 ╚══════════════════════════════════════════╝ --}}
            <div class="rounded-2xl overflow-hidden border border-gray-800 bg-black shadow-2xl"
                 x-data="player({
                    servers: @js($allServers),
                    isYoutube: @js($isYoutubeInit ?? false),
                    youtubeId: @js($youtubeVideoId ?? null),
                    nextUrl: @js($nextEpisode ? route('watch', ['slug' => $anime->slug, 'ep' => $nextEpisode->number]) : null),
                    prevUrl: @js($prevEpisode ? route('watch', ['slug' => $anime->slug, 'ep' => $prevEpisode->number]) : null),
                    episodeId: {{ $episode->id }},
                    animeId: {{ $anime->id }},
                    isAuth: @js(auth()->check()),
                    loginUrl: '{{ route('auth.login') }}',
                    skipTimes: @js($skipTimes ?? null),
                 })"
                 x-init="init()"
                 @keydown.window.prevent.space="togglePlay()"
                 @keydown.window.j="skip(-10)"
                 @keydown.window.l="skip(10)"
                 @keydown.window.k="togglePlay()"
                 @keydown.window.m="toggleMute()"
                 @keydown.window.f="toggleFullscreen()"
            >

                {{-- VIDEO SURFACE --}}
                <div class="relative aspect-video bg-black group">

                    @if($initialServer)
                        @php
                            $mimeMap = [
                                'mp4'  => 'video/mp4',
                                'webm' => 'video/webm',
                                'm3u8' => 'application/x-mpegURL',
                            ];
                            $mimeType    = $mimeMap[$initialServer['type']] ?? null;
                            $isEmbedInit = $initialServer['type'] === 'embed';
                        @endphp

                        {{-- Embed iframe --}}
                        <iframe x-show="isEmbed"
                                x-cloak
                                :src="embedUrl"
                                class="absolute inset-0 w-full h-full border-0"
                                allowfullscreen
                                allow="autoplay; encrypted-media; picture-in-picture">
                        </iframe>

                        {{-- Native / Plyr video --}}
                        <video x-show="!isEmbed"
                               x-ref="video"
                               class="absolute inset-0 w-full h-full"
                               playsinline
                               preload="metadata"
                               @if($episode->thumbnail_url)
                                   poster="{{ $episode->thumbnail_url }}"
                               @endif
                               @if($isYoutubeInit)
                                   data-plyr-provider="youtube"
                                   data-plyr-embed-id="{{ $youtubeVideoId ?? '' }}"
                               @endif
                        >
                            @if(!$isYoutubeInit && !$isEmbedInit)
                                <source src="{{ $initialServer['url'] }}"
                                        @if($mimeType) type="{{ $mimeType }}" @endif>
                            @endif
                        </video>

                        {{-- Skip buttons --}}
                        <div class="absolute bottom-16 right-4 z-10 flex gap-2"
                             x-show="showSkipIntro || showSkipOutro"
                             x-cloak
                             x-transition>
                            <button x-show="showSkipIntro"
                                    @click="skipIntro()"
                                    class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg shadow-xl font-semibold text-sm transition flex items-center gap-2">
                                <i class="fas fa-forward"></i>
                                Skip Intro
                            </button>
                            <button x-show="showSkipOutro"
                                    @click="skipOutro()"
                                    class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg shadow-xl font-semibold text-sm transition flex items-center gap-2">
                                <i class="fas fa-forward-step"></i>
                                Next Episode
                            </button>
                        </div>

                        {{-- "Next Episode" countdown overlay --}}
                        <div x-show="showNextCountdown"
                             x-cloak
                             x-transition.opacity
                             class="absolute inset-0 z-20 flex items-center justify-center bg-black/80 backdrop-blur-sm">
                            <div class="text-center max-w-md p-6">
                                <p class="text-sm text-gray-400 uppercase tracking-wider mb-2">Next Episode</p>

                                @if($nextEpisode)
                                    <h3 class="text-xl font-bold text-white mb-1">
                                        Episode {{ $nextEpisode->number }}
                                    </h3>
                                    @if($nextEpisode->title)
                                        <p class="text-sm text-gray-400 mb-5">{{ $nextEpisode->title }}</p>
                                    @endif
                                @endif

                                <p class="text-3xl font-black text-indigo-400 mb-5" x-text="`Playing in ${countdownSeconds}s`"></p>

                                <div class="flex justify-center gap-2">
                                    <button @click="cancelNextCountdown()" class="btn-cancel">
                                        Cancel
                                    </button>
                                    <button @click="playNextNow()" class="btn-primary">
                                        <i class="fas fa-play"></i> Play Now
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- NO VIDEO SOURCE FALLBACK --}}
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-500 bg-[#0a0a0f]">
                            <i class="fas fa-video-slash text-5xl mb-3"></i>
                            <p class="text-sm">No video source available for this episode.</p>
                            <p class="text-xs text-gray-600 mt-1">Try a different server or report this issue.</p>
                        </div>
                    @endif
                </div>

                {{-- CONTROL BAR --}}
                <div class="bg-[#0f111a] border-t border-gray-800 px-3 py-2 flex flex-wrap items-center justify-between gap-2">

                    {{-- LEFT GROUP: playback --}}
                    <div class="flex items-center gap-1">
                        <button @click="togglePlay()"
                                class="ctrl-btn"
                                title="Play / Pause (Space)">
                            <i class="fas" :class="playing ? 'fa-pause' : 'fa-play'"></i>
                            <span class="hidden sm:inline" x-text="playing ? 'Pause' : 'Play'"></span>
                        </button>

                        <button @click="skip(-10)" class="ctrl-btn" title="Rewind 10s (J)">
                            <i class="fas fa-backward"></i>
                            <span class="hidden sm:inline">-10s</span>
                        </button>

                        <button @click="skip(10)" class="ctrl-btn" title="Forward 10s (L)">
                            <i class="fas fa-forward"></i>
                            <span class="hidden sm:inline">+10s</span>
                        </button>

                        <button @click="toggleTheater()"
                                class="ctrl-btn"
                                :class="config.theater && 'active'"
                                title="Theater mode (T)">
                            <i class="fas fa-tv"></i>
                            <span class="hidden md:inline">Theater</span>
                        </button>
                    </div>

                    {{-- CENTER GROUP: auto features --}}
                    <div class="flex items-center gap-1">
                        <button @click="toggle('autoPlay')"
                                class="ctrl-btn"
                                :class="config.autoPlay && 'active'">
                            <i class="fas" :class="config.autoPlay ? 'fa-check-square' : 'fa-square'"></i>
                            <span class="hidden md:inline">Auto Play</span>
                        </button>

                        <button @click="toggle('autoNext')"
                                class="ctrl-btn"
                                :class="config.autoNext && 'active'">
                            <i class="fas" :class="config.autoNext ? 'fa-check-square' : 'fa-square'"></i>
                            <span class="hidden md:inline">Auto Next</span>
                        </button>

                        <button @click="toggle('autoSkip')"
                                class="ctrl-btn"
                                :class="config.autoSkip && 'active'">
                            <i class="fas" :class="config.autoSkip ? 'fa-check-square' : 'fa-square'"></i>
                            <span class="hidden md:inline">Auto Skip</span>
                        </button>
                    </div>

                    {{-- RIGHT GROUP: nav + tools --}}
                    <div class="flex items-center gap-1">
                        @if($prevEpisode)
                            {{ route('watch', ['slug' => $anime->slug, 'ep' => $prevEpisode->number]) }}-btn" title="Previous (B)">
                                <i class="fas fa-backward-step"></i>
                                <span class="hidden sm:inline">Prev</span>
                            </a>
                        @endif

                        @if($nextEpisode)
                            {{ route('watch', ['slug' => $anime->slug, 'ep' => $nextEpisode->number]) }}-btn" title="Next (N)">
                                <span class="hidden sm:inline">Next</span>
                                <i class="fas fa-forward-step"></i>
                            </a>
                        @endif

                        @if(count($allServers) > 1)
                            <select @change="switchServer($event.target.selectedIndex)"
                                    class="form-select text-xs py-1.5 ml-1">
                                @foreach($allServers as $i => $s)
                                    <option value="{{ $s['server_id'] }}" @selected($i === 0)>
                                        {{ $s['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        @endif

                        {{-- List dropdown --}}
                        <div class="relative" @click.outside="listOpen = false">
                            <button @click="listOpen = !listOpen; reportOpen = false"
                                    class="ctrl-btn"
                                    :class="favoriteCategory && 'active'"
                                    title="Add to list">
                                <i class="fas fa-bookmark"></i>
                                <span class="hidden lg:inline">List</span>
                            </button>

                            <div x-show="listOpen"
                                 x-cloak
                                 x-transition
                                 class="absolute right-0 top-full mt-2 w-56 z-20 rounded-xl bg-[#0f111a] border border-gray-800 shadow-xl py-1">

                                @php
                                    $listCategories = [
                                        ['watching',      'Watching',       'fa-play',         'text-blue-400'],
                                        ['completed',     'Completed',      'fa-circle-check', 'text-emerald-400'],
                                        ['plan_to_watch', 'Plan to Watch',  'fa-clock',        'text-amber-400'],
                                        ['on_hold',       'On Hold',        'fa-pause',        'text-orange-400'],
                                        ['dropped',       'Dropped',        'fa-circle-xmark', 'text-red-400'],
                                    ];
                                @endphp

                                @foreach($listCategories as [$value, $label, $icon, $color])
                                    <button @click="updateList(favoriteCategory === '{{ $value }}' ? null : '{{ $value }}')"
                                            class="dropdown-link justify-between"
                                            :class="favoriteCategory === '{{ $value }}' && 'bg-indigo-600/20 text-white'">
                                        <span class="flex items-center gap-2">
                                            <i class="fas {{ $icon }} {{ $color }} w-4"></i>
                                            {{ $label }}
                                        </span>
                                        <i class="fas fa-check text-indigo-400 text-xs"
                                           x-show="favoriteCategory === '{{ $value }}'"></i>
                                    </button>
                                @endforeach

                                <div class="border-t border-gray-800 my-1"></div>

                                <button @click="updateList(null)"
                                        class="dropdown-link text-red-400 hover:text-red-300"
                                        x-show="favoriteCategory">
                                    <i class="fas fa-times w-4"></i>
                                    Remove from list
                                </button>
                            </div>
                        </div>

                        {{-- Report dropdown --}}
                        <div class="relative" @click.outside="reportOpen = false">
                            <button @click="reportOpen = !reportOpen; listOpen = false" class="ctrl-btn" title="Report issue">
                                <i class="fas fa-triangle-exclamation"></i>
                                <span class="hidden lg:inline">Report</span>
                            </button>

                            <div x-show="reportOpen"
                                 x-cloak
                                 x-transition
                                 class="absolute right-0 top-full mt-2 w-72 z-20 rounded-xl bg-[#0f111a] border border-gray-800 shadow-xl p-3 space-y-3">

                                <div>
                                    <label class="form-label text-xs">Issue type</label>
                                    <select x-model="reportType" class="form-select text-sm">
                                        <option value="broken_video">Broken video</option>
                                        <option value="wrong_episode">Wrong episode</option>
                                        <option value="subtitle_issue">Subtitle issue</option>
                                        <option value="audio_issue">Audio issue</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="form-label text-xs">Description (optional)</label>
                                    <textarea x-model="reportDesc"
                                              rows="3"
                                              class="form-textarea text-sm"
                                              placeholder="Describe the issue..."></textarea>
                                </div>

                                <button @click="submitReport()"
                                        :disabled="submitting"
                                        class="btn-primary w-full">
                                    <span x-text="submitting ? 'Submitting...' : 'Submit Report'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ╔══════════════════════════════════════════╗
                 ║         EPISODE PICKER + NAV             ║
                 ╚══════════════════════════════════════════╝ --}}
            <div class="flex flex-wrap items-center justify-between gap-3">

                <div class="flex items-center gap-2">
                    @if($prevEpisode)
                        {{ route('watch', ['slug' => $anime->slug, 'ep' => $prevEpisode->number]) }} class="btn-cancel btn-sm">
                            <i class="fas fa-arrow-left"></i>
                            <span class="hidden sm:inline">Prev</span>
                        </a>
                    @endif

                    @if($nextEpisode)
                        {{ route('watch', ['slug' => $anime->slug, 'ep' => $nextEpisode->number]) }} class="btn-primary btn-sm">
                            <span class="hidden sm:inline">Next</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    @endif
                </div>

                <select onchange="window.location.href = this.value" class="form-select text-sm">
                    @foreach($anime->episodes as $ep)
                        <option value="{{ route('watch', ['slug' => $anime->slug, 'ep' => $ep->number]) }}"
                                @selected($ep->id === $episode->id)>
                            Episode {{ $ep->number }}{{ $ep->title ? ' — ' . Str::limit($ep->title, 40) : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ╔══════════════════════════════════════════╗
                 ║         EPISODE INFO                     ║
                 ╚══════════════════════════════════════════╝ --}}
            <div class="card p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wider text-gray-500 mb-1">Now Watching</p>
                        <h1 class="text-xl sm:text-2xl font-bold text-white">
                            {{ $anime->title }}
                        </h1>
                        <p class="text-sm text-gray-400 mt-1">
                            Episode {{ $episode->number }}{{ $episode->title ? ' — ' . $episode->title : '' }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-1">
                        @if($episode->has_sub)
                            <span class="badge-sky">SUB</span>
                        @endif
                        @if($episode->has_dub)
                            <span class="badge-success">DUB</span>
                        @endif
                    </div>
                </div>

                {{-- Keyboard shortcuts hint --}}
                <details class="mt-4 group">
                    <summary class="text-xs text-gray-500 hover:text-gray-300 cursor-pointer flex items-center gap-2 select-none">
                        <i class="fas fa-keyboard"></i>
                        Keyboard shortcuts
                        <i class="fas fa-chevron-down text-[10px] group-open:rotate-180 transition"></i>
                    </summary>
                    <div class="mt-2 grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs text-gray-400">
                        <div><kbd class="kbd">Space</kbd> Play/Pause</div>
                        <div><kbd class="kbd">J</kbd> -10s</div>
                        <div><kbd class="kbd">L</kbd> +10s</div>
                        <div><kbd class="kbd">M</kbd> Mute</div>
                        <div><kbd class="kbd">F</kbd> Fullscreen</div>
                        <div><kbd class="kbd">T</kbd> Theater mode</div>
                        <div><kbd class="kbd">N</kbd> Next ep</div>
                        <div><kbd class="kbd">B</kbd> Prev ep</div>
                    </div>
                </details>
            </div>

            {{-- ╔══════════════════════════════════════════╗
                 ║         COMMENTS                         ║
                 ╚══════════════════════════════════════════╝ --}}
            <div class="card p-5">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-comments text-indigo-400"></i>
                    Comments
                    <span class="text-sm text-gray-500 font-normal">({{ $comments->total() }})</span>
                </h3>

                @auth
                    {{ route('comments.store') }} method="POST" class="mb-5 space-y-2">
                        @csrf
                        <input type="hidden" name="episode_id" value="{{ $episode->id }}">
                        <textarea name="body"
                                  rows="3"
                                  required
                                  class="form-textarea"
                                  placeholder="Share your thoughts about this episode..."></textarea>
                        <div class="flex justify-end">
                            <button type="submit" class="btn-primary btn-sm">
                                <i class="fas fa-paper-plane"></i>
                                Post Comment
                            </button>
                        </div>
                    </form>
                @else
                    <div class="mb-5 rounded-lg border border-gray-800 bg-gray-900/50 p-4 text-center">
                        <p class="text-sm text-gray-400">
                            <a href="{{ route('auth.login') }}" class="text-indigo-400 hover:text-indigo-300">Login</a>
                            to join the discussion.
                        </p>
                    </div>
                @endauth

                <div class="space-y-4">
                    @forelse($comments as $comment)
                        <div class="flex gap-3">
                            ={{ urlencode($comment->user->name) }}&background=6366f1&color=fff&size=64"
                                 class="w-9 h-9 rounded-full shrink-0"
                                 alt="{{ $comment->user->name }}">

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-sm font-semibold text-white">{{ $comment->user->name }}</span>
                                    <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>

                                    @auth
                                        @if(auth()->user()->isSuperAdmin() || auth()->id() === $comment->user_id)
                                            {{ route('comments.destroy', $comment) }}"
                                                  method="POST" class="ml-auto"
                                                  onsubmit="return confirm('Delete this comment?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="text-xs text-red-400 hover:text-red-300 transition">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    @endauth
                                </div>

                                <p class="text-sm text-gray-300 mt-1 leading-relaxed whitespace-pre-line">
                                    {{ $comment->body }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <i class="fas fa-comments text-3xl text-gray-700 mb-2"></i>
                            <p class="text-sm text-gray-500">No comments yet. Be the first to comment!</p>
                        </div>
                    @endforelse
                </div>

                @if($comments->hasPages())
                    <div class="mt-5">
                        {{ $comments->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- ─────── RIGHT: EPISODES + INFO ─────── --}}
        <aside class="lg:col-span-4 space-y-4">

            {{-- ╔══════════════════════════════════════════╗
                 ║         EPISODE LIST                     ║
                 ╚══════════════════════════════════════════╝ --}}
            <div class="card p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-base font-semibold text-white flex items-center gap-2">
                        <i class="fas fa-list text-indigo-400"></i>
                        Episodes
                    </h3>
                    <span class="text-xs text-gray-500">{{ $anime->episodes->count() }} total</span>
                </div>

                <div class="max-h-[500px] overflow-y-auto space-y-1 pr-1" x-ref="episodeList">
                    @foreach($anime->episodes as $ep)
                        @php $isCurrent = $ep->id === $episode->id; @endphp

                        {{ route('watch', ['slug' => $anime->slug, 'ep' => $ep->number]) }}
                           @if($isCurrent) data-current x-init="$nextTick(() => $el.scrollIntoView({ block: 'center', behavior: 'instant' }))" @endif
                           class="flex items-center gap-2 p-2 rounded-lg transition group
                                  {{ $isCurrent
                                      ? 'bg-indigo-600/20 border border-indigo-600/40'
                                      : 'hover:bg-white/[0.03] border border-transparent' }}">

                            <div class="aspect-thumb w-20 rounded-md overflow-hidden bg-gray-900 shrink-0 relative">
                                @if($ep->thumbnail_url)
                                    {{ $ep->thumbnail_url }}
                                         class="w-full h-full object-cover group-hover:scale-105 transition"
                                         loading="lazy" alt="">
                                @endif
                                @if($isCurrent)
                                    <div class="absolute inset-0 bg-indigo-600/30 flex items-center justify-center">
                                        <i class="fas fa-play text-white text-xs"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium clamp-1 {{ $isCurrent ? 'text-white' : 'text-gray-300 group-hover:text-white' }}">
                                    Episode {{ $ep->number }}
                                </p>
                                @if($ep->title)
                                    <p class="text-xs text-gray-500 clamp-1">{{ $ep->title }}</p>
                                @endif
                            </div>

                            <div class="flex flex-col gap-0.5 shrink-0">
                                @if($ep->has_sub)
                                    <span class="text-[9px] font-bold text-sky-400">SUB</span>
                                @endif
                                @if($ep->has_dub)
                                    <span class="text-[9px] font-bold text-emerald-400">DUB</span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- ╔══════════════════════════════════════════╗
                 ║         ABOUT THIS ANIME                 ║
                 ╚══════════════════════════════════════════╝ --}}
            <div class="card p-4">
                <div class="flex gap-3 mb-3">
                    {{ $anime->thumbnail_url ?? $anime->poster_url }}
                         class="aspect-poster w-16 rounded-md object-cover bg-gray-900 shrink-0"
                         alt="{{ $anime->title }}" loading="lazy">

                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wider text-gray-500">About</p>
                        <p class="text-sm font-semibold text-white clamp-2">{{ $anime->title }}</p>
                        <div class="flex items-center gap-2 text-xs text-gray-500 mt-1">
                            @if($anime->type)<span class="uppercase">{{ $anime->type }}</span>@endif
                            @if($anime->year)<span>•</span><span>{{ $anime->year }}</span>@endif
                            @if($anime->rating)
                                <span>•</span>
                                <span class="text-amber-400">⭐ {{ number_format($anime->rating, 1) }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if($anime->description)
                    <p class="text-xs text-gray-400 clamp-3 leading-relaxed">
                        {{ $anime->description }}
                    </p>
                @endif

                {{ route('anime.detail', $anime->slug) }} class="inline-flex items-center gap-1 mt-3 text-xs text-indigo-400 hover:text-indigo-300 transition">
                    View full details <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            {{-- ╔══════════════════════════════════════════╗
                 ║         RELATED ANIME                    ║
                 ╚══════════════════════════════════════════╝ --}}
            @if($related->count())
                <div class="card p-4">
                    <h3 class="text-base font-semibold text-white mb-3 flex items-center gap-2">
                        <i class="fas fa-link text-pink-400"></i>
                        Related
                    </h3>

                    <div class="space-y-2">
                        @foreach($related as $rel)
                            {{ route('anime.detail', $rel->slug) }}
                               class="flex items-center gap-2 p-2 -m-2 rounded-lg hover:bg-white/[0.03] transition group">

                                <div class="aspect-poster w-10 rounded overflow-hidden bg-gray-900 shrink-0">
                                    {{ $rel->thumbnail_url ?? $rel->poster_url }}
                                         class="w-full h-full object-cover group-hover:scale-105 transition"
                                         alt="" loading="lazy">
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="text-sm text-gray-200 clamp-1 group-hover:text-white">
                                        {{ $rel->title }}
                                    </p>
                                    <div class="flex items-center gap-1 text-xs text-gray-500">
                                        @if($rel->type)<span>{{ $rel->type }}</span>@endif
                                        @if($rel->year)<span>• {{ $rel->year }}</span>@endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

        </aside>
    </div>
</div>
@endsection

@push('head')
<style>
    [x-cloak] { display: none !important; }

    /* Control buttons */
    .ctrl-btn {
        @apply inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md
               bg-gray-800/60 hover:bg-gray-700 text-gray-300 hover:text-white
               text-xs font-medium transition border border-transparent
               whitespace-nowrap;
    }
    .ctrl-btn.active {
        @apply bg-indigo-600/30 border-indigo-500/50 text-white;
    }

    /* Keyboard hint */
    .kbd {
        @apply px-1.5 py-0.5 text-[10px] font-mono bg-gray-800 border border-gray-700 rounded text-gray-300;
    }

    /* Theater mode */
    body.theater-mode header,
    body.theater-mode footer,
    body.theater-mode aside { display: none !important; }
    body.theater-mode main { padding: 0 !important; max-width: none !important; }
</style>
@endpush