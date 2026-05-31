<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMangaCommentRequest;
use App\Models\MangaComment;

class MangaCommentsController extends Controller
{
    public function store(StoreMangaCommentRequest $request)
    {
        MangaComment::create([
            'chapter_id' => $request->chapter_id,
            'user_id' => auth()->id(),
            'body' => $request->body,
        ]);

        return back()->with('success', 'Comment posted.');
    }

    public function destroy(MangaComment $mangaComment)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $mangaComment->delete();

        return back()->with('success', 'Comment deleted.');
    }
}
