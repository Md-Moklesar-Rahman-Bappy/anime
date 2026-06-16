@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Comments</h1>
    <div class="bg-gray-900 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-gray-400 border-b border-gray-800 text-left">
                    <th class="p-3">User</th>
                    <th class="p-3">Type</th>
                    <th class="p-3">On</th>
                    <th class="p-3">Comment</th>
                    <th class="p-3">Date</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($comments as $comment)
                <tr class="border-b border-gray-800 hover:bg-gray-800/50">
                    <td class="p-3">{{ $comment->user_name }}</td>
                    <td class="p-3">
                        @if($comment->type === 'anime')
                        <span class="text-purple-500">Anime</span>
                        @else
                        <span class="text-emerald-500">Manga</span>
                        @endif
                    </td>
                    <td class="p-3">
                        <a href="{{ $comment->source_url }}" class="text-purple-500 hover:text-purple-400" target="_blank">
                            {{ $comment->source }}
                        </a>
                        <span class="text-gray-500">({{ $comment->episode }})</span>
                    </td>
                    <td class="p-3 max-w-xs truncate">{{ $comment->body }}</td>
                    <td class="p-3 text-gray-400 text-xs">{{ $comment->created_at->diffForHumans() }}</td>
                    <td class="p-3">
                        @if($comment->type === 'anime')
                        <form action="{{ route('admin.comments.destroy.anime', $comment->id) }}" method="POST" onsubmit="return confirm('Delete this comment?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-400 text-xs">Delete</button>
                        </form>
                        @else
                        <form action="{{ route('admin.comments.destroy.manga', $comment->id) }}" method="POST" onsubmit="return confirm('Delete this comment?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-400 text-xs">Delete</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
                @if($comments->count() === 0)
                <tr>
                    <td colspan="6" class="p-6 text-center text-gray-500">No comments yet.</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $comments->links() }}</div>
</div>
@endsection
