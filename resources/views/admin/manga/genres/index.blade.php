@extends('admin.layouts.app')

@section('content')

<div class="max-w-6xl mx-auto">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-6">

        <h1 class="text-xl font-semibold text-white">
            Manga Genres
        </h1>

    </div>


    {{-- CREATE FORM --}}
    <div class="form-card mb-6">

        <form action="{{ route('admin.manga.genres.store') }}"
              method="POST"
              class="flex gap-3 flex-wrap">
            @csrf

            <input type="text"
                   name="name"
                   placeholder="New genre..."
                   required
                   class="form-input flex-1 min-w-[200px]">

            <button type="submit"
                    class="btn-success text-sm">
                Add Genre
            </button>

        </form>

    </div>


    {{-- TABLE --}}
    <div class="table-card">

        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                {{-- HEADER --}}
                <thead class="table-head">
                    <tr>
                        <th class="p-4 text-left">Name</th>
                        <th class="p-4 text-left">Slug</th>
                        <th class="p-4 text-left">Actions</th>
                    </tr>
                </thead>

                {{-- BODY --}}
                <tbody>

                @forelse($genres as $genre)

                <tr class="table-row">

                    {{-- EDIT --}}
                    <td class="p-4">

                        <form action="{{ route('admin.manga.genres.update', $genre) }}"
                              method="POST"
                              class="flex gap-2 items-center">
                            @csrf
                            @method('PUT')

                            <input type="text"
                                   name="name"
                                   value="{{ $genre->name }}"
                                   required
                                   class="form-input text-sm">

                            <button type="submit"
                                    class="text-blue-400 hover:text-blue-300 text-sm transition">
                                Save
                            </button>

                        </form>

                    </td>

                    {{-- SLUG --}}
                    <td class="p-4 text-gray-400 text-sm">
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
                    <td colspan="3" class="p-10 text-center text-gray-500">

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