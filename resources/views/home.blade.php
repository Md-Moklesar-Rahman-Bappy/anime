@extends('layouts.main')

@section('title', 'Home')

@section('content')

<<<<<<< HEAD
<div class="container-fluid px-3 py-3" style="max-width:1280px">

    @if($featured->count())
    <div class="position-relative overflow-hidden" style="height:420px;border-radius:0.75rem">

        @foreach($featured as $anime)
        <div class="position-absolute top-0 start-0 end-0 bottom-0"
             style="background-size:cover;background-position:center;background-image:url('{{ $anime->banner_url ?? asset('fallback.jpg') }}')">
        </div>
=======
    {{-- ✅ FEATURED --}}
    @if(!empty($featured) && $featured->count())
    <section class="mb-8">
        <h2 class="text-xl font-bold text-white mb-4">Featured Anime</h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
            @foreach($featured as $anime)
            <a href="{{ route('anime.detail', $anime->slug) }}" class="group">
                <img src="{{ $anime->thumbnail_url }}" 
                     class="rounded-lg w-full h-48 object-cover group-hover:scale-105 transition" 
                     alt="">
                <h3 class="text-sm text-gray-300 mt-2 group-hover:text-white">
                    {{ $anime->title }}
                </h3>
            </a>
            @endforeach
        </div>
<<<<<<< HEAD
    </section>
=======
        @endif
>>>>>>> cc1abab59e4a91ec22ccd7b474e0817473907c84

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
>>>>>>> e49809d9d6911bdf67fad69ca28d173fa3ca9407
    @endif


<<<<<<< HEAD
    <div class="row">
        <div class="col-lg-9 d-flex flex-column gap-4">

=======
    {{-- ✅ LATEST EPISODES --}}
    <section class="mb-10">
        <h2 class="text-lg font-bold text-white mb-4">Latest Episodes</h2>

<<<<<<< HEAD
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($latestEpisodes ?? [] as $episode)
            <a href="{{ route('watch', ['slug' => $episode->anime->slug, 'ep' => $episode->number]) }}">
                <img src="{{ $episode->thumbnail_url }}" class="rounded-lg w-full h-48 object-cover">
                <p class="text-sm text-gray-300 mt-2">{{ $episode->anime->title }}</p>
                <p class="text-xs text-gray-500">Episode {{ $episode->number }}</p>
            </a>
            @endforeach
        </div>
    </section>

=======
            {{-- Latest Episode --}}
>>>>>>> cc1abab59e4a91ec22ccd7b474e0817473907c84
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
<<<<<<< HEAD

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

=======
            </section>
            @endif
>>>>>>> e49809d9d6911bdf67fad69ca28d173fa3ca9407

    {{-- ✅ 3 COLUMN SECTION --}}
    <div class="grid md:grid-cols-3 gap-6">

        {{-- ✅ NEW RELEASE --}}
        <div>
            <h3 class="text-sm font-bold text-white mb-3">New Release</h3>

            @foreach(($newAnime ?? collect())->take(5) as $anime)
            <a href="{{ route('anime.detail', $anime->slug) }}" class="flex gap-3 mb-2">
                <img src="{{ $anime->thumbnail_url }}" class="w-10 h-14 rounded">
                <p class="text-sm text-gray-300">{{ $anime->title }}</p>
            </a>
            @endforeach
        </div>

        {{-- ✅ NEWLY ADDED (FIXED) --}}
        <div>
            <h3 class="text-sm font-bold text-white mb-3">Newly Added</h3>

            {{-- ✅ USE newAnime --}}
            @foreach(($newAnime ?? []) as $anime)
            <a href="{{ route('anime.detail', $anime->slug) }}" class="flex gap-3 mb-2">
                <img src="{{ $anime->thumbnail_url }}" class="w-10 h-14 rounded">
                <p class="text-sm text-gray-300">{{ $anime->title }}</p>
            </a>
            @endforeach
        </div>

        {{-- ✅ COMPLETED (FIXED) --}}
        <div>
            <h3 class="text-sm font-bold text-white mb-3">Completed</h3>

            {{-- ✅ USE completed --}}
            @foreach(($completed ?? []) as $anime)
            <a href="{{ route('anime.detail', $anime->slug) }}" class="flex gap-3 mb-2">
                <img src="{{ $anime->thumbnail_url }}" class="w-10 h-14 rounded">
                <p class="text-sm text-gray-300">{{ $anime->title }}</p>
            </a>
            @endforeach
        </div>

>>>>>>> cc1abab59e4a91ec22ccd7b474e0817473907c84
    </div>


    {{-- ✅ TOP ANIME (SIDEBAR) --}}
    <div class="mt-10">
        <h3 class="text-lg font-bold text-white mb-4">Top Anime</h3>

        {{-- ✅ USE trending --}}
        @foreach(($trending ?? []) as $i => $anime)
        <div class="flex items-center mb-3">

            <span class="w-5 text-gray-500">
                {{ $i + 1 }}
            </span>

            <img src="{{ $anime->thumbnail_url }}" 
                 class="w-10 h-14 object-cover rounded mx-2">

            <div>
                <p class="text-sm text-gray-300">
                    {{ $anime->title }}
                </p>
                <p class="text-xs text-gray-500">
                    {{ $anime->rating ?? 'N/A' }}
                </p>
            </div>

        </div>
        @endforeach
    </div>

</div>

@endsection