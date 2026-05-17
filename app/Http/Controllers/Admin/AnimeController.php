<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AnimeController extends Controller
{
    public function index()
    {
        $animeList = Anime::latest()->paginate(20);

        return view('admin.anime.index', compact('animeList'));
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
        $anime->delete();

        return redirect()->route('admin.anime.index')->with('success', 'Anime deleted.');
    }
}
