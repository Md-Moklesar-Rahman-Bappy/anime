<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAnimeRequest;
use App\Models\Anime;
use App\Models\Genre;
use App\Services\ContentCrudService;
use Illuminate\Http\Request;

class AnimeController extends Controller
{
    public function __construct(
        protected ContentCrudService $crud,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | INDEX (LIST + SEARCH + AJAX PAGINATION)
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        try {
            $search = trim((string) $request->input('search'));

            $query = Anime::query()
                ->withCount('episodes')
                ->latest('updated_at');

            // ✅ SEARCH
            if ($search !== '') {
                $safe = '%' . addcslashes($search, '%_') . '%';

                $query->where(function ($q) use ($safe) {
                    $q->where('title', 'like', $safe)
                        ->orWhere('type', 'like', $safe)
                        ->orWhere('status', 'like', $safe)
                        ->orWhere('studio', 'like', $safe);
                });
            }

            $animeList = $query->paginate(20)->withQueryString();

            // ✅ AJAX RESPONSE (IMPORTANT)
            if ($request->ajax()) {
                return response()->json([
                    'html' => view('admin.anime._list', compact('animeList'))->render(),
                    'url'  => $request->fullUrl(),
                ]);
            }

            return view('admin.anime.index', compact('animeList'));
        } catch (\Throwable $e) {

            report($e);

            return redirect()
                ->route('admin.anime.index')
                ->with('error', 'Failed to load anime list.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $genres = Genre::select('id', 'name')->get();

        return view('admin.anime.form', compact('genres'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(StoreAnimeRequest $request)
    {
        try {
            $this->crud->create(
                Anime::class,
                $request->validated(),
                $request->input('genres', []),
                'genres'
            );

            return redirect()
                ->route('admin.anime.index')
                ->with('success', 'Anime created successfully.');
        } catch (\Throwable $e) {

            report($e);

            return back()
                ->withInput()
                ->with('error', 'Failed to create anime.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
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
    | UPDATE
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

            report($e);

            return back()
                ->withInput()
                ->with('error', 'Update failed.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function destroy(Request $request, Anime $anime)
    {
        try {
            $this->crud->delete($anime);

            // ✅ AJAX DELETE SUPPORT
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Anime deleted successfully.',
                ]);
            }

            return redirect()
                ->route('admin.anime.index')
                ->with('success', 'Anime deleted.');
        } catch (\Throwable $e) {

            report($e);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Delete failed.',
                ], 500);
            }

            return back()->with('error', 'Delete failed.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW → REDIRECT TO EDIT
    |--------------------------------------------------------------------------
    */
    public function show(Anime $anime)
    {
        return redirect()->route('admin.anime.edit', $anime);
    }
}
