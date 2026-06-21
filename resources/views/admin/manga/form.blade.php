@extends('admin.layouts.app')

@section('content')

<div class="max-w-4xl mx-auto">

    {{-- TITLE --}}
    <h1 class="text-xl font-semibold text-white mb-6">
        {{ isset($manga) ? 'Edit Manga' : 'Create Manga' }}
    </h1>

    <form action="{{ isset($manga) ? route('admin.manga.update', $manga) : route('admin.manga.store') }}"
          method="POST"
          enctype="multipart/form-data">

        @csrf
        @if(isset($manga)) @method('PUT') @endif

        {{-- GRID --}}
        <div class="grid md:grid-cols-2 gap-4">

            {{-- TITLE --}}
            <div>
                <label class="text-sm text-gray-400 mb-1 block">Title</label>
                <input type="text" name="title"
                       value="{{ old('title', $manga->title ?? '') }}"
                       required
                       class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white">
            </div>

            {{-- TYPE --}}
            <div>
                <label class="text-sm text-gray-400 mb-1 block">Type</label>
                <select name="type"
                        class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white">
                    @foreach(['Manga','Manhwa','Manhua','One-shot','Doujinshi'] as $type)
                        <option value="{{ $type }}" @selected(old('type', $manga->type ?? '') == $type)>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- STATUS --}}
            <div>
                <label class="text-sm text-gray-400 mb-1 block">Status</label>
                <select name="status"
                        class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white">
                    @foreach(['Ongoing','Completed','Hiatus','Cancelled'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $manga->status ?? '') == $status)>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- YEAR --}}
            <div>
                <label class="text-sm text-gray-400 mb-1 block">Year</label>
                <input type="number" name="year"
                       value="{{ old('year', $manga->year ?? '') }}"
                       class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white">
            </div>

            {{-- RATING --}}
            <div>
                <label class="text-sm text-gray-400 mb-1 block">Rating</label>
                <input type="number" step="0.1" name="rating"
                       value="{{ old('rating', $manga->rating ?? '') }}"
                       class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white">
            </div>

            {{-- SCORE --}}
            <div>
                <label class="text-sm text-gray-400 mb-1 block">Score</label>
                <input type="number" step="0.1" name="score"
                       value="{{ old('score', $manga->score ?? '') }}"
                       class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white">
            </div>

            {{-- AUTHOR --}}
            <div>
                <label class="text-sm text-gray-400 mb-1 block">Author</label>
                <input type="text" name="author"
                       value="{{ old('author', $manga->author ?? '') }}"
                       class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white">
            </div>

            {{-- ARTIST --}}
            <div>
                <label class="text-sm text-gray-400 mb-1 block">Artist</label>
                <input type="text" name="artist"
                       value="{{ old('artist', $manga->artist ?? '') }}"
                       class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white">
            </div>

            {{-- PUBLISHER --}}
            <div>
                <label class="text-sm text-gray-400 mb-1 block">Publisher</label>
                <input type="text" name="publisher"
                       value="{{ old('publisher', $manga->publisher ?? '') }}"
                       class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white">
            </div>

            {{-- SOURCE --}}
            <div>
                <label class="text-sm text-gray-400 mb-1 block">Source</label>
                <input type="text" name="source"
                       value="{{ old('source', $manga->source ?? '') }}"
                       class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white">
            </div>

        </div>

        {{-- ALT TITLES --}}
        <div class="mt-5">
            <label class="text-sm text-gray-400 mb-1 block">Alternative Titles</label>
            <input type="text" name="alternative_titles"
                   value="{{ old('alternative_titles', $manga->alternative_titles ?? '') }}"
                   class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white">
        </div>

        {{-- DESCRIPTION --}}
        <div class="mt-5">
            <label class="text-sm text-gray-400 mb-1 block">Description</label>
            <textarea name="description"
                      rows="5"
                      class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white">{{ old('description', $manga->description ?? '') }}</textarea>
        </div>

        {{-- FILES --}}
        <div class="grid md:grid-cols-3 gap-4 mt-5">

            <div>
                <label class="text-sm text-gray-400 mb-1 block">Thumbnail</label>
                <input type="file"
                       name="thumbnail"
                       class="w-full bg-gray-900 border border-gray-700 rounded-lg px-2 py-2 text-gray-300">
            </div>

            <div>
                <label class="text-sm text-gray-400 mb-1 block">Banner</label>
                <input type="file"
                       name="banner"
                       class="w-full bg-gray-900 border border-gray-700 rounded-lg px-2 py-2 text-gray-300">
            </div>

            <div class="flex items-end">
                <label class="flex items-center gap-2 text-sm text-gray-300">
                    <input type="checkbox"
                           name="featured"
                           @checked(old('featured', $manga->featured ?? false))>
                    Featured
                </label>
            </div>

        </div>

        {{-- GENRES --}}
        <div class="mt-5">
            <label class="text-sm text-gray-400 mb-2 block">Genres</label>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">

                @foreach($genres as $genre)
                <label class="flex items-center gap-2 text-sm text-gray-300">
                    <input type="checkbox"
                           name="genres[]"
                           value="{{ $genre->id }}"
                           @checked(isset($manga) && $manga->genres->contains($genre->id))>
                    {{ $genre->name }}
                </label>
                @endforeach

            </div>
        </div>

        {{-- ACTION --}}
        <button type="submit"
                class="mt-6 px-6 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg">
            {{ isset($manga) ? 'Update Manga' : 'Create Manga' }}
        </button>

    </form>

</div>

@endsection