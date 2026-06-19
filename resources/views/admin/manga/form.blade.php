@extends('admin.layouts.app')

@section('content')
<div class="container" style="max-width:900px">

    <h1 class="h4 fw-semibold text-white mb-3">
        {{ isset($manga) ? 'Edit Manga' : 'Create Manga' }}
    </h1>

    <form action="{{ isset($manga) ? route('admin.manga.update', $manga) : route('admin.manga.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if(isset($manga)) @method('PUT') @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="small" style="color:#9ca3af">Title</label>
                <input type="text" name="title" value="{{ old('title', $manga->title ?? '') }}"
                    class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff" required>
            </div>
            <div class="col-md-6">
                <label class="small" style="color:#9ca3af">Type</label>
                <select name="type" class="form-select" style="background:#1f2937;border:1px solid #4b5563;color:#fff">
                    @foreach(['Manga','Manhwa','Manhua','One-shot','Doujinshi'] as $type)
                        <option value="{{ $type }}" @selected(old('type', $manga->type ?? '') == $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="small" style="color:#9ca3af">Status</label>
                <select name="status" class="form-select" style="background:#1f2937;border:1px solid #4b5563;color:#fff">
                    @foreach(['Ongoing','Completed','Hiatus','Cancelled'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $manga->status ?? '') == $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="small" style="color:#9ca3af">Year</label>
                <input type="number" name="year" value="{{ old('year', $manga->year ?? '') }}"
                    class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff">
            </div>
            <div class="col-md-6">
                <label class="small" style="color:#9ca3af">Rating</label>
                <input type="number" step="0.1" name="rating" value="{{ old('rating', $manga->rating ?? '') }}"
                    class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff">
            </div>
            <div class="col-md-6">
                <label class="small" style="color:#9ca3af">Score</label>
                <input type="number" step="0.1" name="score" value="{{ old('score', $manga->score ?? '') }}"
                    class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff">
            </div>
            <div class="col-md-6">
                <label class="small" style="color:#9ca3af">Author</label>
                <input type="text" name="author" value="{{ old('author', $manga->author ?? '') }}"
                    class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff">
            </div>
            <div class="col-md-6">
                <label class="small" style="color:#9ca3af">Artist</label>
                <input type="text" name="artist" value="{{ old('artist', $manga->artist ?? '') }}"
                    class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff">
            </div>
            <div class="col-md-6">
                <label class="small" style="color:#9ca3af">Publisher</label>
                <input type="text" name="publisher" value="{{ old('publisher', $manga->publisher ?? '') }}"
                    class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff">
            </div>
            <div class="col-md-6">
                <label class="small" style="color:#9ca3af">Source</label>
                <input type="text" name="source" value="{{ old('source', $manga->source ?? '') }}"
                    class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff">
            </div>
        </div>

        <div class="mt-3">
            <label class="small" style="color:#9ca3af">Alternative Titles</label>
            <input type="text" name="alternative_titles" value="{{ old('alternative_titles', $manga->alternative_titles ?? '') }}"
                class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff">
        </div>

        <div class="mt-3">
            <label class="small" style="color:#9ca3af">Description</label>
            <textarea name="description" rows="5" class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff">{{ old('description', $manga->description ?? '') }}</textarea>
        </div>

        <div class="row g-3 mt-2">
            <div class="col-md-4">
                <label class="small" style="color:#9ca3af">Thumbnail</label>
                <input type="file" name="thumbnail" class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#9ca3af">
            </div>
            <div class="col-md-4">
                <label class="small" style="color:#9ca3af">Banner</label>
                <input type="file" name="banner" class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#9ca3af">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <label class="d-flex align-items-center gap-2" style="color:#d1d5db">
                    <input type="checkbox" name="featured" @checked(old('featured', $manga->featured ?? false))>
                    Featured
                </label>
            </div>
        </div>

        <div class="mt-3">
            <label class="small mb-2 d-block" style="color:#9ca3af">Genres</label>
            <div class="row row-cols-2 row-cols-md-4 g-2">
                @foreach($genres as $genre)
                <div class="col">
                    <label class="d-flex align-items-center gap-2 small" style="color:#d1d5db">
                        <input type="checkbox" name="genres[]" value="{{ $genre->id }}"
                            @checked(isset($manga) && $manga->genres->contains($genre->id))>
                        {{ $genre->name }}
                    </label>
                </div>
                @endforeach
            </div>
        </div>

        <button type="submit" class="btn mt-3" style="background:#059669;color:#fff">
            {{ isset($manga) ? 'Update Manga' : 'Create Manga' }}
        </button>

    </form>
</div>

@endsection