@extends('layouts.main')

@section('title', $manga->title . ' · Ch. ' . rtrim(rtrim($chapter->number, '0'), '.'))
@section('description', 'Read ' . $manga->title . ' Chapter ' . rtrim(rtrim($chapter->number, '0'), '.') . ' free on ' . config('app.name', 'AniKoto'))

@php
    $chapterNumber = rtrim(rtrim($chapter->number, '0'), '.');

    // Pre-build pages array for clean JSON
    $pagesArray = $chapter->pages->map(fn($p) =>
        str_starts_with($p->image_path, 'http')
            ? $p->image_path
            : asset('storage/' . $p->image_path)
    )->values();
@endphp

@push('head')
    <style>
        /* Hide header/footer in immersive mode */
        body.reader-immersive header,
        body.reader-immersive footer { display: none !important; }
        body.reader-immersive main { padding: 0 !important; max-width: none !important; }
    </style>
@endpush

@section('content')
<div
    x-data="mangaReader({
        chapterId: {{ $chapter->id }},
        startPage: {{ $bookmark->page_number ?? 1 }},
        pages: @js($pagesArray),
        chapterUrl: '{{ route('manga.read', ['slug' => $manga->slug, 'chapter' => 'PLACEHOLDER']) }}'
    })"
    x-init="init()"
    @keydown.left.window="prev()"
    @keydown.right.window="next()"
    @keydown.up.window.prevent="if (mode === 'vertical') return; prev()"
    @keydown.down.window.prevent="if (mode === 'vertical') return; next()"
    @keydown.f.window="toggleFullscreen()"
    @keydown.escape.window="settingsOpen = false; jumperOpen = false"
    class="-mx-4 sm:-mx-6 lg:-mx-8 -my-6 min-h-screen bg-black text-white"
>

    {{-- ╔══════════════════════════════════════════╗
         ║         TOP BAR (sticky)                 ║
         ╚══════════════════════════════════════════╝ --}}
    <header class="sticky top-0 z-40 bg-[#0a0a0f]/95 backdrop-blur-md border-b border-gray-800">
        <div class="max-w-screen-2xl mx-auto px-3 sm:px-4 h-14 flex items-center justify-between gap-3">

            {{-- LEFT: Back + Title --}}
            <div class="flex items-center gap-3 min-w-0">
                {{ route('manga.detail', $manga->slug) }}
                   class="shrink-0 w-9 h-9 rounded-full bg-gray-800/60 hover:bg-gray-700 flex items-center justify-center text-emerald-400 transition"
                   title="Back to manga details">
                    <i class="fas fa-arrow-left"></i>
                </a>

                <div class="min-w-0 hidden sm:block">
                    <p class="text-sm font-semibold text-white clamp-1">{{ $manga->title }}</p>
                    <p class="text-xs text-gray-400">
                        Ch. {{ $chapterNumber }}{{ $chapter->title ? ' · ' . $chapter->title : '' }}
                    </p>
                </div>
            </div>

            {{-- RIGHT: Tools --}}
            <div class="flex items-center gap-1 sm:gap-2">

                {{-- Page indicator --}}
                <button @click="jumperOpen = !jumperOpen"
                        class="text-xs text-gray-300 hover:text-white px-2 py-1 rounded bg-gray-800/60 hover:bg-gray-700 transition font-mono">
                    <span x-text="currentPage"></span>
                    <span class="text-gray-500">/</span>
                    <span x-text="totalPages"></span>
                </button>

                {{-- Chapter dropdown --}}
                <select @change="goToChapter($event.target.value)"
                        class="form-select text-xs py-1.5 w-32 sm:w-44">
                    @foreach($allChapters as $ac)
                        @php $acNum = rtrim(rtrim($ac->number, '0'), '.'); @endphp
                        <option value="{{ $ac->number }}" {{ $ac->id == $chapter->id ? 'selected' : '' }}>
                            Ch. {{ $acNum }}{{ $ac->title ? ' — ' . Str::limit($ac->title, 20) : '' }}
                        </option>
                    @endforeach
                </select>

                {{-- Settings button --}}
                <button @click="settingsOpen = !settingsOpen"
                        class="w-9 h-9 rounded-full bg-gray-800/60 hover:bg-gray-700 flex items-center justify-center text-gray-300 hover:text-white transition"
                        title="Reader settings (S)">
                    <i class="fas fa-cog"></i>
                </button>

                {{-- Fullscreen --}}
                <button @click="toggleFullscreen()"
                        class="hidden sm:flex w-9 h-9 rounded-full bg-gray-800/60 hover:bg-gray-700 items-center justify-center text-gray-300 hover:text-white transition"
                        title="Fullscreen (F)">
                    <i class="fas fa-expand" x-show="!isFullscreen"></i>
                    <i class="fas fa-compress" x-show="isFullscreen" x-cloak></i>
                </button>

                {{-- Bookmark saved indicator --}}
                <span x-show="saving" x-cloak
                      class="hidden sm:inline-flex items-center gap-1 text-xs text-emerald-400">
                    <i class="fas fa-bookmark"></i> Saved
                </span>
            </div>
        </div>

        {{-- Progress bar --}}
        <div class="h-0.5 bg-gray-900">
            <div class="h-full bg-emerald-500 transition-all duration-200"
                 :style="`width: ${(currentPage / totalPages) * 100}%`"></div>
        </div>
    </header>


    {{-- ╔══════════════════════════════════════════╗
         ║         SETTINGS DRAWER                  ║
         ╚══════════════════════════════════════════╝ --}}
    <div x-show="settingsOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         @click.outside="settingsOpen = false"
         class="fixed top-16 right-3 z-30 w-80 max-w-[calc(100vw-1.5rem)] rounded-xl bg-[#0f111a] border border-gray-800 shadow-2xl p-4 space-y-4">

        <p class="text-sm font-semibold text-white border-b border-gray-800 pb-2">
            <i class="fas fa-sliders-h text-emerald-400 mr-1"></i> Reader Settings
        </p>

        {{-- Mode --}}
        <div>
            <p class="form-label">Reading Mode</p>
            <div class="grid grid-cols-3 gap-2">
                <button @click="setMode('single')"
                        :class="mode === 'single' ? 'bg-emerald-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-white'"
                        class="px-2 py-2 rounded text-xs transition flex flex-col items-center gap-1">
                    <i class="fas fa-file"></i>
                    <span>Single</span>
                </button>
                <button @click="setMode('double')"
                        :class="mode === 'double' ? 'bg-emerald-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-white'"
                        class="px-2 py-2 rounded text-xs transition flex flex-col items-center gap-1">
                    <i class="fas fa-book-open"></i>
                    <span>Double</span>
                </button>
                <button @click="setMode('vertical')"
                        :class="mode === 'vertical' ? 'bg-emerald-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-white'"
                        class="px-2 py-2 rounded text-xs transition flex flex-col items-center gap-1">
                    <i class="fas fa-arrows-up-down"></i>
                    <span>Vertical</span>
                </button>
            </div>
            <p class="form-hint">Vertical = best for Manhwa / Manhua</p>
        </div>

        {{-- Fit --}}
        <div x-show="mode !== 'vertical'">
            <p class="form-label">Page Fit</p>
            <div class="grid grid-cols-3 gap-2">
                @foreach(['width' => 'Width', 'height' => 'Height', 'original' => 'Original'] as $key => $label)
                    <button @click="setFit('{{ $key }}')"
                            :class="fit === '{{ $key }}' ? 'bg-emerald-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-white'"
                            class="px-2 py-1.5 rounded text-xs transition">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Direction --}}
        <div x-show="mode !== 'vertical'">
            <p class="form-label">Direction</p>
            <div class="grid grid-cols-2 gap-2">
                <button @click="setDirection('ltr')"
                        :class="direction === 'ltr' ? 'bg-emerald-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-white'"
                        class="px-2 py-1.5 rounded text-xs transition">
                    Left → Right
                </button>
                <button @click="setDirection('rtl')"
                        :class="direction === 'rtl' ? 'bg-emerald-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-white'"
                        class="px-2 py-1.5 rounded text-xs transition">
                    Right → Left
                </button>
            </div>
        </div>

        {{-- Keyboard shortcuts --}}
        <div class="text-xs text-gray-500 space-y-1 pt-2 border-t border-gray-800">
            <p class="text-gray-400 font-semibold mb-1">Keyboard:</p>
            <p>← → arrows = navigate pages</p>
            <p>F = fullscreen</p>
            <p>ESC = close menus</p>
        </div>
    </div>


    {{-- ╔══════════════════════════════════════════╗
         ║         PAGE JUMPER POPUP                ║
         ╚══════════════════════════════════════════╝ --}}
    <div x-show="jumperOpen"
         x-cloak
         x-transition
         @click.outside="jumperOpen = false"
         class="fixed top-16 left-1/2 -translate-x-1/2 z-30 w-72 rounded-xl bg-[#0f111a] border border-gray-800 shadow-2xl p-4">

        <p class="text-sm font-semibold text-white mb-3">
            <i class="fas fa-rocket text-emerald-400 mr-1"></i> Jump to Page
        </p>

        <input type="number"
               x-model.number="jumpInput"
               :min="1" :max="totalPages"
               @keydown.enter="jumpTo(jumpInput); jumperOpen = false"
               class="form-input"
               :placeholder="`1 - ${totalPages}`"
               autofocus>

        <button @click="jumpTo(jumpInput); jumperOpen = false"
                class="btn-success w-full mt-3">
            Go to Page <span x-text="jumpInput"></span>
        </button>
    </div>


    {{-- ╔══════════════════════════════════════════╗
         ║         READER CONTENT                   ║
         ╚══════════════════════════════════════════╝ --}}
    <main class="relative">

        {{-- ── SINGLE PAGE MODE ── --}}
        <template x-if="mode === 'single'">
            <div class="min-h-[calc(100vh-3.5rem)] flex items-center justify-center p-2 sm:p-4 relative">

                {{-- Click zones (tap to navigate) --}}
                <button @click="direction === 'rtl' ? next() : prev()"
                        class="absolute left-0 top-0 bottom-0 w-1/3 z-10 cursor-w-resize"
                        aria-label="Previous page"></button>
                <button @click="direction === 'rtl' ? prev() : next()"
                        class="absolute right-0 top-0 bottom-0 w-1/3 z-10 cursor-e-resize"
                        aria-label="Next page"></button>

                <template x-for="(page, index) in pages" :key="index">
                    <img
                        x-show="currentPage === index + 1"
                        :src="page"
                        :alt="`Page ${index + 1}`"
                        :class="{
                            'max-w-full max-h-[calc(100vh-3.5rem)]': fit === 'height',
                            'w-full max-w-3xl': fit === 'width',
                            'max-w-none': fit === 'original'
                        }"
                        class="select-none h-auto"
                        loading="lazy"
                        draggable="false"
                    >
                </template>
            </div>
        </template>

        {{-- ── DOUBLE PAGE MODE ── --}}
        <template x-if="mode === 'double'">
            <div class="min-h-[calc(100vh-3.5rem)] flex items-center justify-center p-2 sm:p-4 relative">

                <button @click="direction === 'rtl' ? next2() : prev2()"
                        class="absolute left-0 top-0 bottom-0 w-1/4 z-10 cursor-w-resize"></button>
                <button @click="direction === 'rtl' ? prev2() : next2()"
                        class="absolute right-0 top-0 bottom-0 w-1/4 z-10 cursor-e-resize"></button>

                <div class="flex gap-1 max-h-[calc(100vh-3.5rem)]" :class="direction === 'rtl' && 'flex-row-reverse'">
                    <img :src="pages[currentPage - 1]"
                         :alt="`Page ${currentPage}`"
                         class="max-h-[calc(100vh-3.5rem)] w-auto select-none"
                         loading="lazy" draggable="false">

                    <img x-show="currentPage < totalPages"
                         :src="pages[currentPage]"
                         :alt="`Page ${currentPage + 1}`"
                         class="max-h-[calc(100vh-3.5rem)] w-auto select-none"
                         loading="lazy" draggable="false">
                </div>
            </div>
        </template>

        {{-- ── VERTICAL SCROLL MODE (Manhwa/Manhua) ── --}}
        <template x-if="mode === 'vertical'">
            <div class="max-w-3xl mx-auto" x-ref="verticalContainer">
                <template x-for="(page, index) in pages" :key="index">
                    <img
                        :src="page"
                        :alt="`Page ${index + 1}`"
                        :data-page="index + 1"
                        class="w-full h-auto select-none block"
                        loading="lazy"
                        draggable="false"
                        @load="onPageLoad(index + 1)"
                    >
                </template>
            </div>
        </template>

    </main>


    {{-- ╔══════════════════════════════════════════╗
         ║         FLOATING PAGE NAV (mobile)       ║
         ╚══════════════════════════════════════════╝ --}}
    <div x-show="mode !== 'vertical'"
         class="fixed bottom-4 left-1/2 -translate-x-1/2 z-30 flex items-center gap-2 bg-[#0f111a]/90 backdrop-blur-md border border-gray-800 rounded-full p-1 shadow-xl">

        <button @click="prev()"
                :disabled="currentPage <= 1"
                class="w-10 h-10 rounded-full bg-gray-800 hover:bg-emerald-600 text-white flex items-center justify-center transition disabled:opacity-30 disabled:cursor-not-allowed"
                aria-label="Previous page">
            <i class="fas fa-chevron-left"></i>
        </button>

        <span class="text-xs text-gray-300 px-2 font-mono">
            <span x-text="currentPage"></span> / <span x-text="totalPages"></span>
        </span>

        <button @click="next()"
                :disabled="currentPage >= totalPages"
                class="w-10 h-10 rounded-full bg-gray-800 hover:bg-emerald-600 text-white flex items-center justify-center transition disabled:opacity-30 disabled:cursor-not-allowed"
                aria-label="Next page">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>


    {{-- ╔══════════════════════════════════════════╗
         ║         BOTTOM CHAPTER NAV               ║
         ╚══════════════════════════════════════════╝ --}}
    <footer class="bg-[#0a0a0f] border-t border-gray-800 py-6">
        <div class="max-w-3xl mx-auto px-4 grid grid-cols-3 gap-2">

            @if($prevChapter)
                {{ route('manga.read', ['slug' => $manga->slug, 'chapter' => $prevChapter->number]) }}
                   class="btn-cancel flex items-center justify-center">
                    <i class="fas fa-chevron-left"></i>
                    <span class="hidden sm:inline">Prev Chapter</span>
                </a>
            @else
                <span class="btn-cancel opacity-40 cursor-not-allowed flex items-center justify-center">
                    <i class="fas fa-chevron-left"></i>
                    <span class="hidden sm:inline">First Chapter</span>
                </span>
            @endif

            {{ route('manga.detail', $manga->slug) }}
               class="btn-cancel flex items-center justify-center">
                <i class="fas fa-list"></i>
                <span class="hidden sm:inline">All Chapters</span>
            </a>

            @if($nextChapter)
                {{ route('manga.read', ['slug' => $manga->slug, 'chapter' => $nextChapter->number]) }}
                   class="btn-success flex items-center justify-center">
                    <span class="hidden sm:inline">Next Chapter</span>
                    <i class="fas fa-chevron-right"></i>
                </a>
            @else
                <div class="bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 rounded-lg flex items-center justify-center px-3 py-2 text-sm">
                    <i class="fas fa-flag-checkered"></i>
                    <span class="hidden sm:inline ml-2">You're up to date!</span>
                </div>
            @endif

        </div>
    </footer>

</div>
@endsection