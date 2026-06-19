@extends('admin.layouts.app')

@section('content')
<div class="container" style="max-width:900px">

    <h1 class="h4 fw-semibold text-white mb-3">
        {{ isset($anime) ? 'Edit Anime' : 'Create Anime' }}
    </h1>

    <form action="{{ isset($anime) ? route('admin.anime.update', $anime) : route('admin.anime.store') }}"
          method="POST"
          enctype="multipart/form-data">

        @csrf
        @if(isset($anime)) @method('PUT') @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="small" style="color:#9ca3af">Title</label>
                <input type="text" name="title"
                    value="{{ old('title', $anime->title ?? '') }}"
                    class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff" required>
            </div>
            <div class="col-md-6">
                <label class="small" style="color:#9ca3af">Type</label>
                <select name="type" class="form-select" style="background:#1f2937;border:1px solid #4b5563;color:#fff">
                    <option value="">Select</option>
                    @foreach(['TV','Movie','OVA','ONA','Special'] as $t)
                        <option value="{{ $t }}" @selected(old('type', $anime->type ?? '')==$t)>
                            {{ $t }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="small" style="color:#9ca3af">Status</label>
                <select name="status" class="form-select" style="background:#1f2937;border:1px solid #4b5563;color:#fff">
                    @foreach(['Ongoing','Completed','Upcoming'] as $s)
                        <option value="{{ $s }}" @selected(old('status', $anime->status ?? '')==$s)>
                            {{ $s }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="small" style="color:#9ca3af">Year</label>
                <input type="number" name="year"
                    value="{{ old('year', $anime->year ?? '') }}"
                    class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff">
            </div>
            <div class="col-md-6">
                <label class="small" style="color:#9ca3af">Season</label>
                <select name="season" class="form-select" style="background:#1f2937;border:1px solid #4b5563;color:#fff">
                    @foreach(['Winter','Spring','Summer','Fall'] as $season)
                        <option value="{{ $season }}" @selected(old('season', $anime->season ?? '')==$season)>
                            {{ $season }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="small" style="color:#9ca3af">Rating</label>
                <input type="number" step="0.1" name="rating"
                    value="{{ old('rating', $anime->rating ?? '') }}"
                    class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff">
            </div>
            <div class="col-md-6">
                <label class="small" style="color:#9ca3af">Duration</label>
                <input type="number" name="duration"
                    value="{{ old('duration', $anime->duration ?? '') }}"
                    class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff">
            </div>
            <div class="col-md-6">
                <label class="small" style="color:#9ca3af">Studio</label>
                <input type="text" name="studio"
                    value="{{ old('studio', $anime->studio ?? '') }}"
                    class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff">
            </div>
            <div class="col-md-6">
                <label class="small" style="color:#9ca3af">Country</label>
                <input type="text" name="country"
                    value="{{ old('country', $anime->country ?? '') }}"
                    class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff">
            </div>
        </div>

        <div class="mt-3">
            <label class="small" style="color:#9ca3af">Description</label>
            <textarea name="description" rows="4" class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff">{{ old('description', $anime->description ?? '') }}</textarea>
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
                    <input type="checkbox" name="featured" value="1"
                        @checked(old('featured', $anime->featured ?? false))>
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
                            @checked(isset($anime) && $anime->genres->contains($genre->id))>
                        {{ $genre->name }}
                    </label>
                </div>
                @endforeach
            </div>
        </div>

        <button type="submit" class="btn mt-3" style="background:#4f46e5;color:#fff">
            {{ isset($anime) ? 'Update Anime' : 'Create Anime' }}
        </button>

    </form>
</div>

@endsection