<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Manga;
use App\Models\MangaPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MangaChapterController extends Controller
{
    public function index(Manga $manga)
    {
        $chapters = $manga->chapters()
            ->orderBy('number', 'desc')
            ->paginate(20);

        return view('admin.manga.chapters.index', compact('manga', 'chapters'));
    }

    public function create(Manga $manga)
    {
        $chapter = null;

        return view('admin.manga.chapters.form', compact('manga', 'chapter'));
    }

    public function store(Request $request, Manga $manga)
    {
        $data = $request->validate([
            'number' => [
                'required',
                'numeric',
                Rule::unique('chapters', 'number')->where(fn($q) => $q->where('manga_id', $manga->id)),
            ],
            'title' => 'nullable|string|max:255',
            'pages' => 'nullable|array',
            'pages.*' => 'image|max:5120',
            'page_urls' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request, $manga, $data, &$chapter) {
                $chapter = Chapter::create([
                    'manga_id' => $manga->id,
                    'number' => $data['number'],
                    'title' => $data['title'] ?? null,
                ]);

                $pageNumber = 1;

                if ($request->hasFile('pages')) {
                    foreach ($request->file('pages') as $page) {
                        $path = $page->store(
                            "manga/{$manga->slug}/chapters/{$chapter->number}",
                            'public'
                        );

                        MangaPage::create([
                            'chapter_id' => $chapter->id,
                            'page_number' => $pageNumber++,
                            'image_path' => $path,
                        ]);
                    }
                }

                foreach ($this->parsePageUrls($request->input('page_urls')) as $url) {
                    MangaPage::create([
                        'chapter_id' => $chapter->id,
                        'page_number' => $pageNumber++,
                        'image_path' => $url,
                    ]);
                }

                $this->syncChapterPageCount($chapter);
                $this->syncMangaChapterCount($manga);
            });

            return redirect()
                ->route('admin.manga.chapters.index', $manga)
                ->with('success', 'Chapter created.');
        } catch (\Throwable $e) {
            Log::error('Manga chapter create failed', [
                'manga_id' => $manga->id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create chapter.');
        }
    }

    public function edit(Manga $manga, Chapter $chapter)
    {
        $this->ensureChapterBelongsToManga($manga, $chapter);

        $chapter->load(['pages' => fn($q) => $q->orderBy('page_number')]);

        return view('admin.manga.chapters.form', compact('manga', 'chapter'));
    }

    public function update(Request $request, Manga $manga, Chapter $chapter)
    {
        $this->ensureChapterBelongsToManga($manga, $chapter);

        $data = $request->validate([
            'number' => [
                'required',
                'numeric',
                Rule::unique('chapters', 'number')
                    ->ignore($chapter->id)
                    ->where(fn($q) => $q->where('manga_id', $manga->id)),
            ],
            'title' => 'nullable|string|max:255',
            'pages' => 'nullable|array',
            'pages.*' => 'image|max:5120',
            'page_urls' => 'nullable|string',
            'delete_pages' => 'nullable|array',
            'delete_pages.*' => 'integer',
        ]);

        $oldNumber = $chapter->number;

        try {
            DB::transaction(function () use ($request, $manga, $chapter, $data, $oldNumber) {
                // Delete selected pages (ONLY from this chapter)
                if (!empty($data['delete_pages'])) {
                    $pagesToDelete = $chapter->pages()
                        ->whereIn('id', $data['delete_pages'])
                        ->get();

                    foreach ($pagesToDelete as $page) {
                        if ($this->isLocalStoragePath($page->image_path)) {
                            Storage::disk('public')->delete($page->image_path);
                        }
                        $page->delete();
                    }
                }

                // Update chapter base data
                $chapter->update([
                    'number' => $data['number'],
                    'title' => $data['title'] ?? null,
                ]);

                // Move existing local files if chapter number changed
                if ((string) $data['number'] !== (string) $oldNumber) {
                    $pages = $chapter->pages()->orderBy('page_number')->get();

                    foreach ($pages as $page) {
                        $oldPath = $page->image_path;

                        if ($this->isLocalStoragePath($oldPath)) {
                            $newPath = str_replace(
                                "/chapters/{$oldNumber}/",
                                "/chapters/{$data['number']}/",
                                $oldPath
                            );

                            if ($oldPath !== $newPath && Storage::disk('public')->exists($oldPath)) {
                                Storage::disk('public')->move($oldPath, $newPath);
                                $page->update(['image_path' => $newPath]);
                            }
                        }
                    }
                }

                // Next page number
                $pageNumber = (($chapter->pages()->max('page_number') ?? 0) + 1);

                // Upload new page files
                if ($request->hasFile('pages')) {
                    foreach ($request->file('pages') as $page) {
                        $path = $page->store(
                            "manga/{$manga->slug}/chapters/{$chapter->number}",
                            'public'
                        );

                        MangaPage::create([
                            'chapter_id' => $chapter->id,
                            'page_number' => $pageNumber++,
                            'image_path' => $path,
                        ]);
                    }
                }

                // Add page URLs
                foreach ($this->parsePageUrls($request->input('page_urls')) as $url) {
                    MangaPage::create([
                        'chapter_id' => $chapter->id,
                        'page_number' => $pageNumber++,
                        'image_path' => $url,
                    ]);
                }

                // Normalize numbering after deletes/additions
                $this->normalizePageNumbers($chapter);

                // Refresh counts
                $this->syncChapterPageCount($chapter);
                $this->syncMangaChapterCount($manga);
            });

            return redirect()
                ->route('admin.manga.chapters.index', $manga)
                ->with('success', 'Chapter updated.');
        } catch (\Throwable $e) {
            Log::error('Manga chapter update failed', [
                'manga_id' => $manga->id,
                'chapter_id' => $chapter->id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update chapter.');
        }
    }

    public function destroy(Manga $manga, Chapter $chapter)
    {
        $this->ensureChapterBelongsToManga($manga, $chapter);

        try {
            DB::transaction(function () use ($manga, $chapter) {
                $pages = $chapter->pages()->get();

                foreach ($pages as $page) {
                    if ($this->isLocalStoragePath($page->image_path)) {
                        Storage::disk('public')->delete($page->image_path);
                    }
                }

                MangaPage::where('chapter_id', $chapter->id)->delete();
                $chapter->delete();

                $this->syncMangaChapterCount($manga);
            });

            return redirect()
                ->route('admin.manga.chapters.index', $manga)
                ->with('success', 'Chapter deleted.');
        } catch (\Throwable $e) {
            Log::error('Manga chapter delete failed', [
                'manga_id' => $manga->id,
                'chapter_id' => $chapter->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to delete chapter.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    protected function ensureChapterBelongsToManga(Manga $manga, Chapter $chapter): void
    {
        abort_if($chapter->manga_id !== $manga->id, 404);
    }

    protected function parsePageUrls(?string $raw): array
    {
        if (!$raw) {
            return [];
        }

        return array_values(array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n/', $raw))
        ));
    }

    protected function syncChapterPageCount(Chapter $chapter): void
    {
        $chapter->update([
            'pages_count' => $chapter->pages()->count(),
        ]);
    }

    protected function syncMangaChapterCount(Manga $manga): void
    {
        $manga->update([
            'chapters_count' => $manga->chapters()->count(),
        ]);
    }

    protected function normalizePageNumbers(Chapter $chapter): void
    {
        $pages = $chapter->pages()
            ->orderBy('page_number')
            ->orderBy('id')
            ->get();

        $counter = 1;

        foreach ($pages as $page) {
            if ((int) $page->page_number !== $counter) {
                $page->update(['page_number' => $counter]);
            }
            $counter++;
        }
    }

    protected function isLocalStoragePath(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        return !str_starts_with($path, 'http://') && !str_starts_with($path, 'https://');
    }
}
