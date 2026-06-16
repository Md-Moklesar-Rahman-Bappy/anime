<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CommentsController extends Controller
{
    public function store(StoreCommentRequest $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return back()->with('error', 'Please login to comment.');
            }

            // ✅ Optional simple anti-spam (30 seconds)
            if (session()->has('last_comment_time')) {
                if (time() - session('last_comment_time') < 30) {
                    return back()->with('error', 'Please wait before posting another comment.');
                }
            }

            Comment::create([
                'episode_id' => $request->episode_id,
                'user_id' => $user->id,
                'body' => trim($request->body),
            ]);

            session()->put('last_comment_time', time());

            if ($request->wantsJson()) {
                return response()->json(['success' => true]);
            }

            return back()->with('success', 'Comment posted.');

        } catch (\Throwable $e) {
            Log::error('Comment create failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to post comment.');
        }
    }

    public function destroy(Request $request, Comment $comment)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                abort(403);
            }

            // ✅ Allow owner OR admin
            if (
                $comment->user_id !== $user->id &&
                !$user->isSuperAdmin()
            ) {
                abort(403);
            }

            $comment->delete();

            if ($request->wantsJson()) {
                return response()->json(['success' => true]);
            }

            return back()->with('success', 'Comment deleted.');

        } catch (\Throwable $e) {
            Log::error('Comment delete failed', [
                'comment_id' => $comment->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to delete comment.');
        }
    }
}
