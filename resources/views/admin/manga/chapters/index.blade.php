@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Chapters: {{ $manga->title }}</h1>
            <a href="{{ route('admin.manga.index') }}" class="text-sm text-purple-500 hover:text-purple-400">← Back to Manga</a>
        </div>
        <a href="{{ route('admin.manga.chapters.create', $manga) }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm">Add Chapter</a>
    </div>
    <div class="bg-gray-900 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead><tr class="text-gray-400 border-b border-gray-800"><th class="text-left p-3">#</th><th class="text-left p-3">Title</th><th class="text-left p-3">Pages</th><th class="text-left p-3">Created</th><th class="text-left p-3">Actions</th></tr></thead>
            <tbody>
                @forelse($chapters as $chapter)
                <tr class="border-b border-gray-800 hover:bg-gray-800/50">
                    <td class="p-3 font-bold text-purple-500">Ch. {{ rtrim(rtrim($chapter->number, '0'), '.') }}</td>
                    <td class="p-3">{{ $chapter->title ?? 'Untitled' }}</td>
                    <td class="p-3">{{ $chapter->pages_count }}</td>
                    <td class="p-3 text-gray-500">{{ $chapter->created_at->format('Y-m-d') }}</td>
                    <td class="p-3 flex space-x-2">
                        <a href="{{ route('admin.manga.chapters.edit', [$manga, $chapter]) }}" class="text-blue-500 hover:text-blue-400">Edit</a>
                        <form action="{{ route('admin.manga.chapters.destroy', [$manga, $chapter]) }}" method="POST" onsubmit="return confirm('Delete this chapter and all its pages?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-400">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="p-6 text-center text-gray-500">No chapters yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $chapters->links() }}</div>
</div>
@endsection
