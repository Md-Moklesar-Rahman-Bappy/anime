<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\ChapterBookmark;
use App\Models\Manga;

class MangaReaderController extends Controller
{
    public function __invoke($slug)
    {
        $manga = Manga::where('slug', $slug)->with('genres')->firstOrFail();

        $chapterNumber = request('chapter', $manga->chapters()->orderBy('number')->value('number'));

        $chapter = Chapter::where('manga_id', $manga->id)
            ->where('number', $chapterNumber)
            ->with(['pages' => function ($q) {
                $q->orderBy('page_number');
            }])
            ->firstOrFail();

        $prevChapter = Chapter::where('manga_id', $manga->id)
            ->where('number', '<', $chapter->number)
            ->orderBy('number', 'desc')
            ->first();

        $nextChapter = Chapter::where('manga_id', $manga->id)
            ->where('number', '>', $chapter->number)
            ->orderBy('number')
            ->first();

        $allChapters = $manga->chapters()->orderBy('number', 'desc')->get(['id', 'number', 'title']);

        $bookmark = null;
        if (auth()->check()) {
            $bookmark = ChapterBookmark::where('user_id', auth()->id())
                ->where('chapter_id', $chapter->id)
                ->first();

            ChapterBookmark::updateOrCreate(
                ['user_id' => auth()->id(), 'chapter_id' => $chapter->id],
                ['page_number' => 1]
            );
        }

        return view('manga-reader', compact(
            'manga', 'chapter', 'prevChapter', 'nextChapter',
            'allChapters', 'bookmark'
        ));
    }
}
