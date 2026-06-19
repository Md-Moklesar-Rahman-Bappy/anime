<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\ChapterBookmark;
use App\Models\Manga;
use App\Models\MangaComment;
use App\Services\ViewCounterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MangaReaderController extends Controller
{
    public function __construct(
        protected ViewCounterService $viewCounter,
    ) {}

    public function __invoke(Request $request, string $slug)
    {
        try {
            $user = $request->user();

            /*
            |--------------------------------------------------------------------------
            | Manga
            |--------------------------------------------------------------------------
            */
            $manga = Manga::where('slug', $slug)
                ->with('genres:id,name,slug')
                ->firstOrFail();

            $this->viewCounter->increment($manga, 'manga');

            /*
            |--------------------------------------------------------------------------
            | Chapter (OPTIMIZED — no full collection load)
            |--------------------------------------------------------------------------
            */
            $chapterNumber = (float) $request->query('chapter');

            $chapter = $manga->chapters()
                ->where('number', $chapterNumber)
                ->first()
                ?? $manga->chapters()->orderBy('number')->first();

            if (!$chapter) {
                abort(404, 'Chapter not found.');
            }

            /*
            |--------------------------------------------------------------------------
            | Pages
            |--------------------------------------------------------------------------
            */
            $chapter->load([
                'pages' => fn ($q) => $q->orderBy('page_number')
            ]);

            /*
            |--------------------------------------------------------------------------
            | Navigation (DB efficient)
            |--------------------------------------------------------------------------
            */
            $prevChapter = $chapter->previous();
            $nextChapter = $chapter->next();

            /*
            |--------------------------------------------------------------------------
            | Chapters list (lightweight)
            |--------------------------------------------------------------------------
            */
            $allChapters = $manga->chapters()
                ->select('id', 'number', 'title')
                ->orderByDesc('number')
                ->get();

            /*
            |--------------------------------------------------------------------------
            | Bookmark
            |--------------------------------------------------------------------------
            */
            $bookmark = null;

            if ($user) {
                $bookmark = ChapterBookmark::getOrCreate(
                    $user->id,
                    $chapter->id
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Comments
            |--------------------------------------------------------------------------
            */
            $comments = MangaComment::with('user:id,name')
                ->where('chapter_id', $chapter->id)
                ->latest()
                ->paginate(20);

            /*
            |--------------------------------------------------------------------------
            | RESPONSE
            |--------------------------------------------------------------------------
            */
            return view('manga-reader', [
                'manga' => $manga,
                'chapter' => $chapter,
                'prevChapter' => $prevChapter,
                'nextChapter' => $nextChapter,
                'allChapters' => $allChapters,
                'bookmark' => $bookmark,
                'comments' => $comments,
            ]);

        } catch (\Throwable $e) {

            Log::error('Manga reader failed', [
                'slug' => $slug,
                'chapter' => request('chapter'),
                'error' => $e->getMessage(),
            ]);

            abort(404, 'Manga not found.');
        }
    }
}