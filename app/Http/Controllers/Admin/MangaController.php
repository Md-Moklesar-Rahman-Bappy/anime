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

    public function index(Request $request)
    {
        $query = Manga::query()->latest('updated_at');

        $mangaList = $query
            ->with('genres')
            ->paginate(20);

        if ($request->wantsJson()) {
            return response()->json([
                'html' => view('admin.manga._table', compact('mangaList'))->render(),
                'pagination' => view('admin.manga._pagination', compact('mangaList'))->render(),
                'total' => $mangaList->total(),
            ]);
        }

        return view('admin.manga.index', compact('mangaList'));
    }

    public function create()
    {
        $genres = MangaGenre::select('id', 'name')->get();

        return view('admin.manga.form', compact('genres'));
    }

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

    public function edit(Manga $manga)
    {
        $manga->load('genres');

        $genres = MangaGenre::select('id', 'name')->get();

        return view('admin.manga.form', compact('manga', 'genres'));
    }

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

    public function destroy(Request $request, Manga $manga)
    {
        try {
            $this->crud->delete($manga);

            if ($request->wantsJson()) {
                return response()->json(['success' => true]);
            }

            return redirect()
                ->route('admin.manga.index')
                ->with('success', 'Manga deleted.');
        } catch (\Throwable $e) {
            Log::error('Manga delete failed', [
                'manga_id' => $manga->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Delete failed.');
        }
    }
}