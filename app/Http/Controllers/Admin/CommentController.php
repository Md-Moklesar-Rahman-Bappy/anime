<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\MangaComment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class CommentController extends Controller
{
    public function index()
    {
        $animeComments = Comment::with(['episode.anime', 'user'])->latest()->get()->map(function ($comment) {
            return (object) [
                'id' => $comment->id,
                'type' => 'anime',
                'user_name' => $comment->user->name,
                'body' => $comment->body,
                'source' => $comment->episode->anime->title,
                'source_url' => route('watch', ['slug' => $comment->episode->anime->slug, 'ep' => $comment->episode->number]),
                'episode' => 'Ep ' . $comment->episode->number,
                'created_at' => $comment->created_at,
            ];
        });

        $mangaComments = MangaComment::with(['chapter.manga', 'user'])->latest()->get()->map(function ($comment) {
            return (object) [
                'id' => $comment->id,
                'type' => 'manga',
                'user_name' => $comment->user->name,
                'body' => $comment->body,
                'source' => $comment->chapter->manga->title,
                'source_url' => route('manga.read', ['slug' => $comment->chapter->manga->slug, 'chapter' => $comment->chapter->number]),
                'episode' => 'Ch. ' . rtrim(rtrim($comment->chapter->number, '0'), '.'),
                'created_at' => $comment->created_at,
            ];
        });

        $all = collect($animeComments)->merge($mangaComments)->sortByDesc('created_at')->values();

        $page = Paginator::resolveCurrentPage('page');
        $perPage = 20;
        $comments = new LengthAwarePaginator(
            $all->forPage($page, $perPage),
            $all->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()]
        );

        return view('admin.comments.index', compact('comments'));
    }

    public function destroyAnime(Comment $comment)
    {
        $comment->delete();
        return back()->with('success', 'Anime comment deleted.');
    }

    public function destroyManga(MangaComment $mangaComment)
    {
        $mangaComment->delete();
        return back()->with('success', 'Manga comment deleted.');
    }
}
