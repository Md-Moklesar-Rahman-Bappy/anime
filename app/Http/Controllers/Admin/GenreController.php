<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\JikanApiException;
use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Services\JikanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GenreController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $genres = Genre::latest()->paginate(20);

        return view('admin.genres.index', compact('genres'));
    }

    /*
    |--------------------------------------------------------------------------
    | Store Genre
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:genres,name',
        ]);

        try {
            $data['slug'] = $this->generateUniqueSlug($data['name']);

            Genre::create($data);

            return redirect()
                ->route('admin.genres.index')
                ->with('success', 'Genre created successfully.');
        } catch (\Throwable $e) {
            Log::error('Genre create failed', [
                'name' => $data['name'],
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Failed to create genre.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Update Genre
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Genre $genre)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:genres,name,' . $genre->id,
        ]);

        try {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $genre->id);

            $genre->update($data);

            return redirect()
                ->route('admin.genres.index')
                ->with('success', 'Genre updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Genre update failed', [
                'id' => $genre->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Update failed.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Genre
    |--------------------------------------------------------------------------
    */
    public function destroy(Genre $genre)
    {
        try {
            $genre->delete();

            return redirect()
                ->route('admin.genres.index')
                ->with('success', 'Genre deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('Genre delete failed', [
                'id' => $genre->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Delete failed.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Import Genres From MAL
    |--------------------------------------------------------------------------
    */
    public function importFromMal(JikanService $jikan)
    {
        try {
            $malGenres = collect($jikan->getGenres());
        } catch (JikanApiException $e) {
            Log::error('Jikan fetch failed', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to fetch genres from MAL.');
        }

        $created = 0;
        $updated = 0;

        // ✅ Load all existing genres once (performance)
        $existing = Genre::all()->keyBy(fn($g) => $g->mal_id ?: $g->slug);

        try {
            DB::transaction(function () use ($malGenres, &$created, &$updated, $existing) {

                foreach ($malGenres as $genreData) {

                    $name = $genreData['name'] ?? null;
                    if (!$name) continue;

                    $slug = Str::slug($name);
                    $key = $genreData['mal_id'] ?? $slug;

                    $genre = $existing[$key] ?? null;

                    if ($genre) {

                        // ✅ Update if MAL ID missing
                        if (!$genre->mal_id && !empty($genreData['mal_id'])) {
                            $genre->update([
                                'mal_id' => $genreData['mal_id'],
                                'name' => $name,
                            ]);
                            $updated++;
                        }

                        continue;
                    }

                    Genre::create([
                        'mal_id' => $genreData['mal_id'] ?? null,
                        'name' => $name,
                        'slug' => $this->generateUniqueSlug($name),
                    ]);

                    $created++;
                }
            });
        } catch (\Throwable $e) {
            Log::error('Genre import failed', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Import failed.');
        }

        return redirect()
            ->route('admin.genres.index')
            ->with('success', "Imported {$created} new, {$updated} updated genres.");
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: Unique Slug Generator
    |--------------------------------------------------------------------------
    */
    protected function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);

        if (!$base) {
            $base = 'genre'; // ✅ prevent empty slug
        }

        $slug = $base;
        $i = 1;

        while (
            Genre::where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
