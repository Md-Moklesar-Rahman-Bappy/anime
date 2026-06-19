<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\MangaComment;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class CommentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX (UNIFIED COMMENTS LIST)
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        try {
            $perPage = 20;
            $page = Paginator::resolveCurrentPage();

            /*
            |--------------------------------------------------------------------------
            | LOAD COMMENTS (LIMITED FOR PERFORMANCE)
            |--------------------------------------------------------------------------
            */
            $animeComments = Comment::with([
                    'episode.anime:id,title,slug',
                    'user:id,name'
                ])
                ->select('id', 'user_id', 'episode_id', 'body', 'created_at')
                ->latest()
                ->take(150)
                ->get()
                ->map(fn ($c) => $this->mapAnimeComment($c));

            $mangaComments = MangaComment::with([
                    'chapter.manga:id,title,slug',
                    'user:id,name'
                ])
                ->select('id', 'user_id', 'chapter_id', 'body', 'created_at')
                ->latest()
                ->take(150)
                ->get()
                ->map(fn ($c) => $this->mapMangaComment($c));

            /*
            |--------------------------------------------------------------------------
            | MERGE + SORT
            |--------------------------------------------------------------------------
            */
            $all = $animeComments
                ->merge($mangaComments)
                ->sortByDesc('created_at')
                ->values();

            /*
            |--------------------------------------------------------------------------
            | MANUAL PAGINATION
            |--------------------------------------------------------------------------
            */
            $paginated = new LengthAwarePaginator(
                $all->forPage($page, $perPage),
                $all->count(),
                $perPage,
                $page,
                ['path' => $request->url()]
            );

            return view('admin.comments.index', [
                'comments' => $paginated,
            ]);

        } catch (\Throwable $e) {

            $this->logError('Admin Comment Index Failed', $e);

            return $this->redirectError('Failed to load comments.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MAP ANIME COMMENT
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
    | MAP MANGA COMMENT
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
    | DELETE ANIME COMMENT
    |--------------------------------------------------------------------------
    */

    public function destroyAnime(Comment $comment)
    {
        try {
            $comment->delete();

            return redirect()->back()
                ->with('success', 'Anime comment deleted.');

        } catch (\Throwable $e) {

            $this->logError('Delete Anime Comment Failed', $e, [
                'id' => $comment->id,
            ]);

            return $this->redirectError('Delete failed.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE MANGA COMMENT
    |--------------------------------------------------------------------------
    */

    public function destroyManga(MangaComment $mangaComment)
    {
        try {
            $mangaComment->delete();

            return redirect()->back()
                ->with('success', 'Manga comment deleted.');

        } catch (\Throwable $e) {

            $this->logError('Delete Manga Comment Failed', $e, [
                'id' => $mangaComment->id,
            ]);

            return $this->redirectError('Delete failed.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SAFE ROUTE BUILDER
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