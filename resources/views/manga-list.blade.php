@extends('layouts.main')

@section('title', $title)

@section('content')
<div class="container py-4">

    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 text-white mb-0">{{ $title }}</h1>
        <span class="badge bg-secondary">{{ $mangaList->total() }} manga</span>
    </div>

    <!-- Grid -->
    <div class="row g-3">
        @forelse($mangaList as $manga)
        <div class="col-6 col-md-4 col-lg-3 col-xl-2">
            <div class="card bg-dark border-secondary h-100">
                <a href="{{ route('manga.detail', $manga->slug) }}">
                    <img src="{{ $manga->thumbnail_url ?? 'https://via.placeholder.com/200x280?text=No+Image' }}"
                         alt="{{ $manga->title }}"
                         class="card-img-top"
                         style="aspect-ratio: 5/7; object-fit: cover;">
                </a>
                <div class="card-body p-2">
                    <a href="{{ route('manga.detail', $manga->slug) }}"
                       class="text-white text-decoration-none stretched-link">
                        <small class="fw-semibold">{{ $manga->title }}</small>
                    </a>
                    <div class="d-flex gap-1 mt-1 flex-wrap">
                        @foreach($manga->genres->take(2) as $genre)
                            <span class="badge bg-secondary" style="font-size: 0.6rem;">{{ $genre->name }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center text-secondary py-5">
                <p class="fs-5">No manga found</p>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-4 d-flex justify-content-center">
        {{ $mangaList->links() }}
    </div>

</div>
@endsection
