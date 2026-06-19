@extends('layouts.main')

@section('title', $genre->name . ' Anime')

@section('content')
<div class="container-fluid px-3 py-3" style="max-width:1280px">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fw-semibold" style="color:#fff;font-size:1.5rem">
                {{ $genre->name }} Anime
            </h1>
            <p class="mt-1" style="color:#9ca3af;font-size:0.875rem">
                Explore anime in this genre
            </p>
        </div>

        <span style="color:#6b7280;font-size:0.875rem">
            {{ $animeList->total() }} results
        </span>
    </div>

    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-3">

        @forelse($animeList as $anime)

        <div class="col">
        <a href="{{ route('anime.detail', $anime->slug) }}" class="text-decoration-none">

            <div style="position:relative;border-radius:0.75rem;overflow:hidden;background:#111827;aspect-ratio:2/3">

                <img src="{{ $anime->thumbnail_url }}"
                     alt="{{ $anime->title }}"
                     style="width:100%;height:100%;object-fit:cover;transition:transform 0.3s"
                     loading="lazy">

                <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.7);opacity:0;color:#fff;font-size:0.875rem;transition:opacity 0.3s">
                    ▶ View
                </div>

                @if($anime->type)
                <span style="position:absolute;top:0.5rem;left:0.5rem;background:rgba(0,0,0,0.7);color:#fff;font-size:0.75rem;padding:0.25rem 0.5rem;border-radius:0.25rem">
                    {{ $anime->type }}
                </span>
                @endif

                @if($anime->episodes_count)
                <span style="position:absolute;top:0.5rem;right:0.5rem;background:#4f46e5;color:#fff;font-size:0.75rem;padding:0.25rem 0.5rem;border-radius:0.25rem">
                    {{ $anime->episodes_count }}
                </span>
                @endif

            </div>

            <h3 style="color:#d1d5db;font-size:0.875rem;margin-top:0.5rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                {{ $anime->title }}
            </h3>

        </a>
        </div>

        @empty

        <div class="col-12 text-center py-5" style="color:#6b7280">
            ❌ No anime found in this genre
        </div>

        @endforelse

    </div>

    <div class="mt-4">
        {{ $animeList->links() }}
    </div>

</div>
@endsection