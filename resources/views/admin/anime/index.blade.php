@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Anime</h1>
        <a href="{{ route('admin.anime.create') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm">Add New</a>
    </div>
    <div class="bg-gray-900 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead><tr class="text-gray-400 border-b border-gray-800"><th class="text-left p-3">Title</th><th class="text-left p-3">Type</th><th class="text-left p-3">Status</th><th class="text-left p-3">Episodes</th><th class="text-left p-3">Actions</th></tr></thead>
            <tbody>
                @foreach($animeList as $anime)
                <tr class="border-b border-gray-800 hover:bg-gray-800/50">
                    <td class="p-3">{{ $anime->title }}</td>
                    <td class="p-3">{{ $anime->type }}</td>
                    <td class="p-3">{{ $anime->status }}</td>
                    <td class="p-3">{{ $anime->episodes_count }}</td>
                    <td class="p-3 flex space-x-2">
                        <a href="{{ route('admin.anime.episodes.index', $anime) }}" class="text-purple-500 hover:text-purple-400">Episodes</a>
                        <a href="{{ route('admin.anime.edit', $anime) }}" class="text-blue-500 hover:text-blue-400">Edit</a>
                        <form action="{{ route('admin.anime.destroy', $anime) }}" method="POST" onsubmit="return confirm('Delete this anime?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-400">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $animeList->links() }}</div>
</div>
@endsection
