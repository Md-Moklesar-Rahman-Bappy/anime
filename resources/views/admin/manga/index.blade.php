@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">

        <h1 class="text-2xl font-semibold text-white">
            Manga
        </h1>

        <a href="{{ route('admin.manga.create') }}"
           class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm transition">
            Add Manga
        </a>

    </div>

    <!-- Table -->
    <div class="bg-[#111827] border border-gray-800 rounded-2xl overflow-hidden">

        <div class="overflow-x-auto">
            <table class="w-full text-sm">

                <thead>
                    <tr class="bg-[#0f172a] text-gray-400 border-b border-gray-800">
                        <th class="p-3 text-left">Title</th>
                        <th class="p-3 text-left">Type</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-left">Chapters</th>
                        <th class="p-3 text-left">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($mangaList as $manga)
                    <tr class="border-b border-gray-800 hover:bg-[#1f2937] transition">

                        <!-- Title -->
                        <td class="p-3 text-white font-medium">
                            {{ $manga->title }}
                        </td>

                        <!-- Type -->
                        <td class="p-3 text-gray-400">
                            {{ $manga->type ?? '—' }}
                        </td>

                        <!-- Status -->
                        <td class="p-3">
                            <span class="px-2 py-1 rounded-lg text-xs
                                @switch($manga->status)
                                    @case('Completed') bg-green-500/10 text-green-400 @break
                                    @case('Ongoing') bg-indigo-500/10 text-indigo-400 @break
                                    @case('Hiatus') bg-yellow-500/10 text-yellow-400 @break
                                    @case('Cancelled') bg-red-500/10 text-red-400 @break
                                    @default bg-gray-700 text-gray-400
                                @endswitch
                            ">
                                {{ $manga->status ?? 'N/A' }}
                            </span>
                        </td>

                        <!-- Chapters -->
                        <td class="p-3 text-gray-400">
                            {{ $manga->chapters_count ?? 0 }}
                        </td>

                        <!-- Actions -->
                        <td class="p-3 flex gap-3 text-sm">

                            <a href="{{ route('admin.manga.chapters.index', $manga) }}"
                               class="text-indigo-400 hover:text-indigo-300 transition">
                                Chapters
                            </a>

                            <a href="{{ route('admin.manga.edit', $manga) }}"
                               class="text-blue-400 hover:text-blue-300 transition">
                                Edit
                            </a>

                            <form action="{{ route('admin.manga.destroy', $manga) }}"
                                  method="POST"
                                  onsubmit="return confirm('Delete this manga?')">
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
                            <p class="text-lg text-gray-300">No manga found</p>
                            <p class="text-sm mt-1">Add your first manga</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-800">
            {{ $mangaList->links() }}
        </div>

    </div>

</div>
@endsection