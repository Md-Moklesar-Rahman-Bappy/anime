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
        $perPage = 20;
        $page = Paginator::resolveCurrentPage('page');

        $animeQuery = Comment::with(['episode.anime', 'user'])->latest();
        $animeTotal = $animeQuery->count();
        $animeComments = $animeQuery->skip(($page - 1) * $perPage)->take($perPage)->get()
            ->map(fn ($comment) => $this->mapAnimeComment($comment));

        $mangaQuery = MangaComment::with(['chapter.manga', 'user'])->latest();
        $mangaTotal = $mangaQuery->count();
        $mangaComments = $mangaQuery->skip(($page - 1) * $perPage)->take($perPage)->get()
            ->map(fn ($comment) => $this->mapMangaComment($comment));

        $all = collect($animeComments)->merge($mangaComments)->sortByDesc('created_at');

        $comments = new LengthAwarePaginator(
            $all->forPage(1, $perPage),
            $animeTotal + $mangaTotal,
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()]
        );

        return view('admin.comments.index', compact('comments'));
    }

    protected function mapAnimeComment(Comment $comment): object
    {
        return (object) [
            'id' => $comment->id,
            'type' => 'anime',
            'user_name' => $comment->user->name,
            'body' => $comment->body,
            'source' => $comment->episode->anime->title,
            'source_url' => route('watch', ['slug' => $comment->episode->anime->slug, 'ep' => $comment->episode->number]),
            'episode' => 'Ep '.$comment->episode->number,
            'created_at' => $comment->created_at,
        ];
    }

    protected function mapMangaComment(MangaComment $comment): object
    {
        return (object) [
            'id' => $comment->id,
            'type' => 'manga',
            'user_name' => $comment->user->name,
            'body' => $comment->body,
            'source' => $comment->chapter->manga->title,
            'source_url' => route('manga.read', ['slug' => $comment->chapter->manga->slug, 'chapter' => $comment->chapter->number]),
            'episode' => 'Ch. '.rtrim(rtrim($comment->chapter->number, '0'), '.'),
            'created_at' => $comment->created_at,
        ];
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
