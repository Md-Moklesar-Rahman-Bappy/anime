@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">

        <div>
            <h1 class="text-2xl font-semibold text-white">
                Chapters: {{ $manga->title }}
            </h1>

            <a href="{{ route('admin.manga.index') }}"
               class="text-sm text-gray-500 hover:text-white">
                ← Back to Manga
            </a>
        </div>

        <a href="{{ route('admin.manga.chapters.create', $manga) }}"
           class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm transition">
            Add Chapter
        </a>

    </div>

    <!-- Table -->
    <div class="bg-[#111827] border border-gray-800 rounded-2xl overflow-hidden">

        <div class="overflow-x-auto">
            <table class="w-full text-sm">

                <thead>
                    <tr class="bg-[#0f172a] text-gray-400 border-b border-gray-800">
                        <th class="p-3 text-left">#</th>
                        <th class="p-3 text-left">Title</th>
                        <th class="p-3 text-left">Pages</th>
                        <th class="p-3 text-left">Created</th>
                        <th class="p-3 text-left">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($chapters as $chapter)
                    <tr class="border-b border-gray-800 hover:bg-[#1f2937] transition">

                        <!-- Number -->
                        <td class="p-3 text-indigo-400 font-semibold">
                            Ch. {{ rtrim(rtrim($chapter->number, '0'), '.') }}
                        </td>

                        <!-- Title -->
                        <td class="p-3 text-gray-300">
                            {{ $chapter->title ?? 'Untitled Chapter' }}
                        </td>

                        <!-- Pages -->
                        <td class="p-3 text-gray-400">
                            {{ $chapter->pages_count }}
                        </td>

                        <!-- Date -->
                        <td class="p-3 text-gray-500 text-xs">
                            {{ $chapter->created_at->format('Y-m-d') }}
                        </td>

                        <!-- Actions -->
                        <td class="p-3 flex gap-3 text-sm">

                            <a href="{{ route('admin.manga.chapters.edit', [$manga, $chapter]) }}"
                               class="text-blue-400 hover:text-blue-300 transition">
                                Edit
                            </a>

                            <form action="{{ route('admin.manga.chapters.destroy', [$manga, $chapter]) }}"
                                  method="POST"
                                  onsubmit="return confirm('Delete this chapter and all its pages?')">

                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                    class="text-red-400 hover:text-red-300 transition">
                                    Delete
                                </button>
                            </form>

                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-10 text-center text-gray-500">
                            <p class="text-lg text-gray-300">No chapters yet</p>
                            <p class="text-sm mt-1">Create your first chapter</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-800">
            {{ $chapters->links() }}
        </div>

    </div>

</div>
@endsection