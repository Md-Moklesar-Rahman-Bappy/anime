@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Genres</h1>
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
