<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMangaCommentRequest;
use App\Models\MangaComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MangaCommentsController extends Controller
{
    public function store(StoreMangaCommentRequest $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return back()->with('error', 'Please login to comment.');
            }

            // ✅ Anti-spam (30 seconds)
            if (session()->has('last_manga_comment_time')) {
                if (time() - session('last_manga_comment_time') < 30) {
                    return back()->with('error', 'Please wait before posting another comment.');
                }
            }

            MangaComment::create([
                'chapter_id' => $request->chapter_id,
                'user_id' => $user->id,
                'body' => trim($request->body),
            ]);

            session()->put('last_manga_comment_time', time());

            if ($request->wantsJson()) {
                return response()->json(['success' => true]);
            }

            return back()->with('success', 'Comment posted.');

        } catch (\Throwable $e) {
            Log::error('Manga comment create failed', [
                'user_id' => auth()->id(),
                'chapter_id' => $request->chapter_id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to post comment.');
        }
    }

    public function destroy(Request $request, MangaComment $mangaComment)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                abort(403);
            }

            // ✅ Allow owner OR admin
            if (
                $mangaComment->user_id !== $user->id &&
                !$user->isSuperAdmin()
            ) {
                abort(403);
            }

            $mangaComment->delete();

            if ($request->wantsJson()) {
                return response()->json(['success' => true]);
            }

            return back()->with('success', 'Comment deleted.');

        } catch (\Throwable $e) {
            Log::error('Manga comment delete failed', [
                'comment_id' => $mangaComment->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to delete comment.');
        }
    }
}