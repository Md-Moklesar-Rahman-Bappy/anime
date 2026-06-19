<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MangaGenre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MangaGenreController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index (List + AJAX Support)
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        try {
            $genres = MangaGenre::select('id', 'name', 'slug', 'created_at')
                ->latest()
                ->paginate(20)
                ->withQueryString();

            if ($request->ajax()) {
                return response()->json([
                    'html' => view('admin.manga.genres._table', compact('genres'))->render(),
                    'pagination' => view('admin.manga.genres._pagination', compact('genres'))->render(),
                    'total' => $genres->total(),
                ]);
            }

            return view('admin.manga.genres.index', compact('genres'));
        } catch (\Throwable $e) {
            Log::error('MangaGenre index failed', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to load genres.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Store Genre
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:manga_genres,name',
        ]);

        try {
            MangaGenre::create([
                'name' => $data['name'],
                'slug' => $this->generateUniqueSlug($data['name']),
            ]);

            return redirect()
                ->route('admin.manga.genres.index')
                ->with('success', 'Genre created successfully.');
        } catch (\Throwable $e) {
            Log::error('Manga genre create failed', [
                'name' => $data['name'],
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create genre.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Update Genre
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, MangaGenre $mangaGenre)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:manga_genres,name,' . $mangaGenre->id,
        ]);

        try {
            $mangaGenre->update([
                'name' => $data['name'],
                'slug' => $this->generateUniqueSlug($data['name'], $mangaGenre->id),
            ]);

            return redirect()
                ->route('admin.manga.genres.index')
                ->with('success', 'Genre updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Manga genre update failed', [
                'genre_id' => $mangaGenre->id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Update failed.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Genre
    |--------------------------------------------------------------------------
    */
    public function destroy(Request $request, MangaGenre $mangaGenre)
    {
        try {
            $mangaGenre->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Genre deleted successfully.',
                ]);
            }

            return redirect()
                ->route('admin.manga.genres.index')
                ->with('success', 'Genre deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('Manga genre delete failed', [
                'genre_id' => $mangaGenre->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Delete failed.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    protected function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);

        // ✅ Prevent empty slug issue
        if (!$base) {
            $base = 'genre';
        }

        $slug = $base;
        $counter = 1;

        while (
            MangaGenre::where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
