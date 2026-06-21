@extends('admin.layouts.app')

@section('content')

<div class="max-w-6xl mx-auto">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-6 flex-wrap gap-4">

        <div>
            <h1 class="text-xl font-semibold text-white">
                Chapters: {{ $manga->title }}
            </h1>

            <a href="{{ route('admin.manga.index') }}"
               class="text-sm text-gray-500 hover:text-white">
                ← Back to Manga
            </a>
        </div>

        <a href="{{ route('admin.manga.chapters.create', $manga) }}"
           class="btn-success text-sm">
            Add Chapter
        </a>

    </div>


    {{-- TABLE --}}
    <div class="table-card">

        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                {{-- HEADER --}}
                <thead class="table-head">
                    <tr>
                        <th class="p-4 text-left">#</th>
                        <th class="p-4 text-left">Title</th>
                        <th class="p-4 text-left">Pages</th>
                        <th class="p-4 text-left">Created</th>
                        <th class="p-4 text-left">Actions</th>
                    </tr>
                </thead>

                {{-- BODY --}}
                <tbody>

                @forelse($chapters as $chapter)

                <tr class="table-row">

                    {{-- NUMBER --}}
                    <td class="p-4 text-indigo-400 font-semibold">
                        Ch. {{ rtrim(rtrim($chapter->number, '0'), '.') }}
                    </td>

                    {{-- TITLE --}}
                    <td class="p-4 text-gray-300">
                        {{ $chapter->title ?? 'Untitled Chapter' }}
                    </td>

                    {{-- PAGES --}}
                    <td class="p-4 text-gray-400">
                        {{ $chapter->pages_count }}
                    </td>

                    {{-- DATE --}}
                    <td class="p-4 text-gray-500 text-xs">
                        {{ $chapter->created_at->format('Y-m-d') }}
                    </td>

                    {{-- ACTIONS --}}
                    <td class="p-4">

                        <div class="flex items-center gap-4 text-sm">

                            <a href="{{ route('admin.manga.chapters.edit', [$manga, $chapter]) }}"
                               class="text-blue-400 hover:text-blue-300">
                                Edit
                            </a>

                            <form action="{{ route('admin.manga.chapters.destroy', [$manga, $chapter]) }}"
                                  method="POST"
                                  onsubmit="return confirm('Delete this chapter and all its pages?')">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="text-red-400 hover:text-red-300">
                                    Delete
                                </button>
                            </form>

                        </div>

                    </td>

                </tr>

                @empty

                <tr>
                    <td colspan="5" class="p-10 text-center text-gray-500">

                        <p class="text-white font-medium mb-1">
                            No chapters yet
                        </p>

                        <p class="text-sm">
                            Create your first chapter
                        </p>

                    </td>
                </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        {{-- PAGINATION --}}
        <div class="p-4 border-t border-gray-700">
            {{ $chapters->links() }}
        </div>

    </div>

</div>

@endsection