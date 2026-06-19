@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-4">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="h4 fw-semibold text-white">
                Episodes: {{ $anime->title }}
            </h1>
            <a href="{{ route('admin.anime.index') }}"
               class="small" style="color:#9ca3af">
                ← Back to Anime
            </a>
        </div>

        <div class="d-flex gap-2">
            @if($anime->mal_id)
            <form action="{{ route('admin.jikan.refresh-episodes', $anime->mal_id) }}" method="POST">
                @csrf
                <button type="submit"
                    class="btn btn-sm d-flex align-items-center gap-1" style="background:#059669;color:#fff">
                    <svg style="width:1rem;height:1rem" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                              d="M4 4v5h.5m15 2A8 8 0 004.5 9M4 9h4m12 11v-5h-.5m0 0A8 8 0 014 15"/>
                    </svg>
                    Refresh
                </button>
            </form>
            @endif

            <a href="{{ route('admin.anime.episodes.create', $anime) }}"
               class="btn btn-sm" style="background:#4f46e5;color:#fff">
                Add Episode
            </a>

            <div x-data="{ open: false }" class="position-relative">
                <button @click="open = !open"
                        class="btn btn-sm" style="background:#1f2937;color:#d1d5db;border:1px solid #4b5563">
                    Quick Import
                </button>
                <div x-show="open" @click.outside="open=false"
                     class="position-absolute end-0 mt-2"
                     style="width:12rem;background:#111827;border:1px solid #374151;border-radius:0.5rem;z-index:50">
                    <a href="{{ route('admin.anime.episodes.create', $anime) }}?source=youtube"
                       class="d-block px-3 py-2 small" style="color:#d1d5db">
                        From YouTube
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="background:#111827;border:1px solid #374151;border-radius:1rem;overflow:hidden">
        <div class="table-responsive">
            <table class="table table-dark table-borderless mb-0 align-middle">
                <thead>
                <tr style="background:#0f172a;color:#9ca3af;border-bottom:1px solid #374151">
                    <th class="p-3 text-start">#</th>
                    <th class="p-3 text-start">Title</th>
                    <th class="p-3 text-start">Source</th>
                    <th class="p-3 text-start">Duration</th>
                    <th class="p-3 text-start">Sub</th>
                    <th class="p-3 text-start">Dub</th>
                    <th class="p-3 text-start">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($episodes as $ep)
                <tr style="border-bottom:1px solid #374151">
                    <td class="p-3 text-white">{{ $ep->number }}</td>
                    <td class="p-3" style="color:#d1d5db">{{ $ep->title ?? 'Episode '.$ep->number }}</td>
                    <td class="p-3">
                        <span class="badge rounded-1 fw-normal" style="font-size:0.75rem;
                            @switch($ep->source_type)
                                @case('youtube') background:rgba(239,68,68,0.1);color:#f87171 @break
                                @case('upload') background:rgba(34,197,94,0.1);color:#4ade80 @break
                                @case('external') background:rgba(34,211,238,0.1);color:#22d3ee @break
                                @default background:#374151;color:#9ca3af
                            @endswitch
                        ">
                            {{ ucfirst($ep->source_type ?? '-') }}
                        </span>
                    </td>
                    <td class="p-3" style="color:#9ca3af">{{ $ep->duration ? $ep->duration.'m' : '-' }}</td>
                    <td class="p-3" style="color:#d1d5db">{{ $ep->has_sub ? '✔' : '—' }}</td>
                    <td class="p-3" style="color:#d1d5db">{{ $ep->has_dub ? '✔' : '—' }}</td>
                    <td class="p-3">
                        <div class="d-flex gap-3 small">
                            <a href="{{ route('admin.anime.episodes.edit', [$anime, $ep]) }}" style="color:#60a5fa">Edit</a>
                            @if($ep->video_path && $ep->storage_disk === 'local')
                            <form action="{{ route('admin.anime.episodes.delete-video', [$anime, $ep]) }}"
                                  method="POST" onsubmit="return confirm('Delete video file?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm border-0 p-0" style="color:#fb923c">Video</button>
                            </form>
                            @endif
                            <form action="{{ route('admin.anime.episodes.destroy', [$anime, $ep]) }}"
                                  method="POST" onsubmit="return confirm('Delete this episode?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm border-0 p-0" style="color:#f87171">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="p-5 text-center" style="color:#6b7280">
                        <p class="h5" style="color:#d1d5db">No episodes found</p>
                        <p class="small mt-1">Add your first episode</p>
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3" style="border-top:1px solid #374151">
            {{ $episodes->links() }}
        </div>
    </div>
</div>
@endsection