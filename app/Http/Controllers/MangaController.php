<?php

namespace App\Http\Controllers;

use App\Models\Manga;

class MangaController extends Controller
{
    public function __invoke(string $slug)
    {
        $manga = Manga::where('slug', $slug)->firstOrFail();

        return view('manga.show', compact('manga'));
    }
}