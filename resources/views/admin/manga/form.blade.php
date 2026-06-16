@extends('admin.layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">

    <h1 class="text-2xl font-semibold text-white mb-6">
        {{ isset($manga) ? 'Edit Manga' : 'Create Manga' }}
    </h1>

    {{ isset($manga) ? route('admin.manga.update', $manga) : route('admin.manga.store') }}

        @csrf
        @if(isset($manga)) @method('PUT') @endif

        <!-- Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div>
                <label class="text-gray-400 text-sm">Title</label>
                <input type="text" name="title"
                    value="{{ old('title', $manga->title ?? '') }}"
                    class="form-input" required>
            </div>

            <div>
                <label class="text-gray-400 text-sm">Type</label>
                <select name="type" class="form-input">
                    @foreach(['Manga','Manhwa','Manhua','One-shot','Doujinshi'] as $type)
                        <option value="{{ $type }}"
                            @selected(old('type', $manga->type ?? '') == $type)>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-gray-400 text-sm">Status</label>
                <select name="status" class="form-input">
                    @foreach(['Ongoing','Completed','Hiatus','Cancelled'] as $status)
                        <option value="{{ $status }}"
                            @selected(old('status', $manga->status ?? '') == $status)>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-gray-400 text-sm">Year</label>
                <input type="number" name="year"
                    value="{{ old('year', $manga->year ?? '') }}"
                    class="form-input">
            </div>

            <div>
                <label class="text-gray-400 text-sm">Rating</label>
                <input type="number" step="0.1" name="rating"
                    value="{{ old('rating', $manga->rating ?? '') }}"
                    class="form-input">
            </div>

            <div>
                <label class="text-gray-400 text-sm">Score</label>
                <input type="number" step="0.1" name="score"
                    value="{{ old('score', $manga->score ?? '') }}"
                    class="form-input">
            </div>

            <div>
                <label class="text-gray-400 text-sm">Author</label>
                <input type="text" name="author"
                    value="{{ old('author', $manga->author ?? '') }}"
                    class="form-input">
            </div>

            <div>
                <label class="text-gray-400 text-sm">Artist</label>
                <input type="text" name="artist"
                    value="{{ old('artist', $manga->artist ?? '') }}"
                    class="form-input">
            </div>

            <div>
                <label class="text-gray-400 text-sm">Publisher</label>
                <input type="text" name="publisher"
                    value="{{ old('publisher', $manga->publisher ?? '') }}"
                    class="form-input">
            </div>

            <div>
                <label class="text-gray-400 text-sm">Source</label>
                <input type="text" name="source"
                    value="{{ old('source', $manga->source ?? '') }}"
                    class="form-input">
            </div>

        </div>

        <!-- Extra -->
        <div>
            <label class="text-gray-400 text-sm">Alternative Titles</label>
            <input type="text" name="alternative_titles"
                value="{{ old('alternative_titles', $manga->alternative_titles ?? '') }}"
                class="form-input">
        </div>

        <div>
            <label class="text-gray-400 text-sm">Description</label>
            <textarea name="description" rows="5" class="form-input">
{{ old('description', $manga->description ?? '') }}
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
                    <input type="checkbox" name="featured"
                        @checked(old('featured', $manga->featured ?? false))
                        class="accent-emerald-500">
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
                        @checked(isset($manga) && $manga->genres->contains($genre->id))
                        class="accent-emerald-500">
                    {{ $genre->name }}
                </label>
                @endforeach

            </div>
        </div>

        <!-- Submit -->
        <button type="submit"
            class="bg-emerald-600 hover:bg-emerald-500 text-white px-6 py-2 rounded-lg transition">
            {{ isset($manga) ? 'Update Manga' : 'Create Manga' }}
        </button>

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