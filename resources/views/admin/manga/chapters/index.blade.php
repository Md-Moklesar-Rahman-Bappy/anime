@extends('admin.layouts.app')

@section('content')

<div class="max-w-6xl mx-auto">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-6">

        <div>
            <h1 class="text-xl font-semibold text-white">
                Chapters: {{ $manga->title }}
            </h1>

             }}" class="text-sm text-gray-500 hover:text-white">
                ← Back to Manga
            </a>
        </div>

         }}" 
           class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-sm">
            Add Chapter
        </a>

    </div>

    {{-- TABLE --}}
    <div class="bg-gray-900 border border-gray-700 rounded-xl overflow-hidden">

        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                {{-- THEAD --}}
                <thead class="bg-gray-800 text-gray-400 border-b border-gray-700">
                    <tr>
                        <th class="p-4 text-left">#</th>
                        <th class="p-4 text-left">Title</th>
                        <th class="p-4 text-left">Pages</th>
                        <th class="p-4 text-left">Created</th>
                        <th class="p-4 text-left">Actions</th>
                    </tr>
                </thead>

                {{-- TBODY --}}
                <tbody>

                @forelse($chapters as $chapter)

                <tr class="border-b border-gray-700">

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

                             }}" 
                               class="text-blue-400 hover:text-blue-300">
                                Edit
                            </a>

                             }}"
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