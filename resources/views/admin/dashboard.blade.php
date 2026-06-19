@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-4"
     x-data="dashboard({
        userGrowthLabels: @json($userGrowth->pluck('label')),
        userGrowthData: @json($userGrowth->pluck('count')),
        typeLabels: @json($animeByType->keys()),
        typeData: @json($animeByType->values()),
        statusLabels: @json($animeByStatus->keys()),
        statusData: @json($animeByStatus->values()),
     })">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="h4 fw-semibold text-white">Dashboard</h1>
            <p class="small" style="color:#9ca3af">{{ now()->format('l, F j, Y') }}</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.anime.create') }}"
               class="btn btn-sm" style="background:#4f46e5;border-color:#4f46e5;color:#fff">
                + Anime
            </a>
            <a href="{{ route('admin.manga.create') }}"
               class="btn btn-sm" style="background:#059669;border-color:#059669;color:#fff">
                + Manga
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach([
            ['Anime',$totalAnime,'🎬'],
            ['Episodes',$totalEpisodes,'▶'],
            ['Manga',$totalManga,'📖'],
            ['Chapters',$totalChapters,'📄'],
            ['Users',$totalUsers,'👥'],
        ] as [$label,$value,$icon])
        <div class="col-6 col-md-3 col-lg">
            <div class="card h-100" style="background:#111827;border:1px solid #374151;border-radius:1rem">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <p class="small text-uppercase mb-0" style="color:#9ca3af;font-size:0.75rem">{{ $label }}</p>
                        <span>{{ $icon }}</span>
                    </div>
                    <p class="h5 fw-bold mb-0">{{ number_format($value) }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card h-100" style="background:#111827;border:1px solid #374151;border-radius:1rem">
                <div class="card-body">
                    <h2 class="fw-semibold mb-2">User Growth</h2>
                    <div style="height:256px"><canvas id="userGrowthChart"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100" style="background:#111827;border:1px solid #374151;border-radius:1rem">
                <div class="card-body">
                    <h2 class="fw-semibold mb-2">Anime Types</h2>
                    <div style="height:256px"><canvas id="animeByTypeChart"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100" style="background:#111827;border:1px solid #374151;border-radius:1rem">
                <div class="card-body">
                    <h2 class="fw-semibold mb-2">Anime Status</h2>
                    <div style="height:256px"><canvas id="animeByStatusChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4" style="background:#111827;border:1px solid #374151;border-radius:1rem">
        <div class="card-body">
            <h2 class="fw-semibold mb-3">Recent Episodes</h2>
            @forelse($recentEpisodes as $episode)
            <div class="d-flex gap-3 py-2" style="border-bottom:1px solid #374151">
                <div style="width:48px;height:28px;background:#1f2937;border-radius:0.25rem;overflow:hidden">
                    <img src="{{ $episode->thumbnail_url }}" style="width:100%;height:100%;object-fit:cover">
                </div>
                <div style="flex:1;min-width:0">
                    <p class="mb-0 small text-truncate">{{ $episode->anime->title ?? '-' }}</p>
                    <p class="mb-0 small" style="color:#9ca3af">Ep {{ $episode->number }}</p>
                </div>
            </div>
            @empty
                <p class="small" style="color:#6b7280">No episodes yet</p>
            @endforelse
        </div>
    </div>

</div>

@endsection



































