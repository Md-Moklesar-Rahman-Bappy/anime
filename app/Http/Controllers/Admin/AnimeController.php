<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnimeController extends Controller
{
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

        $animeList = $query->paginate(20);

        if (request()->wantsJson()) {
            $html = view('admin.anime._table', compact('animeList'))->render();
            $pagination = view('admin.anime._pagination', compact('animeList'))->render();

            return response()->json([
                'html' => $html,
                'pagination' => $pagination,
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
        $genres = Genre::all();

        return view('admin.anime.form', compact('genres'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string',
            'status' => 'nullable|string',
            'country' => 'nullable|string',
            'season' => 'nullable|string',
            'year' => 'nullable|integer',
            'rating' => 'nullable|numeric',
            'score' => 'nullable|numeric',
            'episodes_count' => 'nullable|integer',
            'duration' => 'nullable|integer',
            'source' => 'nullable|string',
            'studio' => 'nullable|string',
            'producers' => 'nullable|string',
            'licensors' => 'nullable|string',
            'thumbnail' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:2048',
            'genres' => 'nullable|array',
            'featured' => 'nullable|boolean',
        ]);

        $data['slug'] = Str::slug($data['title']);
        $data['featured'] = $request->has('featured');

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('anime/thumbnails', 'public');
        }
        if ($request->hasFile('banner')) {
            $data['banner'] = $request->file('banner')->store('anime/banners', 'public');
        }

        $anime = Anime::create($data);

        if ($request->genres) {
            $anime->genres()->sync($request->genres);
        }

        return redirect()->route('admin.anime.index')->with('success', 'Anime created successfully.');
    }

    public function edit(Anime $anime)
    {
        $genres = Genre::all();

        return view('admin.anime.form', compact('anime', 'genres'));
    }

    public function update(Request $request, Anime $anime)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string',
            'status' => 'nullable|string',
            'country' => 'nullable|string',
            'season' => 'nullable|string',
            'year' => 'nullable|integer',
            'rating' => 'nullable|numeric',
            'score' => 'nullable|numeric',
            'episodes_count' => 'nullable|integer',
            'duration' => 'nullable|integer',
            'source' => 'nullable|string',
            'studio' => 'nullable|string',
            'producers' => 'nullable|string',
            'licensors' => 'nullable|string',
            'thumbnail' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:2048',
            'genres' => 'nullable|array',
            'featured' => 'nullable|boolean',
        ]);

        $data['slug'] = Str::slug($data['title']);
        $data['featured'] = $request->has('featured');

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('anime/thumbnails', 'public');
        }
        if ($request->hasFile('banner')) {
            $data['banner'] = $request->file('banner')->store('anime/banners', 'public');
        }

        $anime->update($data);

        if ($request->genres) {
            $anime->genres()->sync($request->genres);
        }

        return redirect()->route('admin.anime.index')->with('success', 'Anime updated successfully.');
    }

    public function destroy(Anime $anime)
    {
        if ($anime->thumbnail) {
            Storage::disk('public')->delete($anime->thumbnail);
        }
        if ($anime->banner) {
            Storage::disk('public')->delete($anime->banner);
        }

        $anime->delete();

        return redirect()->route('admin.anime.index')->with('success', 'Anime deleted.');
    }
}
