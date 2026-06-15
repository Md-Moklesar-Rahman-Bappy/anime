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
    public function index()
    {
        $genres = Genre::latest()->paginate(20);

        return view('admin.genres.index', compact('genres'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:genres',
        ]);

        $data['slug'] = $this->generateUniqueSlug($data['name']);

        Genre::create($data);

        return redirect()
            ->route('admin.genres.index')
            ->with('success', 'Genre created.');
    }

    public function update(Request $request, Genre $genre)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:genres,name,' . $genre->id,
        ]);

        $data['slug'] = $this->generateUniqueSlug($data['name'], $genre->id);

        $genre->update($data);

        return redirect()
            ->route('admin.genres.index')
            ->with('success', 'Genre updated.');
    }

    public function destroy(Genre $genre)
    {
        $genre->delete();

        return redirect()
            ->route('admin.genres.index')
            ->with('success', 'Genre deleted.');
    }

    public function importFromMal(JikanService $jikan)
    {
        try {
            $malGenres = $jikan->getGenres();
        } catch (JikanApiException $e) {
            Log::error('Jikan genre import failed', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to fetch genres from MAL.');
        }

        $created = 0;
        $updated = 0;

        // ✅ Load existing genres once
        $existing = Genre::all()->keyBy(fn($g) => $g->mal_id ?: $g->slug);

        try {
            DB::transaction(function () use ($malGenres, &$created, &$updated, $existing) {

                foreach ($malGenres as $genreData) {

                    if (empty($genreData['name'])) {
                        continue;
                    }

                    $slug = Str::slug($genreData['name']);
                    $key = $genreData['mal_id'] ?? $slug;

                    $genre = $existing[$key] ?? null;

                    if ($genre) {
                        if (!$genre->mal_id) {
                            $genre->update([
                                'mal_id' => $genreData['mal_id'],
                                'name' => $genreData['name'],
                            ]);
                            $updated++;
                        }
                    } else {
                        Genre::create([
                            'mal_id' => $genreData['mal_id'] ?? null,
                            'name' => $genreData['name'],
                            'slug' => $this->generateUniqueSlug($genreData['name']),
                        ]);
                        $created++;
                    }
                }
            });
        } catch (\Throwable $e) {
            Log::error('Genre import transaction failed', [
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
        $slug = Str::slug($name);
        $original = $slug;
        $i = 1;

        while (
            Genre::where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = "{$original}-{$i}";
            $i++;
        }

        return $slug;
    }
}
