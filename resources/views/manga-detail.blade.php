@extends('layouts.main')

@section('title', $manga->title)

@section('content')
<div class="container-fluid px-3 py-3" style="max-width:1280px">

    <div style="position:relative;border-radius:0.75rem;overflow:hidden;height:260px;margin-bottom:2rem">
        <img src="{{ $manga->banner_url ?? asset('fallback.jpg') }}" style="width:100%;height:100%;object-fit:cover">
        <div style="position:absolute;inset:0;background:linear-gradient(to top,#0a0a0f,rgba(0,0,0,0.6),transparent)"></div>
    </div>

    <div class="row">
        <div class="col-lg-3 d-flex flex-column gap-3">

            <img src="{{ $manga->thumbnail_url }}" style="width:100%;border-radius:0.75rem;box-shadow:0 4px 6px rgba(0,0,0,0.3)" loading="lazy">

            <a href="{{ route('manga.read', $manga->slug) }}"
               class="btn d-block w-100 text-center" style="background:#4f46e5;color:#fff;font-weight:600">
                📖 Read Now
            </a>

            @auth
            <button
                x-data="{ favorited: {{ $isFavorited ? 'true' : 'false' }} }"
                @click="favorited = !favorited;
                        fetch('/manga/favorites/toggle', {
                            method: 'POST',
                            headers: {
                                'Content-Type':'application/json',
                                'X-CSRF-TOKEN':'{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ manga_id: {{ $manga->id }} })
                        })"
                class="btn d-block w-100"
                style="background:#1f2937;color:#fff"
                x-text="favorited ? '✔ In Favorites' : '+ Add to Favorites'">
            </button>
            @endauth

        </div>

        <div class="col-lg-9">

            <h1 class="fw-semibold" style="color:#fff;font-size:1.75rem;margin-bottom:0.5rem">
                {{ $manga->title }}
            </h1>

            <div class="d-flex flex-wrap gap-2" style="color:#9ca3af;font-size:0.875rem;margin-bottom:1rem">

                @if($manga->rating)
                <span style="color:#facc15">⭐ {{ $manga->rating }}</span>
                @endif

                @if($manga->score)
                <span>Score: {{ $manga->score }}</span>
                @endif

                @if($manga->type)
                <span style="background:#1f2937;padding:0.25rem 0.5rem;border-radius:0.25rem;color:#d1d5db">{{ $manga->type }}</span>
                @endif

                @if($manga->status)
                <span style="background:#1f2937;padding:0.25rem 0.5rem;border-radius:0.25rem;color:#d1d5db">{{ $manga->status }}</span>
                @endif

                @if($manga->year)
                <span>{{ $manga->year }}</span>
                @endif

                @if($manga->chapters_count)
                <span>{{ $manga->chapters_count }} chapters</span>
                @endif

            </div>

            <div class="d-flex flex-wrap gap-1 mb-3">
                @foreach($manga->genres as $genre)
                    <a href="{{ route('manga.genre', $genre->slug) }}" style="display:inline-block;background:rgba(99,102,241,0.1);color:#818cf8;padding:0.25rem 0.75rem;border-radius:999px;font-size:0.875rem;text-decoration:none">
                        {{ $genre->name }}
                    </a>
                @endforeach
            </div>

            <p style="color:#d1d5db;line-height:1.625;margin-bottom:1.5rem">
                {{ $manga->description ?? 'No description available.' }}
            </p>

            <div class="row row-cols-2 row-cols-md-3 g-3" style="font-size:0.875rem;margin-bottom:2rem">
                @foreach([
                    'Author'=>$manga->author,
                    'Artist'=>$manga->artist,
                    'Publisher'=>$manga->publisher,
                    'Source'=>$manga->source,
                    'Views'=>$manga->views ? number_format($manga->views):null
                ] as $label=>$value)
                    @if($value)
                        <div class="col"><span style="color:#6b7280">{{ $label }}:</span> {{ $value }}</div>
                    @endif
                @endforeach
            </div>

            @if($manga->chapters->count())
            <h2 style="font-size:1.25rem;font-weight:600;color:#fff;margin-bottom:1rem">Chapters</h2>

            <div class="d-flex flex-column gap-2">
                @foreach($manga->chapters as $ch)

                <a href="{{ route('manga.read', ['slug'=>$manga->slug,'chapter'=>$ch->number]) }}"
                   style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem;background:#111827;border:1px solid #374151;border-radius:0.5rem;text-decoration:none;transition:background 0.3s">

                    <div>
                        <span style="color:#818cf8;font-weight:600">
                            Ch. {{ rtrim(rtrim($ch->number,'0'),'.') }}
                        </span>

                        @if($ch->title)
                        <span style="color:#9ca3af;margin-left:0.5rem">
                            {{ $ch->title }}
                        </span>
                        @endif
                    </div>

                    <span style="font-size:0.75rem;color:#6b7280">
                        {{ $ch->pages_count }} pages
                    </span>

                </a>

                @endforeach
            </div>
            @endif

            @if($related->count())
            <h2 style="font-size:1.25rem;font-weight:600;color:#fff;margin-top:2.5rem;margin-bottom:1rem">Related Manga</h2>

            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 g-3">

                @foreach($related as $rel)
                <div class="col">
                <a href="{{ route('manga.detail',$rel->slug) }}" class="text-decoration-none">

                    <div style="position:relative;border-radius:0.75rem;overflow:hidden;background:#111827;aspect-ratio:2/3">
                        <img src="{{ $rel->thumbnail_url }}" style="width:100%;height:100%;object-fit:cover;transition:transform 0.3s">
                    </div>

                    <p style="color:#d1d5db;font-size:0.875rem;margin-top:0.5rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $rel->title }}</p>

                </a>
                </div>
                @endforeach

            </div>
            @endif

        </div>
    </div>
</div>
@endsection