@extends('layouts.main')

@section('title', 'My Anime List')

@section('content')
<div class="container-fluid px-3 py-3" style="max-width:1280px">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="fw-semibold" style="color:#fff;font-size:1.5rem">My Anime List</h1>
            <p class="mt-1" style="color:#9ca3af;font-size:0.875rem">
                Manage your favorites and watch progress
            </p>
        </div>

        <span style="color:#6b7280;font-size:0.875rem">
            {{ $favorites->total() }} items
        </span>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-3">

        <a href="{{ route('favorites.my-list') }}"
           class="btn btn-sm" style="{{ !$activeCategory ? 'background:#4f46e5;color:#fff' : 'background:#1f2937;color:#9ca3af' }}border-radius:0.5rem;font-weight:500">
            All
        </a>

        @foreach($categories as $key => $label)
        <a href="{{ route('favorites.my-list', ['category'=>$key]) }}"
           class="btn btn-sm" style="{{ $activeCategory === $key ? 'background:#4f46e5;color:#fff' : 'background:#1f2937;color:#9ca3af' }}border-radius:0.5rem;font-weight:500">
            {{ $label }}
        </a>
        @endforeach

        <a href="{{ route('favorites.my-list', ['category'=>'favorites']) }}"
           class="btn btn-sm" style="{{ $activeCategory === 'favorites' ? 'background:#4f46e5;color:#fff' : 'background:#1f2937;color:#9ca3af' }}border-radius:0.5rem;font-weight:500">
            Favorites
        </a>

    </div>

    @if($favorites->count())

    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-3">

        @foreach($favorites as $fav)
        <div class="col">

            <a href="{{ route('anime.detail', $fav->anime->slug) }}" class="text-decoration-none d-block">

                <div style="position:relative;border-radius:0.75rem;overflow:hidden">

                    <img
                        src="{{ $fav->anime->thumbnail_url }}"
                        alt="{{ $fav->anime->title }}"
                        style="width:100%;height:240px;object-fit:cover;border-radius:0.75rem;transition:transform 0.3s"
                        loading="lazy"
                    >

                    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.7);display:flex;align-items:center;justify-content:center;opacity:0;color:#fff;font-size:0.875rem;transition:opacity 0.3s">
                        ▶ View
                    </div>

                    <span style="position:absolute;top:0.5rem;right:0.5rem;font-size:0.75rem;padding:0.25rem 0.5rem;border-radius:0.25rem;color:#fff;{{ $fav->category === 'watching' ? 'background:#2563eb' : ($fav->category === 'completed' ? 'background:#16a34a' : ($fav->category === 'plan_to_watch' ? 'background:#eab308' : ($fav->category === 'on_hold' ? 'background:#f97316' : ($fav->category === 'dropped' ? 'background:#dc2626' : 'background:#4f46e5')))) }}">
                        {{ $fav->category ? ($categories[$fav->category] ?? $fav->category) : 'Favorites' }}
                    </span>

                </div>

                <h3 style="color:#d1d5db;font-size:0.875rem;margin-top:0.5rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    {{ $fav->anime->title }}
                </h3>

                <div style="color:#6b7280;font-size:0.75rem;margin-top:0.25rem">
                    {{ $fav->anime->type ?? 'N/A' }} · {{ $fav->anime->episodes_count ?? '?' }} eps
                </div>

            </a>

        </div>
        @endforeach

    </div>

    <div class="mt-4">
        {{ $favorites->links() }}
    </div>

    @else

    <div class="text-center py-5">

        <div style="font-size:3rem;margin-bottom:1rem">📺</div>

        <p style="color:#9ca3af;font-size:1.125rem">
            Your list is empty
        </p>

        <p class="mt-2" style="color:#6b7280;font-size:0.875rem">
            Start adding anime to track your progress
        </p>

        <a href="{{ route('home') }}"
           class="btn mt-3" style="background:#4f46e5;color:#fff">
            Browse Anime
        </a>

    </div>

    @endif

</div>
@endsection