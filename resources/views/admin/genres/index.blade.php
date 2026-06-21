@extends('admin.layouts.app')

@section('content')

<div class="max-w-6xl mx-auto">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-white">Genres</h1>

        {{-- IMPORT BUTTON --}}
        <form action="{{ route('admin.genres.import-from-mal') }}"
              method="POST"
              onsubmit="return confirm('Import all genres from MyAnimeList?')">
            @csrf

            <button type="submit"
                class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-sm transition">
                
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>

                Import from MAL
            </button>
        </form>
    </div>

    {{-- CREATE FORM --}}
    <form action="{{ route('admin.genres.store') }}" method="POST"
          class="flex gap-3 mb-6">
        @csrf

        <input type="text"
               name="name"
               placeholder="Genre name"
               required
               class="flex-1 bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white focus:ring-2 focus:ring-indigo-500">

        <button type="submit"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 rounded-lg text-white text-sm">
            Add
        </button>
    </form>

    {{-- TABLE --}}
    <div class="bg-gray-900 border border-gray-700 rounded-xl overflow-hidden">

        <table class="w-full text-sm">

            {{-- HEADER --}}
            <thead class="bg-gray-800 text-gray-400 border-b border-gray-700">
                <tr>
                    <th class="p-4 text-left">Name</th>
                    <th class="p-4 text-left">Slug</th>
                    <th class="p-4 text-left">Actions</th>
                </tr>
            </thead>

            {{-- BODY --}}
            <tbody>

            @forelse($genres as $genre)
                <tr class="border-b border-gray-700">

                    {{-- NAME EDIT --}}
                    <td class="p-4">
                        <form action="{{ route('admin.genres.update', $genre) }}"
                              method="POST"
                              class="flex gap-2">
                            @csrf
                            @method('PUT')

                            <input type="text"
                                   name="name"
                                   value="{{ $genre->name }}"
                                   class="bg-gray-800 border border-gray-600 rounded px-2 py-1 text-white w-full text-sm">

                            <button type="submit"
                                    class="text-blue-400 hover:text-blue-300 text-sm">
                                Save
                            </button>
                        </form>
                    </td>

                    {{-- SLUG --}}
                    <td class="p-4 text-gray-400">
                        {{ $genre->slug }}
                    </td>

                    {{-- DELETE --}}
                    <td class="p-4">
                        <form action="{{ route('admin.genres.destroy', $genre) }}"
                              method="POST"
                              onsubmit="return confirm('Delete this genre?')">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="text-red-400 hover:text-red-300 text-sm">
                                Delete
                            </button>
                        </form>
                    </td>

                </tr>

            @empty
                <tr>
                    <td colspan="3" class="p-8 text-center text-gray-500">
                        <p class="text-white font-medium mb-1">No genres found</p>
                        <p class="text-sm">Add your first genre</p>
                    </td>
                </tr>
            @endforelse

            </tbody>

        </table>

        {{-- PAGINATION --}}
        <div class="p-4 border-t border-gray-700">
            {{ $genres->links() }}
        </div>

    </div>

</div>

@endsection