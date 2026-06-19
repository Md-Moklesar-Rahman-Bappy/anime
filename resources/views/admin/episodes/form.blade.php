@extends('admin.layouts.app')

@section('content')
<div class="container" style="max-width:900px">

    <h1 class="h4 fw-semibold text-white mb-3">
        {{ isset($episode) ? 'Edit' : 'Create' }} Episode for {{ $anime->title }}
    </h1>

    <form method="POST"
        action="{{ isset($episode) ? route('admin.anime.episodes.update', [$anime, $episode]) : route('admin.anime.episodes.store', $anime) }}"
        enctype="multipart/form-data">

        @csrf
        @if(isset($episode)) @method('PUT') @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="small" style="color:#9ca3af">Episode Number</label>
                <input type="number" name="number"
                    value="{{ old('number', $episode->number ?? '') }}"
                    class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff" required>
            </div>
            <div class="col-md-6">
                <label class="small" style="color:#9ca3af">Duration</label>
                <input type="number" name="duration"
                    value="{{ old('duration', $episode->duration ?? '') }}"
                    class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff">
            </div>
            <div class="col-md-6">
                <label class="small" style="color:#9ca3af">Title</label>
                <input type="text" name="title"
                    value="{{ old('title', $episode->title ?? '') }}"
                    class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff">
            </div>
            <div class="col-md-6">
                <label class="small" style="color:#9ca3af">Air Date</label>
                <input type="date" name="air_date"
                    value="{{ old('air_date', $episode->air_date ?? '') }}"
                    class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff">
            </div>
        </div>

        <div class="mt-3">
            <label class="small" style="color:#9ca3af">Description</label>
            <textarea name="description" rows="3" class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff">{{ old('description', $episode->description ?? '') }}</textarea>
        </div>

        <div class="d-flex gap-4 mt-3" style="color:#d1d5db">
            <label class="d-flex align-items-center gap-2">
                <input type="checkbox" name="has_sub"
                    @checked(old('has_sub', $episode->has_sub ?? true))>
                Sub
            </label>
            <label class="d-flex align-items-center gap-2">
                <input type="checkbox" name="has_dub"
                    @checked(old('has_dub', $episode->has_dub ?? false))>
                Dub
            </label>
        </div>

        <div class="mt-3 pt-3" style="border-top:1px solid #374151">
            <label class="fw-medium" style="color:#d1d5db">Video Source</label>
            <input type="hidden" name="source_type" value="upload">

            <div class="mt-2">
                <label class="small" style="color:#9ca3af">Upload Video</label>
                <input type="file" name="video" class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#9ca3af">
            </div>
            <div class="mt-2">
                <label class="small" style="color:#9ca3af">External URL</label>
                <input type="text" name="video_path"
                    value="{{ old('video_path', $episode->video_path ?? '') }}"
                    class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff"
                    placeholder="https://...">
            </div>
        </div>

        <div class="mt-3">
            <label class="small" style="color:#9ca3af">Thumbnail</label>
            <input type="file" name="thumbnail" class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#9ca3af">
        </div>

        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn" style="background:#4f46e5;color:#fff">
                {{ isset($episode) ? 'Update Episode' : 'Create Episode' }}
            </button>
        </div>

    </form>

</div>

@endsection