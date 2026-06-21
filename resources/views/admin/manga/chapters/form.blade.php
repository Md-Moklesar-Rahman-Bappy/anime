@extends('admin.layouts.app')

@section('content')

<div class="max-w-4xl mx-auto">

    {{-- TITLE --}}
    <h1 class="text-xl font-semibold text-white mb-6">
        {{ isset($chapter) ? 'Edit Chapter' : 'Create Chapter' }} – {{ $manga->title }}
    </h1>

    <form
        action="{{ isset($chapter)
            ? route('admin.manga.chapters.update', [$manga, $chapter])
            : route('admin.manga.chapters.store', $manga) }}"
        method="POST"
        enctype="multipart/form-data"
        x-data="adminForm({
            key: 'chapter_form_{{ $chapter->id ?? 'new' }}'
        })"
        x-init="init()"
        @input.debounce.500ms="saveDraft()"
        @change.debounce.500ms="saveDraft()"
        @submit="submit($event)"
    >

        @csrf
        @if(isset($chapter)) @method('PUT') @endif

        {{-- GRID --}}
        <div class="grid md:grid-cols-2 gap-4">

            {{-- NUMBER --}}
            <div>
                <label class="form-label">Chapter Number</label>
                <input type="number"
                       step="0.1"
                       name="number"
                       value="{{ old('number', $chapter->number ?? '') }}"
                       required
                       class="form-input">
            </div>

            {{-- TITLE --}}
            <div>
                <label class="form-label">Title (optional)</label>
                <input type="text"
                       name="title"
                       value="{{ old('title', $chapter->title ?? '') }}"
                       class="form-input">
            </div>

        </div>


        {{-- FILE UPLOAD (DRAG DROP) --}}
        <div class="mt-6">
            <x-admin.dropzone
                name="pages[]"
                label="Upload Chapter Pages"
                accept="image/*"
                :multiple="true"
            />

            <p class="text-xs text-gray-500 mt-2">
                Drag & drop multiple images. Order can be adjusted before upload.
            </p>
        </div>


        {{-- URL INPUT --}}
        <div class="mt-6">
            <label class="form-label">Or Image URLs</label>

            <textarea name="page_urls"
                      rows="4"
                      class="form-input"
                      placeholder="https://example.com/page-01.jpg">{{ old('page_urls', isset($chapter) ? $chapter->pages->pluck('image_path')->implode("\n") : '') }}</textarea>
        </div>


        {{-- EXISTING PAGES --}}
        @if(isset($chapter) && $chapter->pages->count())
        <div class="mt-6">

            <label class="form-label block mb-2">
                Existing Pages
            </label>

            <div class="grid grid-cols-2 md:grid-cols-6 gap-3">

                @foreach($chapter->pages as $page)
                <div class="relative group">

                    {{-- IMAGE --}}
                    <img src="{{ $page->image_url ?? $page->image_path }}"
                         class="aspect-[3/4] object-cover rounded-lg w-full">

                    {{-- OVERLAY --}}
                    <div class="absolute inset-0 bg-black/60 flex items-center justify-center opacity-0 group-hover:opacity-100 transition rounded-lg">

                        <label class="flex items-center gap-1 text-xs text-white cursor-pointer">
                            <input type="checkbox"
                                   name="delete_pages[]"
                                   value="{{ $page->id }}">
                            Delete
                        </label>

                    </div>

                    {{-- PAGE NUMBER --}}
                    <p class="text-center text-xs text-gray-500 mt-1">
                        P. {{ $page->page_number }}
                    </p>

                </div>
                @endforeach

            </div>

        </div>
        @endif


        {{-- ACTIONS --}}
        <div class="flex gap-3 mt-6">

            <button type="submit"
                    class="btn-success">
                {{ isset($chapter) ? 'Update Chapter' : 'Create Chapter' }}
            </button>

            <a href="{{ route('admin.manga.chapters.index', $manga) }}"
               class="btn-cancel">
                Cancel
            </a>

        </div>

    </form>

</div>

@endsection