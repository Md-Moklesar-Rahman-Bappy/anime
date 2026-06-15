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

    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Anime::query()->latest('updated_at');

        if ($search) {
            $safeSearch = '%' . addcslashes($search, '%_') . '%';

            $query->where(function ($q) use ($safeSearch) {
                $q->where('title', 'like', $safeSearch)
                    ->orWhere('type', 'like', $safeSearch)
                    ->orWhere('status', 'like', $safeSearch)
                    ->orWhere('studio', 'like', $safeSearch);
            });
        }

        $animeList = $query->withCount('episodes')->paginate(20);

        if ($request->wantsJson()) {
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
        $genres = Genre::select('id', 'name')->get();

        return view('admin.anime.form', compact('genres'));
    }

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
            Log::error($e);

            return back()->with('error', 'Failed to create anime.');
        }
    }

    public function edit(Anime $anime)
    {
        $anime->load('genres');
        $genres = Genre::select('id', 'name')->get();

        return view('admin.anime.form', compact('anime', 'genres'));
    }

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
            Log::error($e);

            return back()->with('error', 'Update failed.');
        }
    }

    public function destroy(Request $request, Anime $anime)
    {
        try {
            $this->crud->delete($anime);

            if ($request->wantsJson()) {
                return response()->json(['success' => true]);
            }

            return redirect()
                ->route('admin.anime.index')
                ->with('success', 'Anime deleted.');
        } catch (\Throwable $e) {
            Log::error($e);

            return back()->with('error', 'Delete failed.');
        }
    }
}
