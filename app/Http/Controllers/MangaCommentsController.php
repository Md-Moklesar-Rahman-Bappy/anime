<?php

namespace App\Http\Controllers;

use App\Models\MangaComment;
use Illuminate\Http\Request;

class MangaCommentsController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'body' => 'required|string|max:5000',
        ]);

        MangaComment::create([
            'chapter_id' => $data['chapter_id'],
            'user_id' => auth()->id(),
            'body' => $data['body'],
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
