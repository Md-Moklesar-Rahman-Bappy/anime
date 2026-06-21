@extends('admin.layouts.app')

@section('content')
<div
    class="max-w-7xl mx-auto"
    x-data="mangaDashboard({
        typeLabels:   @json($mangaByType->keys()),
        typeData:     @json($mangaByType->values()),
        statusLabels: @json($mangaByStatus->keys()),
        statusData:   @json($mangaByStatus->values()),
    })"
    x-init="init()"
>

    {{-- HEADER --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-semibold text-white">Manga Dashboard</h1>
            <p class="text-sm text-gray-400">{{ now()->format('l, F j, Y') }}</p>
        </div>

        {{ route('admin.manga.create') }}-1.5 text-sm rounded-md bg-emerald-600 hover:bg-emerald-500 text-white transition">
            + Add Manga
        </a>
    </div>

    {{-- STAT CARDS --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @foreach([
            ['Manga',    $totalManga,       '📖', 'from-emerald-500/20 to-emerald-500/0'],
            ['Chapters', $totalChapters,    '📄', 'from-amber-500/20 to-amber-500/0'],
            ['Views',    $totalMangaViews,  '👁',  'from-sky-500/20 to-sky-500/0'],
            ['Users',    $totalUsers,       '👥', 'from-indigo-500/20 to-indigo-500/0'],
        ] as [$label, $value, $icon, $gradient])
            <div class="relative overflow-hidden rounded-2xl border border-gray-800 bg-[#111827] p-4">
                <div class="absolute inset-0 bg-gradient-to-br {{ $gradient }} pointer-events-none"></div>

                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs uppercase tracking-wider text-gray-400">
                            {{ $label }}
                        </p>
                        <span class="text-lg">{{ $icon }}</span>
                    </div>

                    <p class="text-2xl font-bold text-white">
                        {{ number_format($value) }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- CHARTS --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">

        <div class="rounded-2xl border border-gray-800 bg-[#111827] p-5">
            <h2 class="text-base font-semibold text-white mb-3">
                Manga by Type
            </h2>
            <div class="h-64">
                <canvas id="mangaByTypeChart"></canvas>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-800 bg-[#111827] p-5">
            <h2 class="text-base font-semibold text-white mb-3">
                Manga by Status
            </h2>
            <div class="h-64">
                <canvas id="mangaByStatusChart"></canvas>
            </div>
        </div>

    </div>

    {{-- RECENT CHAPTERS --}}
    <div class="rounded-2xl border border-gray-800 bg-[#111827] p-5">

        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-white">
                Recent Chapters
            </h2>

            {{ route('admin.chapters.index') }}-indigo-400 hover:text-indigo-300">
                View all →
            </a>
        </div>

        @forelse($recentChapters as $chapter)
            <div class="flex items-center gap-3 py-3 border-b border-gray-800 last:border-0">

                {{-- Thumbnail --}}
                <div class="w-10 h-14 bg-gray-800 rounded overflow-hidden flex-shrink-0">
                    @if($chapter->manga && $chapter->manga->thumbnail)
                        {{ $chapter->manga->thumbnail_url }}="w-full h-full object-cover"
                            alt="{{ $chapter->manga->title }}"
                        >
                    @endif
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-white truncate">
                        {{ $chapter->manga->title ?? 'Unknown' }}
                    </p>
                    <p class="text-xs text-gray-400 truncate">
                        Ch {{ $chapter->number }}{{ $chapter->title ? ' — ' . $chapter->title : '' }}
                    </p>
                </div>

                {{-- Action --}}
                @if($chapter->manga)
                     route('admin.manga.edit', $chapter->manga) }}
                       class="text-xs text-gray-400 hover:text-white">
                        Edit
                    </a>
                @endif

            </div>
        @empty
            <p class="text-sm text-gray-500 py-6 text-center">
                No chapters yet
            </p>
        @endforelse

    </div>

</div>
@endsection