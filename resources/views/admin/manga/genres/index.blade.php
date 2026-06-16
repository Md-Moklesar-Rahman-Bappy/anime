@extends('admin.layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Manga Genres</h1>
    </div>

    <div class="bg-gray-900 rounded-lg p-4 mb-6">
        <form action="{{ route('admin.manga.genres.store') }}" method="POST" class="flex space-x-2">
            @csrf
            <input type="text" name="name" placeholder="New genre name..." class="flex-1 bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500" required>
            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm">Add</button>
        </form>
    </div>

    <div class="bg-gray-900 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead><tr class="text-gray-400 border-b border-gray-800"><th class="text-left p-3">Name</th><th class="text-left p-3">Slug</th><th class="text-left p-3">Actions</th></tr></thead>
            <tbody>
                @foreach($genres as $genre)
                <tr class="border-b border-gray-800 hover:bg-gray-800/50">
                    <td class="p-3">
                        <form action="{{ route('admin.manga.genres.update', $genre) }}" method="POST" class="flex space-x-2">
                            @csrf @method('PUT')
                            <input type="text" name="name" value="{{ $genre->name }}" class="bg-transparent text-white border border-gray-700 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-purple-500" required>
                            <button type="submit" class="text-blue-500 hover:text-blue-400 text-xs">Save</button>
                        </form>
                    </td>
                    <td class="p-3 text-gray-500">{{ $genre->slug }}</td>
                    <td class="p-3">
                        <form action="{{ route('admin.manga.genres.destroy', $genre) }}" method="POST" onsubmit="return confirm('Delete this genre?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-400">Delete</button>
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
