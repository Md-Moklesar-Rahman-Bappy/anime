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

    public function index()
    {
        $search = request('search');
        $query = Anime::latest('updated_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhere('studio', 'like', "%{$search}%");
            });
        }

        $animeList = $query->withCount('episodes')->paginate(20);

        if (request()->wantsJson()) {
            return response()->json([
                'html' => view('admin.anime._table', compact('animeList'))->render(),
                'pagination' => view('admin.anime._pagination', compact('animeList'))->render(),
                'total' => $animeList->total(),
            ]);
        }

        return view('admin.anime.index', compact('animeList'));
    }

    public function show(Anime $anime)
    {
        return redirect()->route('admin.anime.edit', $anime);
    }

    public function create()
    {
        return view('admin.anime.form', ['genres' => Genre::all()]);
    }

    public function store(StoreAnimeRequest $request)
    {
        $anime = $this->crud->create(Anime::class, $request->validated(), $request->genres, 'genres');

        return redirect()->route('admin.anime.index')->with('success', 'Anime created successfully.');
    }

    public function edit(Anime $anime)
    {
        return view('admin.anime.form', ['anime' => $anime, 'genres' => Genre::all()]);
    }

    public function update(StoreAnimeRequest $request, Anime $anime)
    {
        $this->crud->update($anime, $request->validated(), $request->genres, 'genres');

        return redirect()->route('admin.anime.index')->with('success', 'Anime updated successfully.');
    }

    public function destroy(Anime $anime)
    {
        $this->crud->delete($anime);

        return redirect()->route('admin.anime.index')->with('success', 'Anime deleted.');
    }
}
