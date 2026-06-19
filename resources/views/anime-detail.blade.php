@extends('layouts.main')

@section('title', $anime->title)

@section('content')
<div class="container-fluid px-3 py-3" style="max-width:1280px">

    <div style="position:relative;border-radius:0.75rem;overflow:hidden;height:260px;margin-bottom:2rem">
        <img src="{{ $anime->banner_url }}" style="width:100%;height:100%;object-fit:cover" alt="">
        <div style="position:absolute;inset:0;background:linear-gradient(to top,#0a0a0f,rgba(0,0,0,0.6),transparent)"></div>
    </div>

    <div class="row">
        <div class="col-lg-3 d-flex flex-column gap-3">

            <img src="{{ $anime->thumbnail_url }}"
                 style="width:100%;border-radius:0.75rem;box-shadow:0 4px 6px rgba(0,0,0,0.3)"
                 alt=""
                 loading="lazy">

            <a href="{{ route('watch', $anime->slug) }}"
               class="btn d-block w-100 text-center" style="background:#4f46e5;color:#fff;font-weight:600">
                ▶ Watch Now
            </a>

            @auth
            <button
                x-data="{ favorited: {{ $isFavorited ? 'true' : 'false' }} }"
                @click="favorited = !favorited; 
                        fetch('/favorites/toggle', {
                            method:'POST',
                            headers:{
                                'Content-Type':'application/json',
                                'X-CSRF-TOKEN':'{{ csrf_token() }}'
                            },
                            body: JSON.stringify({anime_id: {{ $anime->id }}})
                        })"
                class="btn d-block w-100"
                style="background:#1f2937;color:#fff"
                x-text="favorited ? '✔ In Favorites' : '+ Add to Favorites'">
            </button>
            @endauth

        </div>

        <div class="col-lg-9">

            <h1 class="fw-semibold" style="color:#fff;font-size:1.75rem;margin-bottom:0.5rem">
                {{ $anime->title }}
            </h1>

            <div class="d-flex flex-wrap gap-2" style="color:#9ca3af;font-size:0.875rem;margin-bottom:1rem">

                @if($anime->rating)
                <span class="d-flex align-items-center" style="color:#facc15">
                    ⭐ {{ $anime->rating }}
                </span>
                @endif

                @if($anime->score)
                <span>Score: {{ $anime->score }}</span>
                @endif

                @if($anime->type)
                <span style="background:#1f2937;padding:0.25rem 0.5rem;border-radius:0.25rem;color:#d1d5db">{{ $anime->type }}</span>
                @endif

                @if($anime->status)
                <span style="background:#1f2937;padding:0.25rem 0.5rem;border-radius:0.25rem;color:#d1d5db">{{ $anime->status }}</span>
                @endif

                @if($anime->year)
                <span>{{ $anime->year }}</span>
                @endif

                @if($anime->episodes_count)
                <span>{{ $anime->episodes_count }} eps</span>
                @endif

            </div>

            <div class="d-flex flex-wrap gap-1 mb-3">
                @foreach($anime->genres as $genre)
                <a href="{{ route('genre', $genre->slug) }}" style="display:inline-block;background:rgba(99,102,241,0.1);color:#818cf8;padding:0.25rem 0.75rem;border-radius:999px;font-size:0.875rem;text-decoration:none">
                    {{ $genre->name }}
                </a>
                @endforeach
            </div>

            <p style="color:#d1d5db;line-height:1.625;margin-bottom:1.5rem">
                {{ $anime->description ?? 'No description available.' }}
            </p>

            <div class="row row-cols-2 row-cols-md-3 g-3" style="font-size:0.875rem;margin-bottom:2rem">
                @foreach([
                    'Studio' => $anime->studio,
                    'Source' => $anime->source,
                    'Country' => $anime->country,
                    'Producers' => $anime->producers,
                    'Licensors' => $anime->licensors,
                    'Views' => $anime->views ? number_format($anime->views) : null
                ] as $label => $value)

                @if($value)
                <div class="col">
                    <span style="color:#6b7280">{{ $label }}:</span>
                    {{ $value }}
                </div>
                @endif

                @endforeach
            </div>

            @if($anime->episodes->count())
            <h2 style="font-size:1.25rem;font-weight:600;color:#fff;margin-bottom:1rem">Episodes</h2>

            <div class="d-flex flex-column gap-2">
                @foreach($anime->episodes as $ep)
                <a href="{{ route('watch', ['slug' => $anime->slug, 'ep' => $ep->number]) }}"
                   style="display:flex;align-items:center;justify-content:space-between;padding:0.75rem;background:#111827;border:1px solid #374151;border-radius:0.5rem;text-decoration:none;transition:background 0.3s">

                    <div class="d-flex gap-2">
                        <span style="color:#818cf8;font-weight:600">
                            Ep {{ $ep->number }}
                        </span>
                        <span style="color:#d1d5db">{{ $ep->title ?? 'Episode' }}</span>
                    </div>

                    <div class="d-flex gap-1">
                        @if($ep->has_sub)
                            <span style="font-size:0.75rem;background:#2563eb;color:#fff;padding:0.25rem 0.5rem;border-radius:0.25rem">SUB</span>
                        @endif
                        @if($ep->has_dub)
                            <span style="font-size:0.75rem;background:#16a34a;color:#fff;padding:0.25rem 0.5rem;border-radius:0.25rem">DUB</span>
                        @endif
                    </div>

                </a>
                @endforeach
            </div>
            @endif

            @if($related->count())
            <h2 style="font-size:1.25rem;font-weight:600;color:#fff;margin-top:2.5rem;margin-bottom:1rem">Related Anime</h2>

            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 g-3">

                @foreach($related as $rel)
                <div class="col">
                <a href="{{ route('anime.detail', $rel->slug) }}" class="text-decoration-none">

                    <div style="position:relative;border-radius:0.5rem;overflow:hidden;background:#111827;aspect-ratio:2/3">
                        <img src="{{ $rel->thumbnail_url }}"
                             style="width:100%;height:100%;object-fit:cover;transition:transform 0.3s"
                             loading="lazy">
                    </div>

                    <p style="color:#d1d5db;font-size:0.875rem;margin-top:0.5rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        {{ $rel->title }}
                    </p>

                </a>
                </div>
                @endforeach

            </div>
            @endif

        </div>
    </div>
</div>
@endsection