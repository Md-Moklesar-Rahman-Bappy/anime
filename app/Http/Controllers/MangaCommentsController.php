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

            if (session()->has('last_manga_comment_time')) {
                if (time() - session('last_manga_comment_time') < 30) {
                    return back()->with('error', 'Please wait before posting.');
                }
            }

            MangaComment::create([
                'chapter_id' => $request->chapter_id,
                'user_id' => $user->id,
                'body' => trim($request->body),
            ]);

            session()->put('last_manga_comment_time', time());

            return back()->with('success', 'Comment posted.');
        } catch (\Throwable $e) {
            Log::error('Manga comment failed', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to post comment.');
        }
    }

    public function destroy(Request $request, MangaComment $mangaComment)
    {
        try {
            $user = auth()->user();

            if (!$user) abort(403);

            if ($mangaComment->user_id !== $user->id && !$user->isSuperAdmin()) {
                abort(403);
            }

            $mangaComment->delete();

            return back()->with('success', 'Comment deleted.');
        } catch (\Throwable $e) {
            Log::error('Delete failed', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Delete failed.');
        }
    }
}