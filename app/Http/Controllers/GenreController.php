<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GenreController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        try {
            $search = trim((string) $request->input('search'));

            $query = Genre::query()
                ->orderBy('name');

            if ($search !== '') {
                $query->where('name', 'like', '%' . addcslashes($search, '%_') . '%');
            }

            $genres = $query->paginate(20)->withQueryString();

            if ($request->ajax()) {
                return response()->json([
                    'html' => view('admin.genres._list', compact('genres'))->render(),
                    'url'  => $request->fullUrl(),
                ]);
            }

            return view('admin.genres.index', compact('genres'));
        } catch (\Throwable $e) {
            Log::error('Admin genres index failed', [
                'error' => $e->getMessage(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load genres.',
                ], 500);
            }

            return back()->with('error', 'Failed to load genres.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('genres', 'name'),
                ],
            ], [
                'name.unique' => 'This genre already exists.',
            ]);

            $genre = Genre::create([
                'name' => trim($data['name']),
                'slug' => Str::slug($data['name']),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Genre added successfully.',
                    'genre'   => $genre,
                ]);
            }

            return back()->with('success', 'Genre added successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->validator->errors()->first(),
                    'errors'  => $e->errors(),
                ], 422);
            }

            throw $e;
        } catch (\Throwable $e) {
            Log::error('Genre store failed', [
                'error' => $e->getMessage(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add genre.',
                ], 500);
            }

            return back()->with('error', 'Failed to add genre.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Genre $genre)
    {
        try {
            $data = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('genres', 'name')->ignore($genre->id),
                ],
            ], [
                'name.unique' => 'This genre already exists.',
            ]);

            $genre->update([
                'name' => trim($data['name']),
                'slug' => Str::slug($data['name']),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Genre updated successfully.',
                    'genre'   => $genre->fresh(),
                ]);
            }

            return back()->with('success', 'Genre updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->validator->errors()->first(),
                    'errors'  => $e->errors(),
                ], 422);
            }

            throw $e;
        } catch (\Throwable $e) {
            Log::error('Genre update failed', [
                'genre_id' => $genre->id,
                'error'    => $e->getMessage(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update genre.',
                ], 500);
            }

            return back()->with('error', 'Failed to update genre.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function destroy(Request $request, Genre $genre)
    {
        try {
            $genre->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Genre deleted successfully.',
                ]);
            }

            return back()->with('success', 'Genre deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('Genre delete failed', [
                'genre_id' => $genre->id,
                'error'    => $e->getMessage(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete genre.',
                ], 500);
            }

            return back()->with('error', 'Failed to delete genre.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORT FROM MAL
    |--------------------------------------------------------------------------
    | Keep this simple and safe. If you already have custom MAL/Jikan import logic,
    | you can replace the body of this method with your service call.
    |--------------------------------------------------------------------------
    */
    public function importFromMal(Request $request)
    {
        try {
            $defaultGenres = [
                'Action',
                'Adventure',
                'Comedy',
                'Drama',
                'Fantasy',
                'Horror',
                'Mystery',
                'Romance',
                'Sci-Fi',
                'Slice of Life',
                'Sports',
                'Supernatural',
                'Thriller',
            ];

            foreach ($defaultGenres as $name) {
                Genre::firstOrCreate(
                    ['name' => $name],
                    ['slug' => Str::slug($name)]
                );
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Genres imported successfully.',
                ]);
            }

            return back()->with('success', 'Genres imported successfully.');
        } catch (\Throwable $e) {
            Log::error('Genre import failed', [
                'error' => $e->getMessage(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Genre import failed.',
                ], 500);
            }

            return back()->with('error', 'Genre import failed.');
        }
    }
}
