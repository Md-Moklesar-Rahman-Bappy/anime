<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MangaGenre;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MangaGenreController extends Controller
{
    public function index()
    {
        $genres = MangaGenre::latest()->paginate(20);

        return view('admin.manga.genres.index', compact('genres'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        MangaGenre::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
        ]);

        return redirect()->route('admin.manga.genres.index')->with('success', 'Genre created.');
    }

    public function update(Request $request, MangaGenre $mangaGenre)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $mangaGenre->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
        ]);

        return redirect()->route('admin.manga.genres.index')->with('success', 'Genre updated.');
    }

    public function destroy(MangaGenre $mangaGenre)
    {
        $mangaGenre->delete();

        return redirect()->route('admin.manga.genres.index')->with('success', 'Genre deleted.');
    }
}
