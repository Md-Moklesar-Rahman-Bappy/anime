<?php

namespace App\Http\Controllers;

use App\Models\Genre;

class GenreController extends Controller
{
    public function __invoke($slug)
    {
        $genre = Genre::where('slug', $slug)->with('anime')->firstOrFail();
        $animeList = $genre->anime()->latest()->paginate(24);

        return view('genre', compact('genre', 'animeList'));
    }
}
