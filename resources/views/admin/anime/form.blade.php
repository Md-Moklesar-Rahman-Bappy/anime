@extends('admin.layouts.app')

@section('content')

<div class="max-w-4xl mx-auto">

    <h1 class="text-xl font-semibold text-white mb-6">
        {{ isset($anime) ? 'Edit Anime' : 'Create Anime' }}
    </h1>

    <form
        action="{{ isset($anime) ? route('admin.anime.update', $anime) : route('admin.anime.store') }}"
        method="POST"
        enctype="multipart/form-data"

        x-data="adminForm({
            key: 'anime_form_{{ $anime->id ?? 'new' }}',
            ajax: false
        })"
        x-init="init()"
        @input.debounce.500ms="saveDraft()"
        @change.debounce.500ms="saveDraft()"
        @submit="submit($event)"
    >

        @csrf
        @if(isset($anime)) @method('PUT') @endif


        {{-- GRID --}}
        <div class="grid md:grid-cols-2 gap-4">

            {{-- TITLE --}}
            <div>
                <label class="form-label">Title</label>
                <input type="text" name="title"
                       value="{{ old('title', $anime->title ?? '') }}"
                       required
                       class="form-input">
            </div>

            {{-- TYPE --}}
            <div>
                <label class="form-label">Type</label>
                <select name="type" class="form-input">
                    <option value="">Select</option>
                    @foreach(['TV','Movie','OVA','ONA','Special'] as $t)
                        <option value="{{ $t }}" @selected(old('type', $anime->type ?? '')==$t)>
                            {{ $t }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- STATUS --}}
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-input">
                    @foreach(['Ongoing','Completed','Upcoming'] as $s)
                        <option value="{{ $s }}" @selected(old('status', $anime->status ?? '')==$s)>
                            {{ $s }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- YEAR --}}
            <div>
                <label class="form-label">Year</label>
                <input type="number" name="year"
                       value="{{ old('year', $anime->year ?? '') }}"
                       class="form-input">
            </div>

            {{-- SEASON --}}
            <div>
                <label class="form-label">Season</label>
                <select name="season" class="form-input">
                    @foreach(['Winter','Spring','Summer','Fall'] as $season)
                        <option value="{{ $season }}" @selected(old('season', $anime->season ?? '')==$season)>
                            {{ $season }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- RATING --}}
            <div>
                <label class="form-label">Rating</label>
                <input type="number" step="0.1" name="rating"
                       value="{{ old('rating', $anime->rating ?? '') }}"
                       class="form-input">
            </div>

            {{-- DURATION --}}
            <div>
                <label class="form-label">Duration (min)</label>
                <input type="number" name="duration"
                       value="{{ old('duration', $anime->duration ?? '') }}"
                       class="form-input">
            </div>

            {{-- STUDIO --}}
            <div>
                <label class="form-label">Studio</label>
                <input type="text" name="studio"
                       value="{{ old('studio', $anime->studio ?? '') }}"
                       class="form-input">
            </div>

            {{-- COUNTRY --}}
            <div>
                <label class="form-label">Country</label>
                <input type="text" name="country"
                       value="{{ old('country', $anime->country ?? '') }}"
                       class="form-input">
            </div>

        </div>


        {{-- DESCRIPTION --}}
        <div class="mt-5">
            <label class="form-label">Description</label>
            <textarea name="description" rows="4" class="form-input">{{ old('description', $anime->description ?? '') }}</textarea>
        </div>


        {{-- FILE UPLOAD --}}
        <div class="grid md:grid-cols-2 gap-4 mt-6">

            {{-- THUMBNAIL --}}
            <x-admin.dropzone
                name="thumbnail"
                label="Thumbnail"
            />

            {{-- BANNER --}}
            <x-admin.dropzone
                name="banner"
                label="Banner"
            />

        </div>


        {{-- FEATURED --}}
        <div class="mt-5">
            <label class="flex items-center gap-2 text-sm text-gray-300">
                <input type="checkbox"
                       name="featured"
                       value="1"
                       @checked(old('featured', $anime->featured ?? false))>
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
                           @checked(isset($anime) && $anime->genres->contains($genre->id))>
                    {{ $genre->name }}
                </label>
                @endforeach

            </div>
        </div>


        {{-- ACTION --}}
        <button type="submit" class="btn-success mt-6">
            {{ isset($anime) ? 'Update Anime' : 'Create Anime' }}
        </button>

    </form>

</div>

@endsection