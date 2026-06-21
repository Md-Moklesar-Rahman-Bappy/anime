@extends('layouts.main')

@section('title', $anime->title)
@section('description', \Illuminate\Support\Str::limit(strip_tags($anime->description ?? $anime->synopsis ?? ''), 160))
@section('og:title', $anime->title . ' · ' . config('app.name', 'AniKoto'))
@section('og:description', \Illuminate\Support\Str::limit(strip_tags($anime->description ?? ''), 160))
@section('og:image', $anime->banner_url ?? $anime->thumbnail_url)
@section('og:type', 'video.tv_show')

@section('content')
<div x-data="{
        tab: 'episodes',
        episodeSearch: '',
        descriptionExpanded: false,
        favorited: @js($isFavorited ?? false),
        toggleFavorite() {
            if (!{{ auth()->check() ? 'true' : 'false' }}) {
                window.location.href = '{{ route('auth.login') }}';
                return;
            }
            const original = this.favorited;
            this.favorited = !original;
            fetch('/favorites/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrf,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ anime_id: {{ $anime->id }} })
            })
            .then(r => {
                if (!r.ok) throw new Error();
                window.bus?.emit('toast', {
                    type: 'success',
                    message: original ? 'Removed from favorites' : 'Added to favorites'
                });
            })
            .catch(() => {
                this.favorited = original;
                window.bus?.emit('toast', { type: 'error', message: 'Failed to update favorites' });
            });
        }
    }"
>

    {{-- ╔══════════════════════════════════════════╗
         ║         HERO BANNER                      ║
         ╚══════════════════════════════════════════╝ --}}
    <div class="relative -mx-4 sm:-mx-6 lg:-mx-8 mb-6 sm:mb-10">

        <div class="relative h-[280px] sm:h-[400px] lg:h-[480px] overflow-hidden">

            {{ $anime->banner_url ?? $anime->thumbnail_url }}
                 alt="{{ $anime->title }}"
                 class="absolute inset-0 w-full h-full object-cover"
                 loading="eager"
            >

            {{-- Gradient overlays --}}
            <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0f] via-[#0a0a0f]/70 to-transparent"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-[#0a0a0f]/90 via-transparent to-transparent"></div>
        </div>
    </div>


    {{-- ╔══════════════════════════════════════════╗
         ║         MAIN GRID                        ║
         ╚══════════════════════════════════════════╝ --}}
    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 -mt-32 sm:-mt-40 lg:-mt-48 relative z-10">

        {{-- ─────── LEFT SIDEBAR ─────── --}}
        <aside class="lg:col-span-3 space-y-4">

            {{-- POSTER --}}
            <div class="aspect-poster rounded-2xl overflow-hidden shadow-2xl border border-gray-800 bg-gray-900">
                {{ $anime->thumbnail_url ?? $anime->poster_url }}
                     alt="{{ $anime->title }}"
                     class="w-full h-full object-cover"
                     loading="eager"
                >
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="space-y-2">
                {{ route('watch', $anime->slug) }} class="btn-primary btn-lg w-full">
                    <i class="fas fa-play"></i> Watch Now
                </a>

                <button @click="toggleFavorite()"
                        class="w-full transition"
                        :class="favorited ? 'btn-warning' : 'btn-cancel'">
                    <i class="fas" :class="favorited ? 'fa-bookmark' : 'fa-bookmark-o fa-regular'"></i>
                    <span x-text="favorited ? 'In Favorites' : 'Add to Favorites'"></span>
                </button>

                {{-- Share dropdown --}}
                <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                    <button @click="open = !open" class="btn-outline w-full">
                        <i class="fas fa-share-nodes"></i> Share
                    </button>

                    <div x-show="open" x-cloak x-transition
                         class="absolute z-20 top-full mt-2 right-0 w-full rounded-xl bg-[#0f111a] border border-gray-800 shadow-xl py-1">

                        {{ urlencode(url()->current()) }}&text={{ urlencode($anime->title) }}"
                           target="_blank" rel="noopener noreferrer"
                           class="dropdown-link">
                            <i class="fab fa-twitter w-4 text-sky-400"></i> Twitter
                        </a>
                        {{ urlencode(url()->current()) }}"
                           target="_blank" rel="noopener noreferrer"
                           class="dropdown-link">
                            <i class="fab fa-facebook w-4 text-blue-500"></i> Facebook
                        </a>
                        {{ $anime->title }}&body={{ urlencode(url()->current()) }}"
                           class="dropdown-link">
                            <i class="fas fa-envelope w-4 text-gray-400"></i> Email
                        </a>
                        <button @click="navigator.clipboard.writeText('{{ url()->current() }}'); window.bus?.emit('toast', { type:'success', message:'Link copied!' }); open = false"
                                class="dropdown-link w-full text-left">
                            <i class="fas fa-link w-4 text-indigo-400"></i> Copy Link
                        </button>
                    </div>
                </div>
            </div>

            {{-- META INFO CARD --}}
            <div class="card p-4 text-sm space-y-2">
                @foreach([
                    ['Type',      $anime->type ? strtoupper($anime->type) : null, 'fa-tv'],
                    ['Status',    $anime->status ? ucfirst($anime->status) : null, 'fa-circle-info'],
                    ['Episodes',  $anime->episodes_count ?? null, 'fa-list'],
                    ['Year',      $anime->year ?? null, 'fa-calendar'],
                    ['Duration',  ($anime->duration ?? null) ? $anime->duration . ' min' : null, 'fa-clock'],
                    ['Studio',    $anime->studio ?? null, 'fa-building'],
                    ['Source',    $anime->source ?? null, 'fa-book'],
                    ['Country',   $anime->country ?? null, 'fa-flag'],
                    ['Producers', $anime->producers ?? null, 'fa-user-tie'],
                    ['Licensors', $anime->licensors ?? null, 'fa-certificate'],
                    ['Views',     ($anime->views ?? null) ? number_format($anime->views) : null, 'fa-eye'],
                ] as [$label, $value, $icon])
                    @if($value)
                        <div class="flex items-center justify-between gap-2 pb-2 border-b border-gray-800 last:border-0 last:pb-0">
                            <span class="text-gray-500 text-xs flex items-center gap-2">
                                <i class="fas {{ $icon }} w-3 text-center"></i>
                                {{ $label }}
                            </span>
                            <span class="text-gray-200 text-right truncate max-w-[60%]">{{ $value }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </aside>


        {{-- ─────── RIGHT CONTENT ─────── --}}
        <div class="lg:col-span-9 space-y-6">

            {{-- TITLE + RATING --}}
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    @if($anime->type)
                        <span class="badge-indigo uppercase">{{ $anime->type }}</span>
                    @endif
                    @if($anime->status)
                        <span class="badge-success">{{ ucfirst($anime->status) }}</span>
                    @endif
                    @if($anime->year)
                        <span class="badge-gray">{{ $anime->year }}</span>
                    @endif
                    @if($anime->rating)
                        <span class="badge-warning">
                            <i class="fas fa-star"></i> {{ number_format($anime->rating, 1) }}
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-white leading-tight">
                    {{ $anime->title }}
                </h1>

                @if($anime->title_english && $anime->title_english !== $anime->title)
                    <p class="text-sm text-gray-400 mt-1">{{ $anime->title_english }}</p>
                @endif
                @if($anime->title_japanese ?? null)
                    <p class="text-sm text-gray-500 mt-0.5">{{ $anime->title_japanese }}</p>
                @endif
            </div>

            {{-- GENRES --}}
            @if($anime->genres->count())
                <div class="flex flex-wrap gap-2">
                    @foreach($anime->genres as $genre)
                        {{ route('genre', $genre->slug) }}-flex items-center px-3 py-1 rounded-full bg-indigo-500/10 text-indigo-300 hover:bg-indigo-500/20 hover:text-indigo-200 text-sm transition">
                            {{ $genre->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- DESCRIPTION --}}
            @if($anime->description ?? $anime->synopsis ?? null)
                @php $desc = $anime->description ?? $anime->synopsis; @endphp

                <div class="card p-5">
                    <p class="text-sm text-gray-300 leading-relaxed"
                       :class="!descriptionExpanded && 'clamp-4'"
                       x-ref="descBox">
                        {{ $desc }}
                    </p>

                    @if(strlen($desc) > 320)
                        <button @click="descriptionExpanded = !descriptionExpanded"
                                class="mt-3 text-xs font-semibold text-indigo-400 hover:text-indigo-300 transition">
                            <span x-text="descriptionExpanded ? 'Show less ↑' : 'Read more ↓'"></span>
                        </button>
                    @endif
                </div>
            @endif

            {{-- TABS --}}
            <div>
                <div class="flex gap-1 border-b border-gray-800 overflow-x-auto no-scrollbar">

                    @php
                        $tabs = [
                            'episodes' => ['Episodes', 'fa-play-circle', $anime->episodes->count()],
                            'related'  => ['Related', 'fa-link', $related->count() ?? 0],
                            'comments' => ['Comments', 'fa-comments', $anime->comments_count ?? 0],
                        ];
                    @endphp

                    @foreach($tabs as $key => [$label, $icon, $count])
                        <button
                            @click="tab = '{{ $key }}'"
                            :class="tab === '{{ $key }}' ? 'border-indigo-500 text-white' : 'border-transparent text-gray-400 hover:text-white'"
                            class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium border-b-2 transition whitespace-nowrap"
                        >
                            <i class="fas {{ $icon }}"></i>
                            {{ $label }}
                            @if($count)
                                <span class="text-xs px-1.5 py-0.5 rounded bg-gray-800 text-gray-400">{{ $count }}</span>
                            @endif
                        </button>
                    @endforeach
                </div>

                {{-- ─────── TAB: EPISODES ─────── --}}
                <div x-show="tab === 'episodes'" x-transition class="pt-5">
                    @if($anime->episodes->count())

                        {{-- Episode search (only if 10+) --}}
                        @if($anime->episodes->count() > 10)
                            <div class="relative mb-4">
                                <input type="search"
                                       x-model="episodeSearch"
                                       placeholder="Search episodes by number or title…"
                                       class="form-input pl-10"
                                >
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                            </div>
                        @endif

                        {{-- Episode grid (numbers) for huge series --}}
                        @if($anime->episodes->count() > 50)
                            <div class="grid grid-cols-6 sm:grid-cols-8 md:grid-cols-10 gap-2">
                                @foreach($anime->episodes as $ep)
                                    <div x-show="episodeSearch === '' || '{{ $ep->number }}'.includes(episodeSearch) || @js(strtolower($ep->title ?? '')).includes(episodeSearch.toLowerCase())">
                                        {{ route('watch', ['slug' => $anime->slug, 'ep' => $ep->number]) }}
                                           class="flex items-center justify-center aspect-square rounded-md bg-[#1f2937] hover:bg-indigo-600 text-sm text-gray-300 hover:text-white font-medium transition"
                                           title="Ep {{ $ep->number }}{{ $ep->title ? ' — ' . $ep->title : '' }}">
                                            {{ $ep->number }}
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            {{-- Detailed list (better UX < 50 episodes) --}}
                            <div class="card divide-y divide-gray-800">
                                @foreach($anime->episodes as $ep)
                                    <div x-show="episodeSearch === '' || '{{ $ep->number }}'.includes(episodeSearch) || @js(strtolower($ep->title ?? '')).includes(episodeSearch.toLowerCase())">
                                        {{ route('watch', ['slug' => $anime->slug, 'ep' => $ep->number]) }}
                                           class="flex items-center gap-3 p-3 sm:p-4 hover:bg-white/[0.03] transition group">

                                            {{-- Thumbnail --}}
                                            <div class="aspect-thumb w-24 sm:w-32 rounded-md overflow-hidden bg-gray-900 shrink-0 relative">
                                                @if($ep->thumbnail_url)
                                                    {{ $ep->thumbnail_url }}
                                                         class="w-full h-full object-cover group-hover:scale-105 transition"
                                                         loading="lazy" alt="">
                                                @endif
                                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition">
                                                    <i class="fas fa-play text-white text-xl"></i>
                                                </div>
                                            </div>

                                            {{-- Info --}}
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 mb-1 text-xs">
                                                    <span class="text-indigo-400 font-semibold">EP {{ $ep->number }}</span>
                                                    @if($ep->has_sub)
                                                        <span class="badge-sky">SUB</span>
                                                    @endif
                                                    @if($ep->has_dub)
                                                        <span class="badge-success">DUB</span>
                                                    @endif
                                                    @if($ep->duration ?? null)
                                                        <span class="text-gray-500">{{ $ep->duration }}m</span>
                                                    @endif
                                                </div>

                                                <p class="text-sm text-white clamp-1 group-hover:text-indigo-300 transition">
                                                    {{ $ep->title ?? 'Episode ' . $ep->number }}
                                                </p>

                                                @if($ep->aired_at ?? $ep->created_at)
                                                    <p class="text-xs text-gray-500 mt-0.5">
                                                        {{ ($ep->aired_at ?? $ep->created_at)->diffForHumans() }}
                                                    </p>
                                                @endif
                                            </div>

                                            <i class="fas fa-chevron-right text-gray-600 text-xs shrink-0 group-hover:text-indigo-400 transition"></i>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                    @else
                        <div class="card p-10 text-center">
                            <i class="fas fa-circle-info text-3xl text-gray-700 mb-2"></i>
                            <p class="text-gray-400 text-sm">No episodes available yet.</p>
                        </div>
                    @endif
                </div>

                {{-- ─────── TAB: RELATED ─────── --}}
                <div x-show="tab === 'related'" x-cloak x-transition class="pt-5">
                    @if($related->count())
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            @foreach($related as $rel)
                                {{ route('anime.detail', $rel->slug) }} class="anime-card">
                                    {{ $rel->thumbnail_url ?? $rel->poster_url }}
                                         alt="{{ $rel->title }}"
                                         class="anime-card-img"
                                         loading="lazy">
                                    <div class="anime-card-overlay flex items-end p-3">
                                        <div>
                                            <p class="text-white text-sm font-semibold clamp-2">{{ $rel->title }}</p>
                                            @if($rel->rating)
                                                <p class="text-xs text-amber-400 mt-1">⭐ {{ number_format($rel->rating, 1) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="card p-10 text-center">
                            <i class="fas fa-link text-3xl text-gray-700 mb-2"></i>
                            <p class="text-gray-400 text-sm">No related anime found.</p>
                        </div>
                    @endif
                </div>

                {{-- ─────── TAB: COMMENTS ─────── --}}
                <div x-show="tab === 'comments'" x-cloak x-transition class="pt-5">
                    <div class="card p-6 text-center">
                        <i class="fas fa-comments text-3xl text-gray-700 mb-2"></i>
                        <p class="text-gray-400 text-sm">Comments will be loaded here.</p>
                        <p class="text-xs text-gray-600 mt-1">
                            (Wire up <code class="text-indigo-400">liveComments</code> Alpine component)
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection