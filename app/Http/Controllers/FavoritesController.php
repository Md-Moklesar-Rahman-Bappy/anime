<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoritesController extends Controller
{
    public function toggle(Request $request)
    {
        $request->validate(['anime_id' => 'required|exists:anime,id']);

        $exists = Favorite::where('user_id', auth()->id())
            ->where('anime_id', $request->anime_id)->first();

        if ($exists) {
            $exists->delete();
            return response()->json(['status' => 'removed']);
        }

        Favorite::create([
            'user_id' => auth()->id(),
            'anime_id' => $request->anime_id,
        ]);

        return response()->json(['status' => 'added']);
    }
}
