<div class="table-card">

    <div class="overflow-x-auto">

        <table class="w-full text-sm">

            <thead class="table-head">
                <tr>
                    <th class="p-4 text-left">Title</th>
                    <th class="p-4 text-left">Type</th>
                    <th class="p-4 text-left">Status</th>
                    <th class="p-4 text-left">Chapters</th>
                    <th class="p-4 text-left">Actions</th>
                </tr>
            </thead>

            <tbody>

            @forelse($mangaList as $manga)

            <tr class="table-row">

                <td class="p-4 text-white">
                    {{ $manga->title }}
                </td>

                <td class="p-4 text-gray-400">
                    {{ $manga->type }}
                </td>

                <td class="p-4">
                    <span class="badge-indigo">
                        {{ $manga->status }}
                    </span>
                </td>

                <td class="p-4 text-gray-400">
                    {{ $manga->chapters_count }}
                </td>

                <td class="p-4">

                    <div class="flex gap-3 text-sm">

                        <a href="{{ route('admin.manga.chapters.index', $manga) }}"
                           class="text-indigo-400">
                            Chapters
                        </a>

                        <a href="{{ route('admin.manga.edit', $manga) }}"
                           class="text-blue-400">
                            Edit
                        </a>

                        <form action="{{ route('admin.manga.destroy', $manga) }}"
                              method="POST"
                              onsubmit="return confirm('Delete?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-400">
                                Delete
                            </button>
                        </form>

                    </div>

                </td>

            </tr>

            @empty
            <tr>
                <td colspan="5" class="p-10 text-center text-gray-500">
                    No manga found
                </td>
            </tr>
            @endforelse

            </tbody>

        </table>

    </div>

    <div class="p-4 border-t border-gray-700">
        @include('admin.anime._pagination')
    </div>

</div>