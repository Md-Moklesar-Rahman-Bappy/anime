<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\MangaComment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    public function index()
    {
        $perPage = 20;
        $page = Paginator::resolveCurrentPage();

        // ✅ Load MORE data before merging
        $animeComments = Comment::with(['episode.anime', 'user'])
            ->latest()
            ->take(100)
            ->get()
            ->map(fn($c) => $this->mapAnimeComment($c));

        $mangaComments = MangaComment::with(['chapter.manga', 'user'])
            ->latest()
            ->take(100)
            ->get()
            ->map(fn($c) => $this->mapMangaComment($c));

        // ✅ Merge + sort
        $all = $animeComments
            ->merge($mangaComments)
            ->sortByDesc('created_at')
            ->values();

        $total = $all->count();

        // ✅ Paginate AFTER merging
        $paginated = new LengthAwarePaginator(
            $all->forPage($page, $perPage),
            $total,
            $perPage,
            $page,
            ['path' => request()->url()]
        );

        return view('admin.comments.index', [
            'comments' => $paginated,
        ]);
    }

    protected function mapAnimeComment(Comment $comment): object
    {
        return (object) [
            'id' => $comment->id,
            'type' => 'anime',
            'user_name' => $comment->user?->name ?? 'Unknown',
            'body' => $comment->body,
            'source' => $comment->episode?->anime?->title ?? 'Unknown',
            'source_url' => route('watch', [
                'slug' => $comment->episode?->anime?->slug ?? '#',
            ]),
            'episode' => 'Ep ' . ($comment->episode?->number ?? '?'),
            'created_at' => $comment->created_at,
        ];
    }

    protected function mapMangaComment(MangaComment $comment): object
    {
        return (object) [
            'id' => $comment->id,
            'type' => 'manga',
            'user_name' => $comment->user?->name ?? 'Unknown',
            'body' => $comment->body,
            'source' => $comment->chapter?->manga?->title ?? 'Unknown',
            'source_url' => route('manga.read', [
                'slug' => $comment->chapter?->manga?->slug ?? '#',
            ]),
            'episode' => 'Ch. ' . rtrim(rtrim($comment->chapter?->number ?? '0', '0'), '.'),
            'created_at' => $comment->created_at,
        ];
    }

    public function destroyAnime(Comment $comment)
    {
        try {
            $comment->delete();

            return back()->with('success', 'Anime comment deleted.');
        } catch (\Throwable $e) {
            Log::error($e);

            return back()->with('error', 'Delete failed.');
        }
    }

    public function destroyManga(MangaComment $mangaComment)
    {
        try {
            $mangaComment->delete();

            return back()->with('success', 'Manga comment deleted.');
        } catch (\Throwable $e) {
            Log::error($e);

            return back()->with('error', 'Delete failed.');
        }
    }
}
