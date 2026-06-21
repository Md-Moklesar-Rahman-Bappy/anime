<div class="table-card">

    <div class="overflow-x-auto">
        <table class="w-full text-sm">

            {{-- HEADER --}}
            <thead class="table-head">
                <tr>
                    <th class="p-4 text-left">User</th>
                    <th class="p-4 text-left">Type</th>
                    <th class="p-4 text-left">On</th>
                    <th class="p-4 text-left">Comment</th>
                    <th class="p-4 text-left">Date</th>
                    <th class="p-4 text-left">Actions</th>
                </tr>
            </thead>

            {{-- BODY --}}
            <tbody>

            @forelse($comments as $comment)

            <tr class="table-row">

                {{-- USER --}}
                <td class="p-4 text-white">
                    {{ $comment->user_name }}
                </td>

                {{-- TYPE --}}
                <td class="p-4">
                    <span class="{{ $comment->type === 'anime' ? 'badge-indigo' : 'badge-success' }}">
                        {{ ucfirst($comment->type) }}
                    </span>
                </td>

                {{-- SOURCE --}}
                <td class="p-4 text-sm">

                    {{-- ✅ FIX: link restored --}}
                    <a href="{{ $comment->source_url }}"
                       target="_blank"
                       class="text-indigo-400 hover:underline">
                        {{ $comment->source }}
                    </a>

                    @if($comment->episode)
                    <div class="text-xs text-gray-500">
                        {{ $comment->episode }}
                    </div>
                    @endif

                </td>

                {{-- COMMENT --}}
                <td class="p-4 text-gray-300 max-w-[300px] truncate">
                    {{ $comment->body }}
                </td>

                {{-- DATE --}}
                <td class="p-4 text-xs text-gray-500">
                    {{ $comment->created_at->diffForHumans() }}
                </td>

                {{-- ACTIONS --}}
                <td class="p-4">

                    <button
                        @click="deleteComment({{ $comment->id }}, '{{ $comment->type }}')"
                        class="text-red-400 hover:text-red-300 text-sm"
                    >
                        Delete
                    </button>

                </td>

            </tr>

            @empty

            <tr>
                <td colspan="6" class="p-8 text-center text-gray-500">

                    <p class="text-white font-medium mb-1">
                        No comments found
                    </p>

                    <p class="text-sm">
                        Comments will appear here
                    </p>

                </td>
            </tr>

            @endforelse

            </tbody>

        </table>
    </div>

</div>