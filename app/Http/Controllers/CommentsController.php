<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;

class CommentsController extends Controller
{
    public function store(StoreCommentRequest $request)
    {
        Comment::create([
            'episode_id' => $request->episode_id,
            'user_id' => auth()->id(),
            'body' => $request->body,
        ]);

        return back()->with('success', 'Comment posted.');
    }

    public function destroy(Comment $comment)
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $comment->delete();

        return back()->with('success', 'Comment deleted.');
    }
}
