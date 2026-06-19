@extends('layouts.main')

@section('title', 'Home')

@section('content')

<div class="container-fluid px-3 py-3" style="max-width:1280px">

    @if($featured->count())
    <div class="position-relative overflow-hidden" style="height:420px;border-radius:0.75rem">

        @foreach($featured as $anime)
        <div class="position-absolute top-0 start-0 end-0 bottom-0"
             style="background-size:cover;background-position:center;background-image:url('{{ $anime->banner_url ?? asset('fallback.jpg') }}')">
        </div>

        <div class="position-absolute top-0 start-0 end-0 bottom-0" style="background:linear-gradient(to top,#000,rgba(0,0,0,0.6),transparent)"></div>

        <div class="position-absolute bottom-0 start-0 p-3" style="max-width:36rem">
            <h2 style="font-size:1.75rem;font-weight:700;color:#fff;margin-bottom:0.5rem">{{ $anime->title }}</h2>

            <p style="color:#9ca3af;font-size:0.875rem;margin-bottom:0.75rem">
                {{ \Illuminate\Support\Str::limit($anime->description,150) }}
            </p>

            <a href="{{ route('watch', $anime->slug) }}"
               class="btn" style="background:#4f46e5;color:#fff;font-weight:600;font-size:0.875rem">
                ▶ Watch Now
            </a>
        </div>
        @endforeach
    </div>
    @endif


    <div class="row">
        <div class="col-lg-9 d-flex flex-column gap-4">

            <section>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 style="font-size:1.125rem;color:#fff;font-weight:600">Latest Episodes</h2>
                </div>

                <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 g-3">
                    @foreach($latestEpisodes as $episode)
                    <div class="col">
                    <a href="{{ route('watch',['slug'=>$episode->anime->slug,'ep'=>$episode->number]) }}" class="text-decoration-none">

                        <div style="position:relative;border-radius:0.75rem;overflow:hidden;background:#111827;aspect-ratio:2/3">
                            <img src="{{ $episode->thumbnail_url }}" style="width:100%;height:100%;object-fit:cover;transition:transform 0.3s">

                            <div style="position:absolute;top:0.5rem;left:0.5rem;background:#9333ea;color:#fff;font-size:0.75rem;padding:0.125rem 0.5rem;border-radius:0.25rem">
                                Ep {{ $episode->number }}
                            </div>

                            @if($episode->has_sub)
                                <div style="position:absolute;top:0.5rem;right:0.5rem;background:#2563eb;color:#fff;font-size:0.625rem;padding:0.125rem 0.375rem;border-radius:0.25rem">SUB</div>
                            @endif

                            @if($episode->has_dub)
                                <div style="position:absolute;top:1.75rem;right:0.5rem;background:#16a34a;color:#fff;font-size:0.625rem;padding:0.125rem 0.375rem;border-radius:0.25rem">DUB</div>
                            @endif

                            <div style="position:absolute;inset:0;background:rgba(0,0,0,0.7);display:flex;align-items:flex-end;padding:0.75rem;opacity:0;transition:opacity 0.3s">
                                <span style="color:#fff;font-size:0.75rem">Watch</span>
                            </div>
                        </div>

                        <p style="color:#d1d5db;font-size:0.875rem;margin-top:0.5rem">{{ $episode->anime->title }}</p>

                    </a>
                    </div>
                    @endforeach
                </div>
            </section>


            <div class="row row-cols-1 row-cols-md-3 g-3">

                @foreach([
                    ['title'=>'New Release','data'=>$newAnime],
                    ['title'=>'Newly Added','data'=>$newlyAdded],
                    ['title'=>'Completed','data'=>$justCompleted],
                ] as $section)

                <div class="col">
                    <div>
                    <h3 style="font-size:0.875rem;font-weight:700;color:#fff;margin-bottom:0.75rem">
                        {{ $section['title'] }}
                    </h3>

                    @foreach($section['data']->take(5) as $anime)
                    <a href="{{ route('anime.detail',$anime->slug) }}"
                       class="d-flex text-decoration-none" style="gap:0.75rem;padding:0.5rem;border-radius:0.5rem;transition:background 0.2s">

                        <img src="{{ $anime->thumbnail_url }}"
                             style="width:3rem;height:4rem;object-fit:cover;border-radius:0.25rem">

                        <div style="flex:1">
                            <p style="color:#d1d5db;font-size:0.875rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                {{ $anime->title }}
                            </p>

                            <span style="color:#6b7280;font-size:0.75rem">
                                {{ $anime->episodes_count ?? '?' }}
                            </span>
                        </div>

                    </a>
                    @endforeach
                    </div>
                </div>

                @endforeach
            </div>

        </div>


        <div class="col-lg-3">

            <h3 style="font-size:0.875rem;font-weight:700;color:#fff;margin-bottom:1rem">Top Anime</h3>

            @foreach($topAnime as $i => $anime)
            <a href="{{ route('anime.detail',$anime->slug) }}"
               class="d-flex align-items-center text-decoration-none" style="gap:0.75rem;padding:0.5rem;border-radius:0.5rem;transition:background 0.2s">

                <span style="font-size:1.125rem;font-weight:700;width:1.25rem;{{ $i<3?'color:#a855f7':'color:#6b7280' }}">
                    {{ $i+1 }}
                </span>

                <img src="{{ $anime->thumbnail_url }}"
                     style="width:2.5rem;height:3.5rem;object-fit:cover;border-radius:0.25rem">

                <div>
                    <p style="color:#d1d5db;font-size:0.875rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $anime->title }}</p>
                    <p style="color:#6b7280;font-size:0.75rem">⭐ {{ $anime->rating ?? 'N/A' }}</p>
                </div>

            </a>
            @endforeach

        </div>

    </div>
</div>

@endsection