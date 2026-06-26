<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Manga;
use App\Models\MangaPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MangaChapterController extends Controller
{
    public function index(Manga $manga)
    {
        $chapters = $manga->chapters()->orderBy('number', 'desc')->paginate(20);

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
            'number' => 'required|numeric',
            'title' => 'nullable|string|max:255',
            'pages' => 'nullable|array',
            'pages.*' => 'image|max:5120',
            'page_urls' => 'nullable|string',
        ]);

        $data['manga_id'] = $manga->id;

        $chapter = Chapter::create($data);

        $pageNumber = 1;

        if ($request->hasFile('pages')) {
            foreach ($request->file('pages') as $page) {
                $path = $page->store("manga/{$manga->slug}/chapters/{$chapter->number}", 'public');
                MangaPage::create([
                    'chapter_id' => $chapter->id,
                    'page_number' => $pageNumber++,
                    'image_path' => $path,
                ]);
            }
        }

        if ($request->page_urls) {
            $urls = array_filter(array_map('trim', explode("\n", $request->page_urls)));
            foreach ($urls as $url) {
                MangaPage::create([
                    'chapter_id' => $chapter->id,
                    'page_number' => $pageNumber++,
                    'image_path' => $url,
                ]);
            }
        }

        $chapter->update(['pages_count' => $pageNumber - 1]);

        $manga->update(['chapters_count' => $manga->chapters()->count()]);

        return redirect()->route('admin.manga.chapters.index', $manga)
            ->with('success', 'Chapter created.');
    }

    public function edit(Manga $manga, Chapter $chapter)
    {
        $chapter->load('pages');

        return view('admin.manga.chapters.form', compact('manga', 'chapter'));
    }

    public function update(Request $request, Manga $manga, Chapter $chapter)
    {
        $data = $request->validate([
            'number' => 'required|numeric',
            'title' => 'nullable|string|max:255',
            'pages' => 'nullable|array',
            'pages.*' => 'image|max:5120',
            'page_urls' => 'nullable|string',
            'delete_pages' => 'nullable|array',
            'delete_pages.*' => 'integer|exists:manga_pages,id',
        ]);

        $oldNumber = $chapter->number;

        if ($request->delete_pages) {
            $pages = MangaPage::whereIn('id', $request->delete_pages)->get();
            foreach ($pages as $page) {
                if (! str_starts_with($page->image_path, 'http')) {
                    Storage::disk('public')->delete($page->image_path);
                }
                $page->delete();
            }
        }

        $chapter->update(['number' => $data['number'], 'title' => $data['title'] ?? null]);

        if ($data['number'] != $oldNumber) {
            foreach ($chapter->pages as $page) {
                $oldPath = $page->image_path;
                if (! str_starts_with($oldPath, 'http')) {
                    $newPath = str_replace("/chapters/{$oldNumber}/", "/chapters/{$data['number']}/", $oldPath);
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->move($oldPath, $newPath);
                        $page->update(['image_path' => $newPath]);
                    }
                }
            }
        }

        $pageNumber = $chapter->pages()->max('page_number') + 1;

        if ($request->hasFile('pages')) {
            foreach ($request->file('pages') as $page) {
                $path = $page->store("manga/{$manga->slug}/chapters/{$data['number']}", 'public');
                MangaPage::create([
                    'chapter_id' => $chapter->id,
                    'page_number' => $pageNumber++,
                    'image_path' => $path,
                ]);
            }
        }

        if ($request->page_urls) {
            $urls = array_filter(array_map('trim', explode("\n", $request->page_urls)));
            foreach ($urls as $url) {
                MangaPage::create([
                    'chapter_id' => $chapter->id,
                    'page_number' => $pageNumber++,
                    'image_path' => $url,
                ]);
            }
        }

        $chapter->update(['pages_count' => $chapter->pages()->count()]);

        $manga->update(['chapters_count' => $manga->chapters()->count()]);

        return redirect()->route('admin.manga.chapters.index', $manga)
            ->with('success', 'Chapter updated.');
    }

    public function destroy(Manga $manga, Chapter $chapter)
    {
        foreach ($chapter->pages as $page) {
            if (! str_starts_with($page->image_path, 'http')) {
                Storage::disk('public')->delete($page->image_path);
            }
        }

        $chapter->delete();

        $manga->update(['chapters_count' => $manga->chapters()->count()]);

        return redirect()->route('admin.manga.chapters.index', $manga)
            ->with('success', 'Chapter deleted.');
    }
}
