<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoritesController extends Controller
{
    public function myList(Request $request)
    {
        $categories = [
            'watching' => 'Watching',
            'completed' => 'Completed',
            'plan_to_watch' => 'Plan to Watch',
            'on_hold' => 'On Hold',
            'dropped' => 'Dropped',
        ];

        $query = auth()->user()->favorites()
            ->with('anime');

        $activeCategory = $request->input('category');
        if ($activeCategory && array_key_exists($activeCategory, $categories)) {
            $query->where('category', $activeCategory);
        } elseif ($activeCategory === 'favorites') {
            $query->whereNull('category');
        }

        $favorites = $query->latest()->paginate(24)->withQueryString();

        return view('my-list', compact('favorites', 'categories', 'activeCategory'));
    }

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

    public function updateList(Request $request)
    {
        $request->validate([
            'anime_id' => 'required|exists:anime,id',
            'category' => 'nullable|string|in:watching,completed,plan_to_watch,on_hold,dropped',
        ]);

        $category = $request->input('category');

        if (! $category || $category === 'null') {
            Favorite::where('user_id', auth()->id())
                ->where('anime_id', $request->anime_id)->delete();

            return response()->json(['status' => 'ok', 'category' => null]);
        }

        $fav = Favorite::updateOrCreate(
            ['user_id' => auth()->id(), 'anime_id' => $request->anime_id],
            ['category' => $category]
        );

        return response()->json(['status' => 'ok', 'category' => $fav->category]);
    }
}
