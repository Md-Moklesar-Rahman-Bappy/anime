@extends('admin.layouts.app')

@section('content')
<div class="container" style="max-width:900px">

    <h1 class="h4 fw-semibold text-white mb-3">
        {{ isset($chapter) ? 'Edit' : 'Create' }} Chapter - {{ $manga->title }}
    </h1>

    <form action="{{ isset($chapter) ? route('admin.manga.chapters.update', [$manga, $chapter]) : route('admin.manga.chapters.store', $manga) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if(isset($chapter)) @method('PUT') @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="small" style="color:#9ca3af">Chapter Number</label>
                <input type="number" step="0.1" name="number" value="{{ old('number', $chapter->number ?? '') }}"
                    class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff" required>
            </div>
            <div class="col-md-6">
                <label class="small" style="color:#9ca3af">Title (optional)</label>
                <input type="text" name="title" value="{{ old('title', $chapter->title ?? '') }}"
                    class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff">
            </div>
        </div>

        <div class="mt-3">
            <label class="small" style="color:#9ca3af">Upload Page Images</label>
            <input type="file" name="pages[]" multiple accept="image/*"
                class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#9ca3af">
            <p class="small mt-1" style="color:#6b7280">Images sorted alphabetically (jpg, png, webp)</p>
        </div>

        <div class="mt-3">
            <label class="small" style="color:#9ca3af">Or Image URLs</label>
            <textarea name="page_urls" rows="4" class="form-control"
                style="background:#1f2937;border:1px solid #4b5563;color:#fff"
                placeholder="https://example.com/page-01.jpg">{{ old('page_urls', isset($chapter) ? $chapter->pages->pluck('image_path')->implode("\n") : '') }}</textarea>
        </div>

        @if(isset($chapter) && $chapter->pages->count())
        <div class="mt-3">
            <label class="small mb-2 d-block" style="color:#9ca3af">Existing Pages</label>
            <div class="row row-cols-2 row-cols-md-6 g-2">
                @foreach($chapter->pages as $page)
                <div class="col position-relative">
                    <img src="{{ $page->image_url ?? $page->image_path }}" class="w-100" style="aspect-ratio:3/4;object-fit:cover;border-radius:0.5rem">
                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background:rgba(0,0,0,0.6);opacity:0;border-radius:0.5rem">
                        <label class="d-flex align-items-center gap-1 small text-white cursor-pointer">
                            <input type="checkbox" name="delete_pages[]" value="{{ $page->id }}">
                            Delete
                        </label>
                    </div>
                    <span class="small text-center d-block mt-1" style="color:#6b7280">P. {{ $page->page_number }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn" style="background:#059669;color:#fff">
                {{ isset($chapter) ? 'Update' : 'Create' }}
            </button>
            <a href="{{ route('admin.manga.chapters.index', $manga) }}" class="btn" style="background:#1f2937;color:#fff">Cancel</a>
        </div>

    </form>
</div>

@endsection