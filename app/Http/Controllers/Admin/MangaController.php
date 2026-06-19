<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMangaRequest;
use App\Models\Manga;
use App\Models\MangaGenre;
use App\Services\ContentCrudService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MangaController extends Controller
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

            $query = Manga::query()
                ->with('genres:id,name')
                ->latest('updated_at');

            if ($search) {
                $safe = '%' . addcslashes($search, '%_') . '%';

                $query->where(function ($q) use ($safe) {
                    $q->where('title', 'like', $safe)
                        ->orWhere('type', 'like', $safe)
                        ->orWhere('status', 'like', $safe)
                        ->orWhere('author', 'like', $safe);
                });
            }

            $mangaList = $query
                ->paginate(20)
                ->withQueryString();

            /*
            |--------------------------------------------------------------------------
            | AJAX Response
            |--------------------------------------------------------------------------
            */
            if ($request->ajax()) {
                return response()->json([
                    'html' => view('admin.manga._table', compact('mangaList'))->render(),
                    'pagination' => view('admin.manga._pagination', compact('mangaList'))->render(),
                    'total' => $mangaList->total(),
                ]);
            }

            return view('admin.manga.index', compact('mangaList'));
        } catch (\Throwable $e) {
            Log::error('Manga index failed', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to load manga list.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Create Form
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $genres = MangaGenre::select('id', 'name')->get();

        return view('admin.manga.form', compact('genres'));
    }

    /*
    |--------------------------------------------------------------------------
    | Store Manga
    |--------------------------------------------------------------------------
    */
    public function store(StoreMangaRequest $request)
    {
        try {
            $this->crud->create(
                Manga::class,
                $request->validated(),
                $request->input('genres', []),
                'genres'
            );

            return redirect()
                ->route('admin.manga.index')
                ->with('success', 'Manga created successfully.');
        } catch (\Throwable $e) {
            Log::error('Manga create failed', [
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create manga.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Edit Form
    |--------------------------------------------------------------------------
    */
    public function edit(Manga $manga)
    {
        $manga->load('genres');

        $genres = MangaGenre::select('id', 'name')->get();

        return view('admin.manga.form', compact('manga', 'genres'));
    }

    /*
    |--------------------------------------------------------------------------
    | Update Manga
    |--------------------------------------------------------------------------
    */
    public function update(StoreMangaRequest $request, Manga $manga)
    {
        try {
            $this->crud->update(
                $manga,
                $request->validated(),
                $request->input('genres', []),
                'genres'
            );

            return redirect()
                ->route('admin.manga.index')
                ->with('success', 'Manga updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Manga update failed', [
                'manga_id' => $manga->id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update manga.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Manga
    |--------------------------------------------------------------------------
    */
    public function destroy(Request $request, Manga $manga)
    {
        try {
            $this->crud->delete($manga);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Manga deleted successfully.',
                ]);
            }

            return redirect()
                ->route('admin.manga.index')
                ->with('success', 'Manga deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('Manga delete failed', [
                'manga_id' => $manga->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Delete failed.');
        }
    }
}
