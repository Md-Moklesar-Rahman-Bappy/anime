@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Genres</h1>
        <form action="{{ route('admin.genres.import-from-mal') }}" method="POST" class="inline" onsubmit="return confirm('Import all genres from MyAnimeList?')">
            @csrf
            <button type="submit" class="bg-green-700 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Import All Genres from MAL
            </button>
        </form>
    </div>
    <form action="{{ route('admin.genres.store') }}" method="POST" class="flex space-x-2 mb-6">
        @csrf
        <input type="text" name="name" placeholder="Genre name" class="bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700 flex-1" required>
        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg">Add</button>
    </form>
    <div class="bg-gray-900 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead><tr class="text-gray-400 border-b border-gray-800"><th class="text-left p-3">Name</th><th class="text-left p-3">Slug</th><th class="text-left p-3">Actions</th></tr></thead>
            <tbody>
                @foreach($genres as $genre)
                <tr class="border-b border-gray-800">
                    <td class="p-3">
                        <form action="{{ route('admin.genres.update', $genre) }}" method="POST" class="flex space-x-2">
                            @csrf @method('PUT')
                            <input type="text" name="name" value="{{ $genre->name }}" class="bg-gray-800 text-white rounded px-2 py-1 border border-gray-700 text-sm">
                            <button type="submit" class="text-blue-500 hover:text-blue-400 text-sm">Save</button>
                        </form>
                    </td>
                    <td class="p-3 text-gray-400">{{ $genre->slug }}</td>
                    <td class="p-3">
                        <form action="{{ route('admin.genres.destroy', $genre) }}" method="POST" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-400 text-sm">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $genres->links() }}</div>
</div>
@endsection
