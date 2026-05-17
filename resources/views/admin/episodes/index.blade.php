@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Episodes: {{ $anime->title }}</h1>
            <a href="{{ route('admin.anime.index') }}" class="text-sm text-gray-500 hover:text-white">&larr; Back to Anime</a>
        </div>
        <a href="{{ route('admin.anime.episodes.create', $anime) }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm">Add Episode</a>
    </div>
    <div class="bg-gray-900 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead><tr class="text-gray-400 border-b border-gray-800"><th class="text-left p-3">#</th><th class="text-left p-3">Title</th><th class="text-left p-3">Duration</th><th class="text-left p-3">Sub</th><th class="text-left p-3">Dub</th><th class="text-left p-3">Actions</th></tr></thead>
            <tbody>
                @foreach($episodes as $ep)
                <tr class="border-b border-gray-800 hover:bg-gray-800/50">
                    <td class="p-3">{{ $ep->number }}</td>
                    <td class="p-3">{{ $ep->title ?? 'Episode '.$ep->number }}</td>
                    <td class="p-3">{{ $ep->duration ? $ep->duration.'s' : '-' }}</td>
                    <td class="p-3">{{ $ep->has_sub ? 'Yes' : 'No' }}</td>
                    <td class="p-3">{{ $ep->has_dub ? 'Yes' : 'No' }}</td>
                    <td class="p-3 flex space-x-2">
                        <a href="{{ route('admin.anime.episodes.edit', [$anime, $ep]) }}" class="text-blue-500 hover:text-blue-400">Edit</a>
                        <form action="{{ route('admin.anime.episodes.destroy', [$anime, $ep]) }}" method="POST" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-400">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $episodes->links() }}</div>
</div>
@endsection
