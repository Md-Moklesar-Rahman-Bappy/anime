<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\MangaComment;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index (Unified Comments List)
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        try {
            $perPage = 20;
            $page = Paginator::resolveCurrentPage();

            // ✅ Load more records for better UX
            $animeComments = Comment::with(['episode.anime:id,title,slug', 'user:id,name'])
                ->latest()
                ->take(150)
                ->get()
                ->map(fn($c) => $this->mapAnimeComment($c));

            $mangaComments = MangaComment::with(['chapter.manga:id,title,slug', 'user:id,name'])
                ->latest()
                ->take(150)
                ->get()
                ->map(fn($c) => $this->mapMangaComment($c));

            // ✅ Merge and sort
            $all = $animeComments
                ->merge($mangaComments)
                ->sortByDesc('created_at')
                ->values();

            $total = $all->count();

            // ✅ Proper pagination after merge
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
        } catch (\Throwable $e) {
            Log::error('Admin Comment Index Failed', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to load comments.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Mapping: Anime Comments
    |--------------------------------------------------------------------------
    */
    protected function mapAnimeComment(Comment $comment): object
    {
        return (object) [
            'id' => $comment->id,
            'type' => 'anime',
            'user_name' => $comment->user?->name ?? 'Unknown',
            'body' => $comment->body,
            'source' => $comment->episode?->anime?->title ?? 'Unknown',
            'source_url' => $this->safeRoute('watch', [
                'slug' => $comment->episode?->anime?->slug,
            ]),
            'episode' => 'Ep ' . ($comment->episode?->number ?? '?'),
            'created_at' => $comment->created_at,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Mapping: Manga Comments
    |--------------------------------------------------------------------------
    */
    protected function mapMangaComment(MangaComment $comment): object
    {
        return (object) [
            'id' => $comment->id,
            'type' => 'manga',
            'user_name' => $comment->user?->name ?? 'Unknown',
            'body' => $comment->body,
            'source' => $comment->chapter?->manga?->title ?? 'Unknown',
            'source_url' => $this->safeRoute('manga.read', [
                'slug' => $comment->chapter?->manga?->slug,
            ]),
            'episode' => 'Ch. ' . ($comment->chapter?->number ?? '?'),
            'created_at' => $comment->created_at,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Anime Comment
    |--------------------------------------------------------------------------
    */
    public function destroyAnime(Comment $comment)
    {
        try {
            $comment->delete();

            return redirect()->back()->with('success', 'Anime comment deleted.');
        } catch (\Throwable $e) {
            Log::error('Delete Anime Comment Failed', [
                'id' => $comment->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Delete failed.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Manga Comment
    |--------------------------------------------------------------------------
    */
    public function destroyManga(MangaComment $mangaComment)
    {
        try {
            $mangaComment->delete();

            return redirect()->back()->with('success', 'Manga comment deleted.');
        } catch (\Throwable $e) {
            Log::error('Delete Manga Comment Failed', [
                'id' => $mangaComment->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Delete failed.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Safe Route Builder (Prevents crashes)
    |--------------------------------------------------------------------------
    */
    protected function safeRoute(string $name, array $params = []): string
    {
        try {
            if (empty(array_filter($params))) {
                return '#';
            }

            return route($name, $params);
        } catch (\Throwable $e) {
            return '#';
        }
    }
}
