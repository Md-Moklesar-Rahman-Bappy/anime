@extends('admin.layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">{{ isset($chapter) ? 'Edit' : 'Create' }} Chapter - {{ $manga->title }}</h1>
    <form action="{{ isset($chapter) ? route('admin.manga.chapters.update', [$manga, $chapter]) : route('admin.manga.chapters.store', $manga) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf @if(isset($chapter)) @method('PUT') @endif
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm text-gray-400 mb-1">Chapter Number</label><input type="number" step="0.1" name="number" value="{{ old('number', $chapter->number ?? '') }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700" required></div>
            <div><label class="block text-sm text-gray-400 mb-1">Title (optional)</label><input type="text" name="title" value="{{ old('title', $chapter->title ?? '') }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700"></div>
        </div>

        <div>
            <label class="block text-sm text-gray-400 mb-1">Upload Page Images</label>
            <input type="file" name="pages[]" multiple accept="image/*" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-gray-800 file:text-white hover:file:bg-gray-700">
            <p class="text-xs text-gray-600 mt-1">Images are sorted alphabetically by filename. Supported: jpg, png, webp (max 5MB each).</p>
        </div>

        <div>
            <label class="block text-sm text-gray-400 mb-1">Or Add Image URLs (one per line)</label>
            <textarea name="page_urls" rows="4" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700" placeholder="https://example.com/page-01.jpg&#10;https://example.com/page-02.jpg">{{ old('page_urls', isset($chapter) ? $chapter->pages->where('image_path', 'like', 'http%')->pluck('image_path')->implode("\n") : '') }}</textarea>
        </div>

        @if(isset($chapter) && $chapter->pages->where('image_path', 'not like', 'http%')->count())
        <div>
            <label class="block text-sm text-gray-400 mb-1">Existing Pages</label>
            <div class="grid grid-cols-6 gap-2">
                @foreach($chapter->pages()->where('image_path', 'not like', 'http%')->orderBy('page_number')->get() as $page)
                <div class="relative group">
                    <img src="{{ asset('storage/'.$page->image_path) }}" class="w-full aspect-[3/4] object-cover rounded" alt="Page {{ $page->page_number }}">
                    <div class="absolute inset-0 bg-black/60 flex items-center justify-center opacity-0 group-hover:opacity-100 transition rounded">
                        <label class="flex items-center space-x-1 text-xs text-white cursor-pointer">
                            <input type="checkbox" name="delete_pages[]" value="{{ $page->id }}" class="rounded bg-gray-800 border-gray-700 text-red-500">
                            <span>Delete</span>
                        </label>
                    </div>
                    <span class="text-xs text-gray-500 text-center block mt-1">P. {{ $page->page_number }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="flex space-x-3">
            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg">{{ isset($chapter) ? 'Update' : 'Create' }}</button>
            <a href="{{ route('admin.manga.chapters.index', $manga) }}" class="bg-gray-800 hover:bg-gray-700 text-white px-6 py-2 rounded-lg">Cancel</a>
        </div>
    </form>
</div>
@endsection
