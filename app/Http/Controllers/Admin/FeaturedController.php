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

class FeaturedController extends Controller
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
    | Import Genres From MAL (Jikan)
    |--------------------------------------------------------------------------
    */
    public function importFromMal(JikanService $jikan)
    {
        try {
            $malGenres = collect($jikan->getGenres());
        } catch (JikanApiException $e) {
            Log::error('Jikan genre fetch failed', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to fetch genres from MAL.');
        }

        $created = 0;
        $updated = 0;

        // ✅ Preload all existing genres
        $existing = Genre::all()->keyBy(function ($g) {
            return $g->mal_id ?: $g->slug;
        });

        try {
            DB::transaction(function () use ($malGenres, &$created, &$updated, $existing) {

                foreach ($malGenres as $genreData) {

                    if (empty($genreData['name'])) {
                        continue;
                    }

                    $slug = Str::slug($genreData['name']);
                    $key = $genreData['mal_id'] ?? $slug;

                    $existingGenre = $existing[$key] ?? null;

                    if ($existingGenre) {

                        // ✅ update missing MAL ID
                        if (!$existingGenre->mal_id && !empty($genreData['mal_id'])) {
                            $existingGenre->update([
                                'mal_id' => $genreData['mal_id'],
                                'name' => $genreData['name'],
                            ]);

                            $updated++;
                        }

                        continue;
                    }

                    Genre::create([
                        'mal_id' => $genreData['mal_id'] ?? null,
                        'name' => $genreData['name'],
                        'slug' => $this->generateUniqueSlug($genreData['name']),
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
    | Helpers
    |--------------------------------------------------------------------------
    */

    protected function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);

        if (!$base) {
            $base = 'genre';
        }

        $slug = $base;
        $counter = 1;

        while (
            Genre::where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
