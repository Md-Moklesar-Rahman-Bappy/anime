<?php

namespace App\Http\Controllers;

use App\Models\Manga;

class MangaRandomController extends Controller
{
    public function __invoke()
    {
        $manga = Manga::inRandomOrder()->first();

        if (! $manga) {
            return redirect()->route('manga.index')->with('error', 'No manga found.');
        }

        return redirect()->route('manga.detail', $manga->slug);
    }
}
