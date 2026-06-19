<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Manga;
use App\Models\MangaPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MangaChapterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Chapter List
    |--------------------------------------------------------------------------
    */
    public function index(Manga $manga)
    {
        $chapters = $manga->chapters()
            ->select('id', 'manga_id', 'number', 'title', 'pages_count', 'created_at')
            ->orderByDesc('number')
            ->paginate(20)
            ->withQueryString();

        return view('admin.manga.chapters.index', compact('manga', 'chapters'));
    }

    /*
    |--------------------------------------------------------------------------
    | Create Form
    |--------------------------------------------------------------------------
    */
    public function create(Manga $manga)
    {
        return view('admin.manga.chapters.form', [
            'manga' => $manga,
            'chapter' => null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Store Chapter
    |--------------------------------------------------------------------------
    */
    public function store(Request $request, Manga $manga)
    {
        $data = $this->validateChapter($request, $manga);

        $uploadedFiles = [];

        try {
            DB::transaction(function () use ($request, $manga, $data, &$uploadedFiles) {

                $chapter = Chapter::create([
                    'manga_id' => $manga->id,
                    'number' => $data['number'],
                    'title' => $data['title'] ?? null,
                    'pages_count' => 0,
                ]);

                $pageNumber = 1;

                /*
                |--------------------------------------------------------------------------
                | Uploaded Pages
                |--------------------------------------------------------------------------
                */
                if ($request->hasFile('pages')) {
                    foreach ($request->file('pages') as $page) {
                        $path = $page->store(
                            $this->chapterStoragePath($manga, $chapter),
                            'public'
                        );

                        $uploadedFiles[] = $path;

                        MangaPage::create([
                            'chapter_id' => $chapter->id,
                            'page_number' => $pageNumber++,
                            'image_path' => $path,
                        ]);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Remote Page URLs
                |--------------------------------------------------------------------------
                */
                foreach ($this->parsePageUrls($request->input('page_urls')) as $url) {
                    MangaPage::create([
                        'chapter_id' => $chapter->id,
                        'page_number' => $pageNumber++,
                        'image_path' => $url,
                    ]);
                }

                $this->normalizePageNumbers($chapter);
                $this->syncChapterPageCount($chapter);
                $this->syncMangaChapterCount($manga);
            });

            return redirect()
                ->route('admin.manga.chapters.index', $manga)
                ->with('success', 'Chapter created successfully.');
        } catch (\Throwable $e) {
            $this->deleteUploadedFiles($uploadedFiles);

            $this->logError('Manga chapter create failed', $e, [
                'manga_id' => $manga->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create chapter.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Edit Form
    |--------------------------------------------------------------------------
    */
    public function edit(Manga $manga, Chapter $chapter)
    {
        $this->ensureChapterBelongsToManga($manga, $chapter);

        $chapter->load([
            'pages' => fn($q) => $q->orderBy('page_number')->orderBy('id'),
        ]);

        return view('admin.manga.chapters.form', compact('manga', 'chapter'));
    }

    /*
    |--------------------------------------------------------------------------
    | Update Chapter
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Manga $manga, Chapter $chapter)
    {
        $this->ensureChapterBelongsToManga($manga, $chapter);

        $data = $this->validateChapter($request, $manga, $chapter);

        $uploadedFiles = [];
        $filesToDeleteAfterCommit = [];

        try {
            DB::transaction(function () use (
                $request,
                $manga,
                $chapter,
                $data,
                &$uploadedFiles,
                &$filesToDeleteAfterCommit
            ) {

                /*
                |--------------------------------------------------------------------------
                | Delete Selected Pages
                |--------------------------------------------------------------------------
                */
                if (!empty($data['delete_pages'])) {
                    $pagesToDelete = $chapter->pages()
                        ->whereIn('id', $data['delete_pages'])
                        ->get();

                    foreach ($pagesToDelete as $page) {
                        if ($this->isLocalStoragePath($page->image_path)) {
                            $filesToDeleteAfterCommit[] = $page->image_path;
                        }

                        $page->delete();
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Update Chapter Info
                |--------------------------------------------------------------------------
                */
                $chapter->update([
                    'number' => $data['number'],
                    'title' => $data['title'] ?? null,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Next Page Number
                |--------------------------------------------------------------------------
                */
                $pageNumber = ((int) $chapter->pages()->max('page_number')) + 1;

                /*
                |--------------------------------------------------------------------------
                | Add Uploaded Pages
                |--------------------------------------------------------------------------
                */
                if ($request->hasFile('pages')) {
                    foreach ($request->file('pages') as $page) {
                        $path = $page->store(
                            $this->chapterStoragePath($manga, $chapter),
                            'public'
                        );

                        $uploadedFiles[] = $path;

                        MangaPage::create([
                            'chapter_id' => $chapter->id,
                            'page_number' => $pageNumber++,
                            'image_path' => $path,
                        ]);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Add Remote Page URLs
                |--------------------------------------------------------------------------
                */
                foreach ($this->parsePageUrls($request->input('page_urls')) as $url) {
                    MangaPage::create([
                        'chapter_id' => $chapter->id,
                        'page_number' => $pageNumber++,
                        'image_path' => $url,
                    ]);
                }

                $this->normalizePageNumbers($chapter);
                $this->syncChapterPageCount($chapter);
                $this->syncMangaChapterCount($manga);
            });

            /*
            |--------------------------------------------------------------------------
            | Delete old files only after DB transaction succeeded
            |--------------------------------------------------------------------------
            */
            $this->deleteUploadedFiles($filesToDeleteAfterCommit);

            return redirect()
                ->route('admin.manga.chapters.index', $manga)
                ->with('success', 'Chapter updated successfully.');
        } catch (\Throwable $e) {
            $this->deleteUploadedFiles($uploadedFiles);

            $this->logError('Manga chapter update failed', $e, [
                'manga_id' => $manga->id,
                'chapter_id' => $chapter->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update chapter.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Chapter
    |--------------------------------------------------------------------------
    */
    public function destroy(Manga $manga, Chapter $chapter)
    {
        $this->ensureChapterBelongsToManga($manga, $chapter);

        $filesToDeleteAfterCommit = [];

        try {
            DB::transaction(function () use ($manga, $chapter, &$filesToDeleteAfterCommit) {

                $pages = $chapter->pages()->get();

                foreach ($pages as $page) {
                    if ($this->isLocalStoragePath($page->image_path)) {
                        $filesToDeleteAfterCommit[] = $page->image_path;
                    }
                }

                $chapter->pages()->delete();

                $chapter->delete();

                $this->syncMangaChapterCount($manga);
            });

            /*
            |--------------------------------------------------------------------------
            | Delete files only after DB transaction succeeded
            |--------------------------------------------------------------------------
            */
            $this->deleteUploadedFiles($filesToDeleteAfterCommit);

            return redirect()
                ->route('admin.manga.chapters.index', $manga)
                ->with('success', 'Chapter deleted successfully.');
        } catch (\Throwable $e) {
            $this->logError('Manga chapter delete failed', $e, [
                'manga_id' => $manga->id,
                'chapter_id' => $chapter->id,
            ]);

            return back()->with('error', 'Failed to delete chapter.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */
    protected function validateChapter(Request $request, Manga $manga, ?Chapter $chapter = null): array
    {
        return $request->validate([
            'number' => [
                'required',
                'numeric',
                Rule::unique('chapters', 'number')
                    ->where(fn($q) => $q->where('manga_id', $manga->id))
                    ->ignore($chapter?->id),
            ],

            'title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'pages' => [
                'nullable',
                'array',
            ],

            'pages.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:5120',
            ],

            'page_urls' => [
                'nullable',
                'string',
            ],

            'delete_pages' => [
                'nullable',
                'array',
            ],

            'delete_pages.*' => [
                'integer',
                'exists:manga_pages,id',
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationship Guards
    |--------------------------------------------------------------------------
    */
    protected function ensureChapterBelongsToManga(Manga $manga, Chapter $chapter): void
    {
        abort_if((int) $chapter->manga_id !== (int) $manga->id, 404);
    }

    /*
    |--------------------------------------------------------------------------
    | Page URL Parser
    |--------------------------------------------------------------------------
    */
    protected function parsePageUrls(?string $raw): array
    {
        if (!$raw) {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', $raw))
            ->map(fn($url) => trim($url))
            ->filter()
            ->filter(fn($url) => filter_var($url, FILTER_VALIDATE_URL))
            ->filter(fn($url) => str_starts_with($url, 'http://') || str_starts_with($url, 'https://'))
            ->unique()
            ->values()
            ->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | Count Sync
    |--------------------------------------------------------------------------
    */
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

    /*
    |--------------------------------------------------------------------------
    | Page Number Normalization
    |--------------------------------------------------------------------------
    */
    protected function normalizePageNumbers(Chapter $chapter): void
    {
        $pages = $chapter->pages()
            ->orderBy('page_number')
            ->orderBy('id')
            ->get();

        $counter = 1;

        foreach ($pages as $page) {
            if ((int) $page->page_number !== $counter) {
                $page->update([
                    'page_number' => $counter,
                ]);
            }

            $counter++;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | File Helpers
    |--------------------------------------------------------------------------
    */
    protected function chapterStoragePath(Manga $manga, Chapter $chapter): string
    {
        $safeChapterNumber = str_replace('.', '_', (string) $chapter->number);

        return "manga/{$manga->slug}/chapters/{$safeChapterNumber}";
    }

    protected function isLocalStoragePath(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        return !str_starts_with($path, 'http://') &&
            !str_starts_with($path, 'https://');
    }

    protected function deleteUploadedFiles(array $paths): void
    {
        foreach ($paths as $path) {
            if ($this->isLocalStoragePath($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
