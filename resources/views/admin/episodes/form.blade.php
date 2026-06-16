@extends('admin.layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">

    <h1 class="text-2xl font-semibold text-white mb-6">
        {{ isset($episode) ? 'Edit' : 'Create' }} Episode for {{ $anime->title }}
    </h1>

    <form method="POST"
        action="{{ isset($episode) ? route('admin.anime.episodes.update', [$anime, $episode]) : route('admin.anime.episodes.store', $anime) }}"
        enctype="multipart/form-data"
        class="space-y-6">

        @csrf
        @if(isset($episode)) @method('PUT') @endif

        <!-- Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div>
                <label class="text-gray-400 text-sm">Episode Number</label>
                <input type="number" name="number"
                    value="{{ old('number', $episode->number ?? '') }}"
                    class="form-input" required>
            </div>

            <div>
                <label class="text-gray-400 text-sm">Duration</label>
                <input type="number" name="duration"
                    value="{{ old('duration', $episode->duration ?? '') }}"
                    class="form-input">
            </div>

            <div>
                <label class="text-gray-400 text-sm">Title</label>
                <input type="text" name="title"
                    value="{{ old('title', $episode->title ?? '') }}"
                    class="form-input">
            </div>

            <div>
                <label class="text-gray-400 text-sm">Air Date</label>
                <input type="date" name="air_date"
                    value="{{ old('air_date', $episode->air_date ?? '') }}"
                    class="form-input">
            </div>

        </div>

        <!-- Description -->
        <div>
            <label class="text-gray-400 text-sm">Description</label>
            <textarea name="description" rows="3" class="form-input">
{{ old('description', $episode->description ?? '') }}
            </textarea>
        </div>

        <!-- Sub/Dub -->
        <div class="flex gap-6 text-gray-300">

            <label class="flex items-center gap-2">
                <input type="checkbox" name="has_sub"
                    @checked(old('has_sub', $episode->has_sub ?? true))
                    class="accent-indigo-500">
                Sub
            </label>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="has_dub"
                    @checked(old('has_dub', $episode->has_dub ?? false))
                    class="accent-indigo-500">
                Dub
            </label>

        </div>

        <!-- Source -->
        <div class="border-t border-gray-800 pt-4 space-y-4">

            <label class="text-gray-300 font-medium">Video Source</label>

            <input type="hidden" name="source_type" value="upload">

            <div>
                <label class="text-gray-400 text-sm">Upload Video</label>
                <input type="file" name="video" class="file-input">
            </div>

            <div>
                <label class="text-gray-400 text-sm">External URL</label>
                <input type="text" name="video_path"
                    value="{{ old('video_path', $episode->video_path ?? '') }}"
                    class="form-input"
                    placeholder="https://...">
            </div>

        </div>

        <!-- Thumbnail -->
        <div>
            <label class="text-gray-400 text-sm">Thumbnail</label>
            <input type="file" name="thumbnail" class="file-input">
        </div>

        <!-- Submit -->
        <div class="flex gap-3">
            <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2 rounded-lg transition">
                {{ isset($episode) ? 'Update Episode' : 'Create Episode' }}
            </button>
        </div>

    </form>

</div>

<style>
.form-input {
    @apply w-full mt-1 px-3 py-2 bg-[#1f2937] border border-gray-700 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500;
}
.file-input {
    @apply w-full text-sm text-gray-400 file:mr-3 file:px-4 file:py-2 file:bg-[#1f2937] file:text-white file:border-0 file:rounded-lg;
}
</style>

@endsection