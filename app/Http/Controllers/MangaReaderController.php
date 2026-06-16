<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\ChapterBookmark;
use App\Models\Manga;
use App\Models\MangaComment;
use App\Services\ViewCounterService;
use Illuminate\Support\Facades\Log;

class MangaReaderController extends Controller
{
    public function __construct(
        protected ViewCounterService $viewCounter,
    ) {}

    public function __invoke(string $slug)
    {
        try {
            // ✅ Load manga with relations
            $manga = Manga::where('slug', $slug)
                ->with('genres')
                ->firstOrFail();

            // ✅ Increment view safely
            $this->viewCounter->increment($manga, 'manga');

            // ✅ Load all chapters once
            $allChapters = $manga->chapters()
                ->orderBy('number')
                ->get(['id', 'number', 'title']);

            if ($allChapters->isEmpty()) {
                abort(404, 'No chapters found.');
            }

            // ✅ Determine chapter number
            $chapterNumber = request('chapter') ?? $allChapters->first()->number;

            // ✅ Find current chapter
            $chapter = $allChapters->firstWhere('number', (int) $chapterNumber);

            if (!$chapter) {
                abort(404, 'Chapter not found.');
            }

            // ✅ Load full chapter with pages
            $chapter = Chapter::with([
                'pages' => fn ($q) => $q->orderBy('page_number')
            ])->findOrFail($chapter->id);

            // ✅ Navigation (NO extra queries)
            $index = $allChapters->search(fn ($c) => $c->id === $chapter->id);

            $prevChapter = $allChapters[$index - 1] ?? null;
            $nextChapter = $allChapters[$index + 1] ?? null;

            // ✅ User bookmark
            $bookmark = null;
            $user = auth()->user();

            if ($user) {
                $bookmark = ChapterBookmark::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'chapter_id' => $chapter->id
                    ],
                    [
                        'page_number' => 1
                    ]
                );
            }

            // ✅ Comments
            $comments = MangaComment::with('user')
                ->where('chapter_id', $chapter->id)
                ->latest()
                ->paginate(20);

            return view('manga-reader', [
                'manga' => $manga,
                'chapter' => $chapter,
                'prevChapter' => $prevChapter,
                'nextChapter' => $nextChapter,
                'allChapters' => $allChapters->sortByDesc('number'),
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