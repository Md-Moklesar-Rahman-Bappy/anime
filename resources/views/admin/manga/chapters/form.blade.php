@extends('admin.layouts.app')

@section('content')

<div class="max-w-4xl mx-auto">

    {{-- TITLE --}}
    <h1 class="text-xl font-semibold text-white mb-6">
        {{ isset($chapter) ? 'Edit' : 'Create' }} Chapter - {{ $manga->title }}
    </h1>

    <form action="{{ isset($chapter)
            ? route('admin.manga.chapters.update', [$manga, $chapter])
            : route('admin.manga.chapters.store', $manga) }}"
          method="POST"
          enctype="multipart/form-data">

        @csrf
        @if(isset($chapter)) @method('PUT') @endif

        {{-- ROW --}}
        <div class="grid md:grid-cols-2 gap-4">

            {{-- NUMBER --}}
            <div>
                <label class="text-sm text-gray-400 mb-1 block">Chapter Number</label>
                <input type="number"
                       step="0.1"
                       name="number"
                       value="{{ old('number', $chapter->number ?? '') }}"
                       required
                       class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white focus:ring-2 focus:ring-indigo-500">
            </div>

            {{-- TITLE --}}
            <div>
                <label class="text-sm text-gray-400 mb-1 block">Title (optional)</label>
                <input type="text"
                       name="title"
                       value="{{ old('title', $chapter->title ?? '') }}"
                       class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white">
            </div>

        </div>

        {{-- FILE UPLOAD --}}
        <div class="mt-5">
            <label class="text-sm text-gray-400 mb-1 block">Upload Page Images</label>
            <input type="file"
                   name="pages[]"
                   multiple
                   accept="image/*"
                   class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-gray-300">
            <p class="text-xs text-gray-500 mt-1">
                Images sorted alphabetically (jpg, png, webp)
            </p>
        </div>

        {{-- URL INPUT --}}
        <div class="mt-5">
            <label class="text-sm text-gray-400 mb-1 block">Or Image URLs</label>
            <textarea name="page_urls"
                      rows="4"
                      class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white"
                      placeholder="https://example.com/page-01.jpg">{{ old('page_urls', isset($chapter) ? $chapter->pages->pluck('image_path')->implode("\n") : '') }}</textarea>
        </div>

        {{-- EXISTING PAGES --}}
        @if(isset($chapter) && $chapter->pages->count())
        <div class="mt-6">

            <label class="text-sm text-gray-400 block mb-2">
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
                    class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg">
                {{ isset($chapter) ? 'Update' : 'Create' }}
            </button>

            route('admin.manga.chapters.index', $manga) }}"
               class="px-5 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg">
                Cancel
            </a>

        </div>

    </form>

</div>

@endsection