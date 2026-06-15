<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMangaRequest;
use App\Models\Manga;
use App\Models\MangaGenre;
use App\Services\ContentCrudService;

class MangaController extends Controller
{
    public function __construct(
        protected ContentCrudService $crud,
    ) {}

    public function index()
    {
        return view('admin.manga.index', ['mangaList' => Manga::latest()->paginate(20)]);
    }

    public function create()
    {
        return view('admin.manga.form', ['genres' => MangaGenre::all()]);
    }

    public function store(StoreMangaRequest $request)
    {
        $this->crud->create(Manga::class, $request->validated(), $request->genres, 'genres');

        return redirect()->route('admin.manga.index')->with('success', 'Manga created successfully.');
    }

    public function edit(Manga $manga)
    {
        return view('admin.manga.form', ['manga' => $manga, 'genres' => MangaGenre::all()]);
    }

    public function update(StoreMangaRequest $request, Manga $manga)
    {
        $this->crud->update($manga, $request->validated(), $request->genres, 'genres');

        return redirect()->route('admin.manga.index')->with('success', 'Manga updated successfully.');
    }

    public function destroy(Manga $manga)
    {
        $this->crud->delete($manga);

        return redirect()->route('admin.manga.index')->with('success', 'Manga deleted.');
    }
}
