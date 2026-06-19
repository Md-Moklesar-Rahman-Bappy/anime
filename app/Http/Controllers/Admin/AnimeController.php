<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAnimeRequest;
use App\Models\Anime;
use App\Models\Genre;
use App\Services\ContentCrudService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnimeController extends Controller
{
    public function __construct(
        protected ContentCrudService $crud,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Index (List + Search + AJAX)
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        try {
            $search = trim($request->input('search'));

            $query = Anime::query()
                ->withCount('episodes')
                ->latest('updated_at');

            if ($search) {
                $safe = '%' . addcslashes($search, '%_') . '%';

                $query->where(function ($q) use ($safe) {
                    $q->where('title', 'like', $safe)
                      ->orWhere('type', 'like', $safe)
                      ->orWhere('status', 'like', $safe)
                      ->orWhere('studio', 'like', $safe);
                });
            }

            $animeList = $query->paginate(20)->withQueryString();

            // ✅ AJAX response (for live search / table updates)
            if ($request->ajax()) {
                return response()->json([
                    'html' => view('admin.anime._table', compact('animeList'))->render(),
                    'pagination' => view('admin.anime._pagination', compact('animeList'))->render(),
                    'total' => $animeList->total(),
                ]);
            }

            return view('admin.anime.index', compact('animeList'));

        } catch (\Throwable $e) {
            Log::error('Admin Anime Index Failed', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to load anime list.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Create Form
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $genres = Genre::select('id', 'name')->get();

        return view('admin.anime.form', compact('genres'));
    }

    /*
    |--------------------------------------------------------------------------
    | Store New Anime
    |--------------------------------------------------------------------------
    */
    public function store(StoreAnimeRequest $request)
    {
        try {
            $anime = $this->crud->create(
                Anime::class,
                $request->validated(),
                $request->input('genres', []),
                'genres'
            );

            return redirect()
                ->route('admin.anime.index')
                ->with('success', 'Anime created successfully.');

        } catch (\Throwable $e) {
            Log::error('Anime Create Failed', [
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Failed to create anime.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Edit Form
    |--------------------------------------------------------------------------
    */
    public function edit(Anime $anime)
    {
        $anime->load('genres');
        $genres = Genre::select('id', 'name')->get();

        return view('admin.anime.form', compact('anime', 'genres'));
    }

    /*
    |--------------------------------------------------------------------------
    | Update Anime
    |--------------------------------------------------------------------------
    */
    public function update(StoreAnimeRequest $request, Anime $anime)
    {
        try {
            $this->crud->update(
                $anime,
                $request->validated(),
                $request->input('genres', []),
                'genres'
            );

            return redirect()
                ->route('admin.anime.index')
                ->with('success', 'Anime updated successfully.');

        } catch (\Throwable $e) {
            Log::error('Anime Update Failed', [
                'id' => $anime->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Update failed.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Anime
    |--------------------------------------------------------------------------
    */
    public function destroy(Request $request, Anime $anime)
    {
        try {
            $this->crud->delete($anime);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Anime deleted successfully.'
                ]);
            }

            return redirect()
                ->route('admin.anime.index')
                ->with('success', 'Anime deleted.');

        } catch (\Throwable $e) {
            Log::error('Anime Delete Failed', [
                'id' => $anime->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Delete failed.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Redirect Show → Edit
    |--------------------------------------------------------------------------
    */
    public function show(Anime $anime)
    {
        return redirect()->route('admin.anime.edit', $anime);
    }
}