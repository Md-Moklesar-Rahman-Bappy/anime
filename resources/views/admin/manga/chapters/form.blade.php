@extends('admin.layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">

    <h1 class="text-2xl font-semibold text-white mb-6">
        {{ isset($chapter) ? 'Edit' : 'Create' }} Chapter - {{ $manga->title }}
    </h1>

    {{ isset($chapter) ? route('admin.manga.chapters.update', [$manga, $chapter]) : route('admin.manga.chapters.store', $manga) }}

        @csrf
        @if(isset($chapter)) @method('PUT') @endif

        <!-- Chapter Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div>
                <label class="text-gray-400 text-sm">Chapter Number</label>
                <input type="number" step="0.1" name="number"
                    value="{{ old('number', $chapter->number ?? '') }}"
                    class="form-input" required>
            </div>

            <div>
                <label class="text-gray-400 text-sm">Title (optional)</label>
                <input type="text" name="title"
                    value="{{ old('title', $chapter->title ?? '') }}"
                    class="form-input">
            </div>

        </div>

        <!-- Upload -->
        <div>
            <label class="text-gray-400 text-sm">Upload Page Images</label>
            <input type="file" name="pages[]" multiple accept="image/*"
                class="file-input">

            <p class="text-xs text-gray-500 mt-1">
                Images sorted alphabetically (jpg, png, webp)
            </p>
        </div>

        <!-- URL Import -->
        <div>
            <label class="text-gray-400 text-sm">Or Image URLs</label>

            <textarea name="page_urls" rows="4"
                class="form-input"
                placeholder="https://example.com/page-01.jpg">
{{ old('page_urls', isset($chapter) ? $chapter->pages->pluck('image_path')->implode("\n") : '') }}
            </textarea>
        </div>

        <!-- Existing Pages -->
        @if(isset($chapter) && $chapter->pages->count())
        <div>

            <label class="text-gray-400 text-sm mb-2 block">
                Existing Pages
            </label>

            <div class="grid grid-cols-2 md:grid-cols-6 gap-2">

                @foreach($chapter->pages as $page)
                <div class="relative group">

                     }}" 
                         class="w-full aspect-[3/4] object-cover rounded-lg">

                    <div class="absolute inset-0 bg-black/60 flex items-center justify-center opacity-0 group-hover:opacity-100 transition rounded-lg">
                        <label class="flex items-center gap-1 text-xs text-white cursor-pointer">
                            <input type="checkbox" name="delete_pages[]" value="{{ $page->id }}">
                            Delete
                        </label>
                    </div>

                    <span class="text-xs text-gray-500 text-center block mt-1">
                        P. {{ $page->page_number }}
                    </span>
                </div>
                @endforeach

            </div>

        </div>
        @endif

        <!-- Actions -->
        <div class="flex gap-3">

            <button type="submit"
                class="bg-emerald-600 hover:bg-emerald-500 text-white px-6 py-2 rounded-lg transition">
                {{ isset($chapter) ? 'Update' : 'Create' }}
            </button>

             }}"
               class="bg-gray-800 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition">
                Cancel
            </a>

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