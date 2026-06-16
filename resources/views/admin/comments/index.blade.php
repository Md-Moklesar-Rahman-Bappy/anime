@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">

    <h1 class="text-2xl font-semibold text-white mb-6">
        Comments
    </h1>

    <div class="bg-[#111827] border border-gray-800 rounded-2xl shadow overflow-hidden">

        <div class="overflow-x-auto">
            <table class="w-full text-sm">

                <thead>
                    <tr class="bg-[#0f172a] text-gray-400 border-b border-gray-800 text-left">
                        <th class="p-3">User</th>
                        <th class="p-3">Type</th>
                        <th class="p-3">On</th>
                        <th class="p-3">Comment</th>
                        <th class="p-3">Date</th>
                        <th class="p-3">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($comments as $comment)
                    <tr class="border-b border-gray-800 hover:bg-[#1f2937] transition">

                        <!-- User -->
                        <td class="p-3 text-white">
                            {{ $comment->user_name }}
                        </td>

                        <!-- Type -->
                        <td class="p-3">
                            @if($comment->type === 'anime')
                                <span class="text-indigo-400 text-xs font-medium">Anime</span>
                            @else
                                <span class="text-emerald-400 text-xs font-medium">Manga</span>
                            @endif
                        </td>

                        <!-- Source -->
                        <td class="p-3">
                            <a href="{{ $comment->source_url }}"
                               target="_blank"
                               class="text-indigo-400 hover:text-indigo-300 transition">
                                {{ $comment->source }}
                            </a>
                            <div class="text-gray-500 text-xs">
                                {{ $comment->episode }}
                            </div>
                        </td>

                        <!-- Comment -->
                        <td class="p-3 text-gray-300 max-w-xs truncate">
                            {{ $comment->body }}
                        </td>

                        <!-- Date -->
                        <td class="p-3 text-gray-500 text-xs">
                            {{ $comment->created_at->diffForHumans() }}
                        </td>

                        <!-- Actions -->
                        <td class="p-3">
                            @if($comment->type === 'anime')
                                <form action="{{ route('admin.comments.destroy.anime', $comment->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Delete this comment?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-400 hover:text-red-300 text-xs transition">
                                        Delete
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.comments.destroy.manga', $comment->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Delete this comment?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-400 hover:text-red-300 text-xs transition">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-10 text-center text-gray-500">
                            <p class="text-lg text-gray-300">No comments found</p>
                            <p class="text-sm mt-1">User comments will appear here</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-800">
            {{ $comments->links() }}
        </div>

    </div>
</div>
@endsection