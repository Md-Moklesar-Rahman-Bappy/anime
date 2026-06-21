@extends('layouts.main')

@section('title', $manga->title)
@section('description', \Illuminate\Support\Str::limit(strip_tags($manga->description ?? ''), 160))
@section('og:title', $manga->title . ' · ' . config('app.name', 'AniKoto'))
@section('og:description', \Illuminate\Support\Str::limit(strip_tags($manga->description ?? ''), 160))
@section('og:image', $manga->banner_url ?? $manga->thumbnail_url)
@section('og:type', 'book')

@section('content')
<div x-data="{
        tab: 'chapters',
        chapterSearch: '',
        sortDesc: true,
        descriptionExpanded: false,
        favorited: @js($isFavorited ?? false),
        toggleFavorite() {
            if (!{{ auth()->check() ? 'true' : 'false' }}) {
                window.location.href = '{{ route('auth.login') }}';
                return;
            }
            const original = this.favorited;
            this.favorited = !original;
            fetch('/manga/favorites/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrf,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ manga_id: {{ $manga->id }} })
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

            {{ $manga->banner_url ?? $manga->thumbnail_url ?? asset('fallback.jpg') }}
                 alt="{{ $manga->title }}"
                 class="absolute inset-0 w-full h-full object-cover"
                 loading="eager">

            {{-- Gradients --}}
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

            {{-- COVER --}}
            <div class="aspect-manga rounded-2xl overflow-hidden shadow-2xl border border-gray-800 bg-gray-900">
                {{ $manga->thumbnail_url ?? $manga->poster_url }}
                     alt="{{ $manga->title }}"
                     class="w-full h-full object-cover"
                     loading="eager">
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="space-y-2">

                {{-- Continue Reading (if user has progress) --}}
                @if(isset($readingProgress) && $readingProgress)
                    {{ route('manga.read', ['slug' => $manga->slug, 'chapter' => $readingProgress->chapter_number]) }}
                       class="btn-primary btn-lg w-full">
                        <i class="fas fa-book-open"></i>
                        Continue Ch. {{ rtrim(rtrim($readingProgress->chapter_number, '0'), '.') }}
                    </a>
                @else
                    {{-- Start reading from Chapter 1 --}}
                    @if($manga->chapters->count())
                        {{ route('manga.read', ['slug' => $manga->slug, 'chapter' => $manga->chapters->first()->number]) }}
                           class="btn-primary btn-lg w-full">
                            <i class="fas fa-book-open"></i> Start Reading
                        </a>
                    @endif
                @endif

                {{-- Latest Chapter (if more than 1) --}}
                @if($manga->chapters->count() > 1)
                    {{ route('manga.read', ['slug' => $manga->slug, 'chapter' => $manga->chapters->last()->number]) }}
                       class="btn-cancel w-full">
                        <i class="fas fa-forward-step"></i>
                        Latest Chapter
                    </a>
                @endif

                {{-- Favorites --}}
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

                        {{ urlencode(url()->current()) }}&text={{ urlencode($manga->title) }}"
                           target="_blank" rel="noopener noreferrer"
                           class="dropdown-link">
                            <i class="fab fa-twitter w-4 text-sky-400"></i> Twitter
                        </a>
                        {{ urlencode(url()->current()) }}"
                           target="_blank" rel="noopener noreferrer"
                           class="dropdown-link">
                            <i class="fab fa-facebook w-4 text-blue-500"></i> Facebook
                        </a>
                        {{ $manga->title }}&body={{ urlencode(url()->current()) }}"
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
                    ['Type',      $manga->type ? strtoupper($manga->type) : null, 'fa-book'],
                    ['Status',    $manga->status ? ucfirst($manga->status) : null, 'fa-circle-info'],
                    ['Chapters',  $manga->chapters_count ?? $manga->chapters->count(), 'fa-list'],
                    ['Year',      $manga->year ?? null, 'fa-calendar'],
                    ['Author',    $manga->author ?? null, 'fa-pen-fancy'],
                    ['Artist',    $manga->artist ?? null, 'fa-palette'],
                    ['Publisher', $manga->publisher ?? null, 'fa-building'],
                    ['Source',    $manga->source ?? null, 'fa-book-bookmark'],
                    ['Views',     ($manga->views ?? null) ? number_format($manga->views) : null, 'fa-eye'],
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

            {{-- TITLE + BADGES --}}
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    @if($manga->type)
                        <span class="badge-indigo uppercase">{{ $manga->type }}</span>
                    @endif
                    @if($manga->status)
                        <span class="badge-success">{{ ucfirst($manga->status) }}</span>
                    @endif
                    @if($manga->year)
                        <span class="badge-gray">{{ $manga->year }}</span>
                    @endif
                    @if($manga->rating ?? $manga->score)
                        <span class="badge-warning">
                            <i class="fas fa-star"></i> {{ number_format($manga->rating ?? $manga->score, 1) }}
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-white leading-tight">
                    {{ $manga->title }}
                </h1>

                @if($manga->title_english ?? null)
                    <p class="text-sm text-gray-400 mt-1">{{ $manga->title_english }}</p>
                @endif
                @if($manga->title_japanese ?? null)
                    <p class="text-sm text-gray-500 mt-0.5">{{ $manga->title_japanese }}</p>
                @endif
            </div>

            {{-- GENRES --}}
            @if($manga->genres->count())
                <div class="flex flex-wrap gap-2">
                    @foreach($manga->genres as $genre)
                        {{ route('manga.genre', $genre->slug) }}-flex items-center px-3 py-1 rounded-full bg-indigo-500/10 text-indigo-300 hover:bg-indigo-500/20 hover:text-indigo-200 text-sm transition">
                            {{ $genre->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- DESCRIPTION --}}
            @if($manga->description ?? null)
                <div class="card p-5">
                    <p class="text-sm text-gray-300 leading-relaxed"
                       :class="!descriptionExpanded && 'clamp-4'">
                        {{ $manga->description }}
                    </p>

                    @if(strlen($manga->description) > 320)
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
                            'chapters' => ['Chapters', 'fa-list', $manga->chapters->count()],
                            'related'  => ['Related', 'fa-link', $related->count() ?? 0],
                            'comments' => ['Comments', 'fa-comments', $manga->comments_count ?? 0],
                        ];
                    @endphp

                    @foreach($tabs as $key => [$label, $icon, $count])
                        <button @click="tab = '{{ $key }}'"
                                :class="tab === '{{ $key }}' ? 'border-indigo-500 text-white' : 'border-transparent text-gray-400 hover:text-white'"
                                class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium border-b-2 transition whitespace-nowrap">
                            <i class="fas {{ $icon }}"></i>
                            {{ $label }}
                            @if($count)
                                <span class="text-xs px-1.5 py-0.5 rounded bg-gray-800 text-gray-400">{{ $count }}</span>
                            @endif
                        </button>
                    @endforeach
                </div>


                {{-- ─────── TAB: CHAPTERS ─────── --}}
                <div x-show="tab === 'chapters'" x-transition class="pt-5">
                    @if($manga->chapters->count())

                        {{-- Search + Sort toolbar --}}
                        <div class="flex flex-wrap items-center gap-3 mb-4">
                            <div class="relative flex-1 min-w-[200px]">
                                <input type="search"
                                       x-model="chapterSearch"
                                       placeholder="Search chapter number or title…"
                                       class="form-input pl-10">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                            </div>

                            <button @click="sortDesc = !sortDesc"
                                    class="btn-cancel btn-sm">
                                <i class="fas" :class="sortDesc ? 'fa-arrow-down-9-1' : 'fa-arrow-up-1-9'"></i>
                                <span x-text="sortDesc ? 'Newest first' : 'Oldest first'"></span>
                            </button>
                        </div>

                        {{-- CHAPTER GRID (huge series — 50+) --}}
                        @if($manga->chapters->count() > 50)
                            @php $sortedChapters = $manga->chapters; @endphp

                            <div class="card p-4">
                                <p class="text-xs text-gray-500 mb-3">Tap a chapter number to read</p>
                                <div class="grid grid-cols-6 sm:grid-cols-8 md:grid-cols-10 lg:grid-cols-12 gap-2"
                                     x-data="{ sortedAsc: false }">
                                    @foreach($sortedChapters as $ch)
                                        <div x-show="chapterSearch === '' || '{{ $ch->number }}'.includes(chapterSearch) || @js(strtolower($ch->title ?? '')).includes(chapterSearch.toLowerCase())">
                                            {{ route('manga.read', ['slug' => $manga->slug, 'chapter' => $ch->number]) }}
                                               class="flex items-center justify-center aspect-square rounded-md bg-[#1f2937] hover:bg-indigo-600 text-sm text-gray-300 hover:text-white font-medium transition"
                                               title="Ch. {{ $ch->number }}{{ $ch->title ? ' — ' . $ch->title : '' }}">
                                                {{ rtrim(rtrim($ch->number, '0'), '.') }}
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            {{-- DETAILED CHAPTER LIST (< 50 chapters) --}}
                            <div class="card divide-y divide-gray-800 max-h-[600px] overflow-y-auto">
                                <template x-for="(_, i) in [1]" :key="i"></template>

                                @foreach($manga->chapters as $ch)
                                    <div x-show="chapterSearch === '' || '{{ $ch->number }}'.includes(chapterSearch) || @js(strtolower($ch->title ?? '')).includes(chapterSearch.toLowerCase())">
                                        {{ route('manga.read', ['slug' => $manga->slug, 'chapter' => $ch->number]) }}
                                           class="flex items-center gap-3 p-3 hover:bg-white/[0.03] transition group">

                                            <div class="w-10 h-10 rounded-lg bg-indigo-500/15 text-indigo-400 flex items-center justify-center text-sm font-bold shrink-0">
                                                {{ rtrim(rtrim($ch->number, '0'), '.') }}
                                            </div>

                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm text-white clamp-1 group-hover:text-indigo-300 transition">
                                                    {{ $ch->title ?: 'Chapter ' . rtrim(rtrim($ch->number, '0'), '.') }}
                                                </p>
                                                <div class="flex items-center gap-3 text-xs text-gray-500 mt-1">
                                                    @if($ch->pages_count)
                                                        <span><i class="fas fa-file"></i> {{ $ch->pages_count }} pages</span>
                                                    @endif
                                                    @if($ch->published_at ?? $ch->created_at)
                                                        <span>{{ ($ch->published_at ?? $ch->created_at)->diffForHumans() }}</span>
                                                    @endif
                                                </div>
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
                            <p class="text-gray-400 text-sm">No chapters available yet.</p>
                        </div>
                    @endif
                </div>


                {{-- ─────── TAB: RELATED ─────── --}}
                <div x-show="tab === 'related'" x-cloak x-transition class="pt-5">
                    @if($related->count())
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            @foreach($related as $rel)
                                {{ route('manga.detail', $rel->slug) }} class="anime-card">
                                    {{ $rel->thumbnail_url ?? $rel->poster_url }}
                                         alt="{{ $rel->title }}"
                                         class="anime-card-img"
                                         loading="lazy">
                                    <div class="anime-card-overlay flex items-end p-3">
                                        <div>
                                            <p class="text-white text-sm font-semibold clamp-2">{{ $rel->title }}</p>
                                            @if($rel->rating ?? $rel->score)
                                                <p class="text-xs text-amber-400 mt-1">⭐ {{ number_format($rel->rating ?? $rel->score, 1) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="card p-10 text-center">
                            <i class="fas fa-link text-3xl text-gray-700 mb-2"></i>
                            <p class="text-gray-400 text-sm">No related manga found.</p>
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