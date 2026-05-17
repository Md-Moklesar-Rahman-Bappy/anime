<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Manga;
use App\Models\MangaGenre;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MangaController extends Controller
{
    public function index()
    {
        $mangaList = Manga::latest()->paginate(20);

        return view('admin.manga.index', compact('mangaList'));
    }

    public function create()
    {
        $genres = MangaGenre::all();

        return view('admin.manga.form', compact('genres'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'alternative_titles' => 'nullable|string',
            'type' => 'nullable|string',
            'status' => 'nullable|string',
            'year' => 'nullable|integer',
            'rating' => 'nullable|numeric',
            'score' => 'nullable|numeric',
            'source' => 'nullable|string',
            'author' => 'nullable|string',
            'artist' => 'nullable|string',
            'publisher' => 'nullable|string',
            'thumbnail' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:2048',
            'genres' => 'nullable|array',
            'featured' => 'nullable|boolean',
        ]);

        $data['slug'] = Str::slug($data['title']);
        $data['featured'] = $request->has('featured');

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('manga/thumbnails', 'public');
        }
        if ($request->hasFile('banner')) {
            $data['banner'] = $request->file('banner')->store('manga/banners', 'public');
        }

        $manga = Manga::create($data);

        if ($request->genres) {
            $manga->genres()->sync($request->genres);
        }

        return redirect()->route('admin.manga.index')->with('success', 'Manga created successfully.');
    }

    public function edit(Manga $manga)
    {
        $genres = MangaGenre::all();

        return view('admin.manga.form', compact('manga', 'genres'));
    }

    public function update(Request $request, Manga $manga)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'alternative_titles' => 'nullable|string',
            'type' => 'nullable|string',
            'status' => 'nullable|string',
            'year' => 'nullable|integer',
            'rating' => 'nullable|numeric',
            'score' => 'nullable|numeric',
            'source' => 'nullable|string',
            'author' => 'nullable|string',
            'artist' => 'nullable|string',
            'publisher' => 'nullable|string',
            'thumbnail' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:2048',
            'genres' => 'nullable|array',
            'featured' => 'nullable|boolean',
        ]);

        $data['slug'] = Str::slug($data['title']);
        $data['featured'] = $request->has('featured');

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('manga/thumbnails', 'public');
        }
        if ($request->hasFile('banner')) {
            $data['banner'] = $request->file('banner')->store('manga/banners', 'public');
        }

        $manga->update($data);

        if ($request->genres) {
            $manga->genres()->sync($request->genres);
        }

        return redirect()->route('admin.manga.index')->with('success', 'Manga updated successfully.');
    }

    public function destroy(Manga $manga)
    {
        $manga->delete();

        return redirect()->route('admin.manga.index')->with('success', 'Manga deleted.');
    }
}
