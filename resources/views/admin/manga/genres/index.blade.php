@extends('admin.layouts.app')

@section('content')

<div class="max-w-4xl mx-auto">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-white">
            Manga Genres
        </h1>
    </div>

    {{-- CREATE FORM --}}
    <div class="bg-gray-900 border border-gray-700 rounded-xl p-4 mb-6">

        <form action="{{ route('admin.manga.genres.store') }}" method="POST"
              class="flex gap-3">
            @csrf

            <input type="text"
                   name="name"
                   placeholder="New genre..."
                   required
                   class="flex-1 bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white focus:ring-2 focus:ring-indigo-500">

            <button type="submit"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg">
                Add
            </button>
        </form>

    </div>

    {{-- TABLE --}}
    <div class="bg-gray-900 border border-gray-700 rounded-xl overflow-hidden">

        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                {{-- THEAD --}}
                <thead class="bg-gray-800 text-gray-400 border-b border-gray-700">
                    <tr>
                        <th class="p-4 text-left">Name</th>
                        <th class="p-4 text-left">Slug</th>
                        <th class="p-4 text-left">Actions</th>
                    </tr>
                </thead>

                {{-- TBODY --}}
                <tbody>

                @forelse($genres as $genre)

                <tr class="border-b border-gray-700">

                    {{-- EDIT --}}
                    <td class="p-4">

                        <form action="{{ route('admin.manga.genres.update', $genre) }}"
                              method="POST"
                              class="flex gap-2">
                            @csrf
                            @method('PUT')

                            <input type="text"
                                   name="name"
                                   value="{{ $genre->name }}"
                                   required
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

                        <form action="{{ route('admin.manga.genres.destroy', $genre) }}"
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

                        <p class="text-white font-medium mb-1">
                            No manga genres yet
                        </p>

                        <p class="text-sm">
                            Add your first genre
                        </p>

                    </td>
                </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        {{-- PAGINATION --}}
        <div class="p-4 border-t border-gray-700">
            {{ $genres->links() }}
        </div>

    </div>

</div>

@endsection