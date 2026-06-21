@extends('admin.layouts.app')

@section('content')

<div class="max-w-4xl mx-auto">

    {{-- TITLE --}}
    <h1 class="text-xl font-semibold text-white mb-6">
        {{ isset($episode) ? 'Edit Episode' : 'Create Episode' }} for {{ $anime->title }}
    </h1>

    <form
        method="POST"
        action="{{ isset($episode) ? route('admin.anime.episodes.update', [$anime, $episode]) : route('admin.anime.episodes.store', $anime) }}"
        enctype="multipart/form-data"
        x-data="adminForm({ key: 'episode_form_{{ $episode->id ?? 'new' }}' })"
        x-init="init()"
        @input.debounce.500ms="saveDraft()"
        @change.debounce.500ms="saveDraft()"
        @submit="submit($event)"
    >

        @csrf
        @if(isset($episode)) @method('PUT') @endif

        {{-- GRID --}}
        <div class="grid md:grid-cols-2 gap-4">

            <div>
                <label class="form-label">Episode Number</label>
                <input type="number" name="number"
                       value="{{ old('number', $episode->number ?? '') }}"
                       required
                       class="form-input">
            </div>

            <div>
                <label class="form-label">Duration</label>
                <input type="number" name="duration"
                       value="{{ old('duration', $episode->duration ?? '') }}"
                       class="form-input">
            </div>

            <div>
                <label class="form-label">Title</label>
                <input type="text" name="title"
                       value="{{ old('title', $episode->title ?? '') }}"
                       class="form-input">
            </div>

            <div>
                <label class="form-label">Air Date</label>
                <input type="date" name="air_date"
                       value="{{ old('air_date', $episode->air_date ?? '') }}"
                       class="form-input">
            </div>

        </div>

        {{-- DESCRIPTION --}}
        <div class="mt-5">
            <label class="form-label">Description</label>
            <textarea name="description" rows="3"
                      class="form-input">{{ old('description', $episode->description ?? '') }}</textarea>
        </div>

        {{-- FLAGS --}}
        <div class="flex gap-6 mt-5 text-sm text-gray-300">

            <label class="flex items-center gap-2">
                <input type="checkbox" name="has_sub"
                       @checked(old('has_sub', $episode->has_sub ?? true))>
                Sub
            </label>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="has_dub"
                       @checked(old('has_dub', $episode->has_dub ?? false))>
                Dub
            </label>

        </div>

        {{-- VIDEO SOURCE --}}
        <div class="mt-6 pt-4 border-t border-gray-700">

            <label class="text-white font-medium block mb-3">
                Video Source
            </label>

            <input type="hidden" name="source_type" value="upload">

            {{-- UPLOAD VIDEO --}}
            <div class="mt-2">
                <label class="form-label">Upload Video</label>
                <input type="file" name="video"
                       class="form-input text-gray-400">
            </div>

            {{-- URL --}}
            <div class="mt-3">
                <label class="form-label">External URL</label>
                <input type="text" name="video_path"
                       value="{{ old('video_path', $episode->video_path ?? '') }}"
                       placeholder="https://..."
                       class="form-input">
            </div>

        </div>

        {{-- THUMBNAIL --}}
        <div class="mt-6">
            <x-admin.dropzone
                name="thumbnail"
                label="Episode Thumbnail"
            />
        </div>

        {{-- ACTION --}}
        <div class="mt-6">
            <button type="submit" class="btn-primary">
                {{ isset($episode) ? 'Update Episode' : 'Create Episode' }}
            </button>
        </div>

    </form>

</div>

@endsection