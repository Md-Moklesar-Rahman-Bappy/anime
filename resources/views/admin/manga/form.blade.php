@extends('admin.layouts.app')

@section('content')

<div class="max-w-4xl mx-auto">

    {{-- TITLE --}}
    <h1 class="text-xl font-semibold text-white mb-6">
        {{ isset($manga) ? 'Edit Manga' : 'Create Manga' }}
    </h1>

    <form action="{{ isset($manga) ? route('admin.manga.update', $manga) : route('admin.manga.store') }}"
          method="POST"
          enctype="multipart/form-data"
          x-data="adminForm({ id: 'manga-{{ $manga->id ?? 'new' }}' })"
          x-init="init()"
          @input.debounce.500ms="saveDraft()"
          @change.debounce.500ms="saveDraft()"
          @submit="submit($event)"
    >

        @csrf
        @if(isset($manga)) @method('PUT') @endif

        {{-- GRID --}}
        <div class="grid md:grid-cols-2 gap-4">

            {{-- TITLE --}}
            <div>
                <label class="form-label">Title</label>
                <input type="text" name="title"
                       value="{{ old('title', $manga->title ?? '') }}"
                       required
                       class="form-input">
            </div>

            {{-- TYPE --}}
            <div>
                <label class="form-label">Type</label>
                <select name="type" class="form-input">
                    @foreach(['Manga','Manhwa','Manhua','One-shot','Doujinshi'] as $type)
                        <option value="{{ $type }}" @selected(old('type', $manga->type ?? '') == $type)>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- STATUS --}}
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-input">
                    @foreach(['Ongoing','Completed','Hiatus','Cancelled'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $manga->status ?? '') == $status)>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- YEAR --}}
            <div>
                <label class="form-label">Year</label>
                <input type="number" name="year"
                       value="{{ old('year', $manga->year ?? '') }}"
                       class="form-input">
            </div>

            {{-- RATING --}}
            <div>
                <label class="form-label">Rating</label>
                <input type="number" step="0.1" name="rating"
                       value="{{ old('rating', $manga->rating ?? '') }}"
                       class="form-input">
            </div>

            {{-- SCORE --}}
            <div>
                <label class="form-label">Score</label>
                <input type="number" step="0.1" name="score"
                       value="{{ old('score', $manga->score ?? '') }}"
                       class="form-input">
            </div>

            {{-- AUTHOR --}}
            <div>
                <label class="form-label">Author</label>
                <input type="text" name="author"
                       value="{{ old('author', $manga->author ?? '') }}"
                       class="form-input">
            </div>

            {{-- ARTIST --}}
            <div>
                <label class="form-label">Artist</label>
                <input type="text" name="artist"
                       value="{{ old('artist', $manga->artist ?? '') }}"
                       class="form-input">
            </div>

            {{-- PUBLISHER --}}
            <div>
                <label class="form-label">Publisher</label>
                <input type="text" name="publisher"
                       value="{{ old('publisher', $manga->publisher ?? '') }}"
                       class="form-input">
            </div>

            {{-- SOURCE --}}
            <div>
                <label class="form-label">Source</label>
                <input type="text" name="source"
                       value="{{ old('source', $manga->source ?? '') }}"
                       class="form-input">
            </div>

        </div>

        {{-- ALT TITLES --}}
        <div class="mt-5">
            <label class="form-label">Alternative Titles</label>
            <input type="text" name="alternative_titles"
                   value="{{ old('alternative_titles', $manga->alternative_titles ?? '') }}"
                   class="form-input">
        </div>

        {{-- DESCRIPTION --}}
        <div class="mt-5">
            <label class="form-label">Description</label>
            <textarea name="description" rows="5" class="form-input">{{ old('description', $manga->description ?? '') }}</textarea>
        </div>

        {{-- FILES --}}
        <div class="grid md:grid-cols-2 gap-4 mt-6">

            <x-admin.dropzone name="thumbnail" label="Thumbnail" />
            <x-admin.dropzone name="banner" label="Banner" />

        </div>

        {{-- FEATURED --}}
        <div class="mt-5">
            <label class="flex items-center gap-2 text-sm text-gray-300">
                <input type="checkbox"
                       name="featured"
                       value="1"
                       @checked(old('featured', $manga->featured ?? false))>
                Featured
            </label>
        </div>

        {{-- GENRES --}}
        <div class="mt-6">
            <label class="form-label mb-2">Genres</label>

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
        <button type="submit" class="btn-success mt-6">
            {{ isset($manga) ? 'Update Manga' : 'Create Manga' }}
        </button>

    </form>

</div>

@endsection