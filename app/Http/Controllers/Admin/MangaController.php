<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMangaRequest;
use App\Models\Manga;
use App\Models\MangaGenre;
use App\Services\ContentCrudService;
use Illuminate\Http\Request;

class MangaController extends Controller
{
    public function __construct(
        protected ContentCrudService $crud,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | INDEX (LIST + SEARCH + AJAX)
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        try {
            $search = trim((string) $request->input('search'));

            $query = Manga::query()
                ->with('genres:id,name')
                ->withCount('chapters') // ✅ useful for admin table
                ->select(
                    'id',
                    'title',
                    'slug',
                    'type',
                    'status',
                    'author',
                    'views',
                    'updated_at'
                )
                ->latest('updated_at');

            /*
            |--------------------------------------------------------------------------
            | SEARCH
            |--------------------------------------------------------------------------
            */
            if ($search !== '') {
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
            | AJAX RESPONSE
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

            $this->logError('Manga index failed', $e);

            return $this->redirectError('Failed to load manga list.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE FORM
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $genres = MangaGenre::select('id', 'name')->get();

        return view('admin.manga.form', compact('genres'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
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

            $this->logError('Manga create failed', $e);

            return back()
                ->withInput()
                ->with('error', 'Failed to create manga.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
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
    | UPDATE
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

            $this->logError('Manga update failed', $e, [
                'manga_id' => $manga->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update manga.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function destroy(Request $request, Manga $manga)
    {
        try {
            $this->crud->delete($manga);

            /*
            |--------------------------------------------------------------------------
            | AJAX DELETE
            |--------------------------------------------------------------------------
            */
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

            $this->logError('Manga delete failed', $e, [
                'manga_id' => $manga->id,
            ]);

            return $this->redirectError('Delete failed.');
        }
    }
}
