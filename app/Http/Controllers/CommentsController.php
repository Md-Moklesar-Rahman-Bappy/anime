<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentsController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'episode_id' => 'required|exists:episodes,id',
            'body' => 'required|string|max:1000',
        ]);

        Comment::create([
            'episode_id' => $request->episode_id,
            'user_id' => auth()->id(),
            'body' => $request->body,
        ]);

        return back()->with('success', 'Comment posted.');
    }
}
