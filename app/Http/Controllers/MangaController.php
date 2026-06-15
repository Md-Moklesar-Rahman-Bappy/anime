<?php

namespace App\Http\Controllers;

use App\Models\Manga;
use App\Services\RelatedContentService;
use App\Services\ViewCounterService;

class MangaController extends Controller
{
    public function __construct(
        protected ViewCounterService $viewCounter,
        protected RelatedContentService $relatedContent,
    ) {}

    public function __invoke($slug)
    {
        $manga = Manga::where('slug', $slug)
            ->with(['genres', 'chapters' => fn ($q) => $q->orderBy('number', 'desc')])
            ->firstOrFail();

        $this->viewCounter->increment($manga, 'manga');

        $related = $this->relatedContent->byGenres($manga, $manga->genres, 'genres');

        $isFavorited = auth()->check()
            && $manga->favoritedBy()->where('user_id', auth()->id())->exists();

        return view('manga-detail', compact('manga', 'related', 'isFavorited'));
    }
}
