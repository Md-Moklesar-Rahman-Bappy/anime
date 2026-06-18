@extends('admin.layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">

    <h1 class="text-2xl font-semibold text-white mb-6">
        {{ isset($anime) ? 'Edit Anime' : 'Create Anime' }}
    </h1>

    <form action="{{ isset($anime) ? route('admin.anime.update', $anime) : route('admin.anime.store') }}"
          method="POST"
          enctype="multipart/form-data"
          class="space-y-6">

        @csrf
        @if(isset($anime)) @method('PUT') @endif

        <!-- Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div>
                <label class="text-gray-400 text-sm">Title</label>
                <input type="text" name="title"
                    value="{{ old('title', $anime->title ?? '') }}"
                    class="form-input" required>
            </div>

            <div>
                <label class="text-gray-400 text-sm">Type</label>
                <select name="type" class="form-input">
                    <option value="">Select</option>
                    @foreach(['TV','Movie','OVA','ONA','Special'] as $t)
                        <option value="{{ $t }}" @selected(old('type', $anime->type ?? '')==$t)>
                            {{ $t }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-gray-400 text-sm">Status</label>
                <select name="status" class="form-input">
                    @foreach(['Ongoing','Completed','Upcoming'] as $s)
                        <option value="{{ $s }}" @selected(old('status', $anime->status ?? '')==$s)>
                            {{ $s }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-gray-400 text-sm">Year</label>
                <input type="number" name="year"
                    value="{{ old('year', $anime->year ?? '') }}"
                    class="form-input">
            </div>

            <div>
                <label class="text-gray-400 text-sm">Season</label>
                <select name="season" class="form-input">
                    @foreach(['Winter','Spring','Summer','Fall'] as $season)
                        <option value="{{ $season }}" @selected(old('season', $anime->season ?? '')==$season)>
                            {{ $season }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-gray-400 text-sm">Rating</label>
                <input type="number" step="0.1" name="rating"
                    value="{{ old('rating', $anime->rating ?? '') }}"
                    class="form-input">
            </div>

            <div>
                <label class="text-gray-400 text-sm">Duration</label>
                <input type="number" name="duration"
                    value="{{ old('duration', $anime->duration ?? '') }}"
                    class="form-input">
            </div>

            <div>
                <label class="text-gray-400 text-sm">Studio</label>
                <input type="text" name="studio"
                    value="{{ old('studio', $anime->studio ?? '') }}"
                    class="form-input">
            </div>

            <div>
                <label class="text-gray-400 text-sm">Country</label>
                <input type="text" name="country"
                    value="{{ old('country', $anime->country ?? '') }}"
                    class="form-input">
            </div>

        </div>

        <!-- Description -->
        <div>
            <label class="text-gray-400 text-sm">Description</label>
            <textarea name="description" rows="4" class="form-input">
{{ old('description', $anime->description ?? '') }}
            </textarea>
        </div>

        <!-- Uploads -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <div>
                <label class="text-gray-400 text-sm">Thumbnail</label>
                <input type="file" name="thumbnail" class="file-input">
            </div>

            <div>
                <label class="text-gray-400 text-sm">Banner</label>
                <input type="file" name="banner" class="file-input">
            </div>

            <div class="flex items-end">
                <label class="flex items-center gap-2 text-gray-300">
                    <input type="checkbox" name="featured" value="1"
                        @checked(old('featured', $anime->featured ?? false))
                        class="accent-indigo-500">
                    Featured
                </label>
            </div>

        </div>

        <!-- Genres -->
        <div>
            <label class="text-gray-400 text-sm mb-2 block">Genres</label>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">

                @foreach($genres as $genre)
                <label class="flex items-center gap-2 text-sm text-gray-300">
                    <input type="checkbox"
                        name="genres[]"
                        value="{{ $genre->id }}"
                        @checked(isset($anime) && $anime->genres->contains($genre->id))
                        class="accent-indigo-500">
                    {{ $genre->name }}
                </label>
                @endforeach

            </div>
        </div>

        <!-- Submit -->
        <button type="submit"
            class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2 rounded-lg transition">
            {{ isset($anime) ? 'Update Anime' : 'Create Anime' }}
        </button>

    </form>
</div>

{{-- Reusable styles --}}
<style>
.form-input {
    @apply w-full mt-1 px-3 py-2 bg-[#1f2937] border border-gray-700 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500;
}
.file-input {
    @apply w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-[#1f2937] file:text-white;
}
</style>

@endsection