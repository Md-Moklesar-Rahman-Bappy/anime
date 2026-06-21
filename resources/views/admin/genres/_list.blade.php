<div class="table-card">

    <div class="overflow-x-auto">

        <table class="w-full text-sm">

            <thead class="table-head">
                <tr>
                    <th class="p-4 text-left">Name</th>
                    <th class="p-4 text-left">Slug</th>
                    <th class="p-4 text-left">Actions</th>
                </tr>
            </thead>

            <tbody>

            @forelse($genres as $genre)

                <tr class="table-row">

                    {{-- NAME --}}
                    <td class="p-4">

                        <input
                            type="text"
                            value="{{ $genre->name }}"
                            @change="updateGenre({{ $genre->id }}, $event.target.value)"
                            class="form-input text-sm"
                        >

                    </td>

                    {{-- SLUG --}}
                    <td class="p-4 text-gray-400 text-sm">
                        {{ $genre->slug }}
                    </td>

                    {{-- ACTIONS --}}
                    <td class="p-4">

                        <button
                            type="button"
                            @click="deleteGenre({{ $genre->id }})"
                            class="text-red-400 hover:text-red-300 text-sm"
                        >
                            Delete
                        </button>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="3" class="p-10 text-center text-gray-500">

                        <p class="text-white font-medium mb-1">
                            No genres found
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

    <div class="p-4 border-t border-gray-700">
        {{ $genres->links() }}
    </div>

</div>
