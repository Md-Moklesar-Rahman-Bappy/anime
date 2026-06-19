@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-4"
     x-data="mangaDashboard({
        typeLabels: @json($mangaByType->keys()),
        typeData: @json($mangaByType->values()),
        statusLabels: @json($mangaByStatus->keys()),
        statusData: @json($mangaByStatus->values()),
     })">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="h4 fw-semibold text-white">Manga Dashboard</h1>
            <p class="small" style="color:#9ca3af">{{ now()->format('l, F j, Y') }}</p>
        </div>

        <a href="{{ route('admin.manga.create') }}"
           class="btn btn-sm" style="background:#059669;border-color:#059669;color:#fff">
            + Add Manga
        </a>
    </div>

    <div class="row g-3 mb-4">
        @foreach([
            ['Manga',$totalManga,'📖'],
            ['Chapters',$totalChapters,'📄'],
            ['Views',$totalMangaViews,'👁'],
            ['Users',$totalUsers,'👥'],
        ] as [$label,$value,$icon])
        <div class="col-6 col-md-3">
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
        <div class="col-lg-6">
            <div class="card h-100" style="background:#111827;border:1px solid #374151;border-radius:1rem">
                <div class="card-body">
                    <h2 class="fw-semibold mb-2">Manga by Type</h2>
                    <div style="height:256px"><canvas id="mangaByTypeChart"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100" style="background:#111827;border:1px solid #374151;border-radius:1rem">
                <div class="card-body">
                    <h2 class="fw-semibold mb-2">Manga by Status</h2>
                    <div style="height:256px"><canvas id="mangaByStatusChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4" style="background:#111827;border:1px solid #374151;border-radius:1rem">
        <div class="card-body">
            <h2 class="fw-semibold mb-3">Recent Chapters</h2>
            @forelse($recentChapters as $chapter)
            <div class="d-flex gap-3 py-2" style="border-bottom:1px solid #374151">
                <div style="width:36px;height:48px;background:#1f2937;border-radius:0.25rem;overflow:hidden;flex-shrink:0">
                    @if($chapter->manga && $chapter->manga->thumbnail)
                    <img src="{{ $chapter->manga->thumbnail_url }}" style="width:100%;height:100%;object-fit:cover">
                    @endif
                </div>
                <div style="flex:1;min-width:0">
                    <p class="mb-0 small text-truncate">{{ $chapter->manga->title ?? 'Unknown' }}</p>
                    <p class="mb-0 small" style="color:#9ca3af">Ch {{ $chapter->number }}{{ $chapter->title ? ' - '.$chapter->title : '' }}</p>
                </div>
            </div>
            @empty
                <p class="small" style="color:#6b7280">No chapters yet</p>
            @endforelse
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
function mangaDashboard(chartData) {
    return {
        init() {
            this.$nextTick(() => {
                this.initMangaTypeChart();
                this.initMangaStatusChart();
            });
        },
        initMangaTypeChart() {
            const ctx = document.getElementById('mangaByTypeChart');
            if (!ctx || !chartData.typeLabels?.length) return;
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: chartData.typeLabels,
                    datasets: [{
                        data: chartData.typeData,
                        backgroundColor: ['#10b981', '#f59e0b', '#3b82f6', '#ef4444', '#8b5cf6', '#ec4899'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: '#9ca3af', padding: 16, usePointStyle: true, pointStyle: 'circle' }
                        }
                    }
                }
            });
        },
        initMangaStatusChart() {
            const ctx = document.getElementById('mangaByStatusChart');
            if (!ctx || !chartData.statusLabels?.length) return;
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: chartData.statusLabels,
                    datasets: [{
                        data: chartData.statusData,
                        backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: '#9ca3af', padding: 16, usePointStyle: true, pointStyle: 'circle' }
                        }
                    }
                }
            });
        }
    };
}
</script>
@endpush
