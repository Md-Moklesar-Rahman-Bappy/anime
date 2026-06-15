<?php

namespace App\Http\Controllers;

use App\Models\Manga;
use Illuminate\Support\Facades\DB;

class MangaRandomController extends Controller
{
    public function __invoke()
    {
        $randomId = DB::table('manga')->inRandomOrder()->limit(1)->value('id');

        if (! $randomId) {
            return redirect()->route('manga.index')->with('error', 'No manga found.');
        }

        return redirect()->route('manga.detail', Manga::findOrFail($randomId)->slug);
    }
}
