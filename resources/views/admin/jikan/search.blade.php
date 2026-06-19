@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-4">

    <h1 class="h4 fw-semibold text-white mb-3">MAL Import</h1>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card" style="background:#111827;border:1px solid #374151;border-radius:1rem">
                <div class="card-body">
                    <h2 class="h5 fw-medium text-white mb-3">Search & Import</h2>
                    <form action="{{ route('admin.jikan.search.results') }}" method="POST">
                        @csrf
                        <div class="d-flex gap-3">
                            <input type="text" name="q"
                                value="{{ old('q', $query ?? '') }}"
                                placeholder="Search MyAnimeList..."
                                class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff;flex:1"
                                required>
                            <button class="btn" style="background:#4f46e5;color:#fff">Search</button>
                        </div>
                    </form>
                    <div class="mt-3 d-flex flex-wrap gap-2 small" style="color:#9ca3af">
                        <span>Try:</span>
                        @foreach(['One Piece','Naruto','Attack on Titan','Demon Slayer'] as $s)
                        <form action="{{ route('admin.jikan.search.results') }}" method="POST">
                            @csrf
                            <input type="hidden" name="q" value="{{ $s }}">
                            <button class="btn btn-sm border-0 p-0" style="color:#818cf8;text-decoration:underline"> {{ $s }}</button>
                        </form>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card" style="background:#111827;border:1px solid #374151;border-radius:1rem">
                <div class="card-body">
                    <h2 class="h5 fw-medium text-white mb-2">Mass Import</h2>
                    <p class="small mb-2" style="color:#9ca3af">
                        Imported: <span class="text-white">{{ $totalImported ?? 0 }}</span>
                        @if(!empty($lastMalId))
                            <br>Progress: <span style="color:#818cf8">#{{ $lastMalId }}</span>
                        @endif
                    </p>
                    <div class="d-flex flex-column gap-2">
                        @foreach([5,10,25] as $batch)
                        <form action="{{ route('admin.jikan.batch-import') }}" method="POST">
                            @csrf
                            <input type="hidden" name="batch_size" value="{{ $batch }}">
                            <input type="hidden" name="with_episodes" value="1">
                            <button class="btn btn-sm w-100" style="background:#4f46e5;color:#fff">Import Next {{ $batch }}</button>
                        </form>
                        @endforeach
                        @if(!empty($lastMalId))
                        <form action="{{ route('admin.jikan.reset-progress') }}" method="POST">
                            @csrf
                            <button class="btn btn-sm w-100" style="background:#1f2937;color:#9ca3af">Reset Progress</button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @isset($results)
        @if(count($results) > 0)
        <div class="d-flex flex-column gap-2">
            @foreach($results as $item)
            <div class="card" style="background:#111827;border:1px solid #374151;border-radius:1rem">
                <div class="d-flex">
                    <div style="width:80px">
                        @if($item['thumbnail'])
                            <img src="{{ $item['thumbnail'] }}" style="width:100%;height:112px;object-fit:cover">
                        @endif
                    </div>
                    <div class="p-3" style="flex:1">
                        <h3 class="text-white fw-medium text-truncate">{{ $item['title'] }}</h3>
                        <div class="d-flex flex-wrap gap-2 mt-1 small" style="color:#9ca3af">
                            <span class="badge" style="background:#1f2937;color:#9ca3af;font-weight:normal">{{ $item['type'] ?? 'N/A' }}</span>
                            <span class="badge" style="background:#1f2937;color:#9ca3af;font-weight:normal">{{ $item['episodes_count'] ? $item['episodes_count'].' eps' : '?' }}</span>
                            @if($item['score'])
                            <span class="badge" style="background:rgba(234,179,8,0.1);color:#facc15;font-weight:normal">{{ $item['score'] }}</span>
                            @endif
                        </div>
                        <div class="mt-2 small">
                        @if(in_array($item['mal_id'], $existingMalIds ?? []))
                            <span style="color:#6b7280">Imported</span>
                        @else
                            <a href="{{ route('admin.jikan.preview', $item['mal_id']) }}" style="color:#818cf8;font-weight:500">Preview & Import</a>
                        @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
            <p style="color:#9ca3af">No results found.</p>
        @endif
    @else
        <div class="card text-center" style="background:#111827;border:1px solid #374151;border-radius:1rem;padding:2rem;color:#6b7280">
            <p class="h5 mb-2" style="color:#d1d5db">Search for anime</p>
            <p class="small">Use the form above to import data from MAL.</p>
        </div>
    @endisset

</div>

@endsection
