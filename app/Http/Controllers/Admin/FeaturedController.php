<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\JikanApiException;
use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Services\JikanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $genres = Genre::select('id', 'name', 'slug', 'mal_id')
            ->latest()
            ->paginate(20);

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
            $this->logError('Genre create failed', $e, [
                'name' => $data['name'],
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
            $this->logError('Genre update failed', $e, [
                'id' => $genre->id,
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
            $this->logError('Genre delete failed', $e, [
                'id' => $genre->id,
            ]);

            return back()->with('error', 'Delete failed.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Import From MAL (Optimized)
    |--------------------------------------------------------------------------
    */
    public function importFromMal(JikanService $jikan)
    {
        try {
            $malGenres = collect($jikan->getGenres());
        } catch (JikanApiException $e) {
            $this->logError('Jikan fetch failed', $e);

            return back()->with('error', 'Failed to fetch genres from MAL.');
        }

        $created = 0;
        $updated = 0;

        // ✅ Preload existing genres by mal_id and name
        $existingByMal = Genre::whereNotNull('mal_id')->get()->keyBy('mal_id');
        $existingByName = Genre::all()->keyBy(fn($g) => strtolower($g->name));

        try {
            DB::transaction(function () use (
                $malGenres,
                &$created,
                &$updated,
                $existingByMal,
                $existingByName
            ) {

                foreach ($malGenres as $genreData) {

                    if (empty($genreData['name'])) {
                        continue;
                    }

                    $name = trim($genreData['name']);
                    $malId = $genreData['mal_id'] ?? null;

                    /*
                    |--------------------------------------------------------------------------
                    | Existing (by MAL ID)
                    |--------------------------------------------------------------------------
                    */
                    if ($malId && isset($existingByMal[$malId])) {

                        $genre = $existingByMal[$malId];

                        if ($genre->name !== $name) {
                            $genre->update(['name' => $name]);
                            $updated++;
                        }

                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Existing (by name)
                    |--------------------------------------------------------------------------
                    */
                    $key = strtolower($name);

                    if (isset($existingByName[$key])) {
                        $genre = $existingByName[$key];

                        if (!$genre->mal_id && $malId) {
                            $genre->update(['mal_id' => $malId]);
                            $updated++;
                        }

                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Create new genre
                    |--------------------------------------------------------------------------
                    */
                    Genre::create([
                        'mal_id' => $malId,
                        'name' => $name,
                        'slug' => $this->generateUniqueSlug($name),
                    ]);

                    $created++;
                }
            });
        } catch (\Throwable $e) {
            $this->logError('Genre import failed', $e);

            return back()->with('error', 'Import failed.');
        }

        return redirect()
            ->route('admin.genres.index')
            ->with('success', "Imported {$created}, updated {$updated} genres.");
    }

    /*
    |--------------------------------------------------------------------------
    | Slug Generator
    |--------------------------------------------------------------------------
    */
    protected function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'genre';
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
