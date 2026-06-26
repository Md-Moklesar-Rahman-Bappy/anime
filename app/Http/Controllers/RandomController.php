<?php

namespace App\Http\Controllers;

use App\Models\Anime;

class RandomController extends Controller
{
    public function __invoke()
    {
        $anime = Anime::inRandomOrder()->firstOrFail();

        return redirect()->route('anime.detail', $anime->slug);
    }
}
