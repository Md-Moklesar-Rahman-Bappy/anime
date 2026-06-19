@extends('admin.layouts.app')

@section('content')
<div class="container" style="max-width:1100px">

    <a href="{{ route('admin.jikan.search') }}"
       class="small mb-3 d-inline-block" style="color:#818cf8">
        ← Back to search
    </a>

    <div class="card mb-4" style="background:#111827;border:1px solid #374151;border-radius:1rem;overflow:hidden">
        <div class="row g-0">
            @if($anime['thumbnail'])
            <div class="col-md-4 col-lg-3">
                <img src="{{ $anime['thumbnail'] }}" style="width:100%;height:100%;object-fit:cover">
            </div>
            @endif
            <div class="col p-4">
                <h1 class="h4 fw-semibold text-white mb-2">{{ $anime['title'] }}</h1>

                <div class="d-flex flex-wrap gap-2 mb-3">
                    @foreach($anime['genres'] as $genre)
                        <span class="badge rounded-1 fw-normal" style="background:rgba(99,102,241,0.1);color:#818cf8;font-size:0.75rem">{{ $genre['name'] }}</span>
                    @endforeach
                </div>

                <div class="row row-cols-2 row-cols-md-4 g-3 small mb-3">
                    <div><span class="d-block" style="color:#6b7280">Type</span><span style="color:#d1d5db">{{ $anime['type'] ?: '-' }}</span></div>
                    <div><span class="d-block" style="color:#6b7280">Status</span><span style="color:#d1d5db">{{ $anime['status'] ?: '-' }}</span></div>
                    <div><span class="d-block" style="color:#6b7280">Episodes</span><span style="color:#d1d5db">{{ $anime['episodes_count'] ?: '?' }}</span></div>
                    <div><span class="d-block" style="color:#6b7280">Score</span><span style="color:#d1d5db">{{ $anime['score'] ?: '-' }}</span></div>
                    <div><span class="d-block" style="color:#6b7280">Season</span><span style="color:#d1d5db">{{ $anime['season'] ? "{$anime['season']} {$anime['year']}" : '-' }}</span></div>
                    <div><span class="d-block" style="color:#6b7280">Studio</span><span style="color:#d1d5db">{{ $anime['studio'] ?: '-' }}</span></div>
                    <div><span class="d-block" style="color:#6b7280">Duration</span><span style="color:#d1d5db">{{ $anime['duration'] ? "{$anime['duration']} min" : '-' }}</span></div>
                    <div><span class="d-block" style="color:#6b7280">Source</span><span style="color:#d1d5db">{{ $anime['source'] ?: '-' }}</span></div>
                </div>

                @if($anime['description'])
                <p class="small" style="color:#9ca3af;line-height:1.7">{{ $anime['description'] }}</p>
                @endif
            </div>
        </div>
    </div>

    @if(count($episodes) > 0)
    <div class="card mb-4" style="background:#111827;border:1px solid #374151;border-radius:1rem;overflow:hidden">
        <div class="p-3" style="border-bottom:1px solid #374151">
            <h2 class="h5 fw-medium text-white">Episodes ({{ count($episodes) }})</h2>
        </div>
        <div class="table-responsive">
            <table class="table table-dark table-borderless mb-0 align-middle">
                <thead>
                    <tr style="background:#0f172a;color:#9ca3af;border-bottom:1px solid #374151">
                        <th class="p-3 text-start">#</th>
                        <th class="p-3 text-start">Title</th>
                        <th class="p-3 text-start">Aired</th>
                        <th class="p-3 text-start">Duration</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($episodes as $ep)
                    <tr style="border-bottom:1px solid #374151">
                        <td class="p-3 text-white">{{ $ep['number'] }}</td>
                        <td class="p-3" style="color:#d1d5db">{{ $ep['title'] ?: 'Episode '.$ep['number'] }}</td>
                        <td class="p-3" style="color:#9ca3af">{{ $ep['air_date'] ?: '-' }}</td>
                        <td class="p-3" style="color:#9ca3af">{{ $ep['duration'] ? "{$ep['duration']} min" : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="d-flex align-items-center justify-content-between gap-3">
        <form action="{{ route('admin.jikan.import', $anime['mal_id']) }}" method="POST">
            @csrf
            <button type="submit"
                class="btn btn-lg" style="background:#4f46e5;color:#fff;font-weight:500"
                onclick="return confirm('{{ $alreadyImported ? 'Re-import (update)' : 'Import' }} {{ $anime['title'] }}?')">
                {{ $alreadyImported ? 'Re-import (Update)' : 'Import Anime' }}
            </button>
        </form>
        <span class="small" style="color:#6b7280">
            MAL ID: {{ $anime['mal_id'] }} • {{ count($episodes) }} episodes
        </span>
    </div>

</div>
@endsection