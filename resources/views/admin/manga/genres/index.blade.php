@extends('admin.layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-white">
            Manga Genres
        </h1>
    </div>

    <!-- Add Genre -->
    <div class="bg-[#111827] border border-gray-800 rounded-2xl p-4 mb-6">

         }}" method="POST" class="flex gap-2">
            @csrf

            <input type="text" name="name"
                placeholder="New genre..."
                class="form-input flex-1"
                required>

            <button type="submit"
                class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm transition">
                Add
            </button>
        </form>
    </div>

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

                        <!-- Editable Name -->
                        <td class="p-3">
                             }}" method="POST" class="flex gap-2">
                                @csrf
                                @method('PUT')

                                <input type="text" name="name"
                                    value="{{ $genre->name }}"
                                    class="form-input text-sm"
                                    required>

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
                             }}" method="POST"
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
                            <p class="text-lg text-gray-300">No manga genres yet</p>
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
