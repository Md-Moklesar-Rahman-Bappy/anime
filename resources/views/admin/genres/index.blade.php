@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-white">Genres</h1>

        <form action="{{ route('admin.genres.import-from-mal') }}"
              method="POST"
              onsubmit="return confirm('Import all genres from MyAnimeList?')">
            @csrf
            <button type="submit"
                class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-1 transition">
                
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>

                Import from MAL
            </button>
        </form>
    </div>

    <!-- Add Genre -->
    <form action="{{ route('admin.genres.store') }}" method="POST" class="flex gap-2 mb-6">
        @csrf

        <input type="text" name="name"
               placeholder="Genre name"
               class="form-input flex-1" required>

        <button type="submit"
            class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg transition">
            Add
        </button>
    </form>

    <!-- Table -->
    <div class="bg-[#111827] border border-gray-800 rounded-2xl overflow-hidden">

        <div class="overflow-x-auto">
            <table class="w-full text-sm">

                <thead>
                <tr class="bg-[#0f172a] text-gray-400 border-b border-gray-800 text-left">
                    <th class="p-3">Name</th>
                    <th class="p-3">Slug</th>
                    <th class="p-3">Actions</th>
                </tr>
                </thead>

                <tbody>
                @forelse($genres as $genre)
                <tr class="border-b border-gray-800 hover:bg-[#1f2937] transition">

                    <!-- Name (Editable) -->
                    <td class="p-3">
                        <form action="{{ route('admin.genres.update', $genre) }}"
                              method="POST"
                              class="flex gap-2">
                            @csrf
                            @method('PUT')

                            <input type="text" name="name"
                                   value="{{ $genre->name }}"
                                   class="form-input text-sm">

                            <button type="submit"
                                class="text-blue-400 hover:text-blue-300 text-sm transition">
                                Save
                            </button>
                        </form>
                    </td>

                    <!-- Slug -->
                    <td class="p-3 text-gray-400">
                        {{ $genre->slug }}
                    </td>

                    <!-- Delete -->
                    <td class="p-3">
                        <form action="{{ route('admin.genres.destroy', $genre) }}"
                              method="POST"
                              onsubmit="return confirm('Delete this genre?')">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                class="text-red-400 hover:text-red-300 text-sm transition">
                                Delete
                            </button>
                        </form>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="3" class="p-10 text-center text-gray-500">
                        <p class="text-lg text-gray-300">No genres found</p>
                        <p class="text-sm mt-1">Add your first genre</p>
                    </td>
                </tr>
                @endforelse
                </tbody>

            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-800">
            {{ $genres->links() }}
        </div>

    </div>
</div>

<style>
.form-input {
    @apply w-full px-3 py-2 bg-[#1f2937] border border-gray-700 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500;
}
</style>

@endsection
