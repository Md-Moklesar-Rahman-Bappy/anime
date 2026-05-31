<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use Illuminate\Support\Facades\DB;

class RandomController extends Controller
{
    public function __invoke()
    {
        $randomId = DB::table('anime')
            ->inRandomOrder()
            ->limit(1)
            ->value('id');

        abort_if(!$randomId, 404);

        return redirect()->route('anime.detail', Anime::findOrFail($randomId)->slug);
    }
}
