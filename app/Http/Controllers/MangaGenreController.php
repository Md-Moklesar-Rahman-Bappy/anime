<?php

namespace App\Http\Controllers;

use App\Models\MangaGenre;

class MangaGenreController extends Controller
{
    public function __invoke($slug)
    {
        $genre = MangaGenre::where('slug', $slug)->firstOrFail();
        $mangaList = $genre->manga()->latest()->paginate(24);
        $title = $genre->name;

        return view('manga-list', compact('mangaList', 'title'));
    }
}
