@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-4">

    <h1 class="h4 fw-semibold text-white mb-3">Comments</h1>

    <div class="card" style="background:#111827;border:1px solid #374151;border-radius:1rem;overflow:hidden">
        <div class="table-responsive">
            <table class="table table-dark table-borderless mb-0 align-middle">
                <thead>
                    <tr style="background:#0f172a;color:#9ca3af;border-bottom:1px solid #374151">
                        <th class="p-3 text-start">User</th>
                        <th class="p-3 text-start">Type</th>
                        <th class="p-3 text-start">On</th>
                        <th class="p-3 text-start">Comment</th>
                        <th class="p-3 text-start">Date</th>
                        <th class="p-3 text-start">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($comments as $comment)
                    <tr style="border-bottom:1px solid #374151">
                        <td class="p-3 text-white">{{ $comment->user_name }}</td>
                        <td class="p-3">
                            @if($comment->type === 'anime')
                                <span class="small fw-medium" style="color:#818cf8">Anime</span>
                            @else
                                <span class="small fw-medium" style="color:#34d399">Manga</span>
                            @endif
                        </td>
                        <td class="p-3">
                            <a href="{{ $comment->source_url }}" target="_blank" style="color:#818cf8">{{ $comment->source }}</a>
                            <div class="small" style="color:#6b7280">{{ $comment->episode }}</div>
                        </td>
                        <td class="p-3" style="color:#d1d5db;max-width:300px" class="text-truncate">{{ $comment->body }}</td>
                        <td class="p-3 small" style="color:#6b7280">{{ $comment->created_at->diffForHumans() }}</td>
                        <td class="p-3">
                            @if($comment->type === 'anime')
                            <form action="{{ route('admin.comments.destroy-anime', $comment->id) }}"
                                  method="POST" onsubmit="return confirm('Delete this comment?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm border-0 p-0" style="color:#f87171">Delete</button>
                            </form>
                            @else
                            <form action="{{ route('admin.comments.destroy-manga', $comment->id) }}"
                                  method="POST" onsubmit="return confirm('Delete this comment?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm border-0 p-0" style="color:#f87171">Delete</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-5 text-center" style="color:#6b7280">
                            <p class="h5" style="color:#d1d5db">No comments found</p>
                            <p class="small mt-1">User comments will appear here</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3" style="border-top:1px solid #374151">
            {{ $comments->links() }}
        </div>
    </div>
</div>
@endsection