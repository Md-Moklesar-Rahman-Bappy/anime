<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Services\FavoriteService;
use Illuminate\Http\Request;

class FavoritesController extends Controller
{
    protected array $categories = [
        'watching' => 'Watching',
        'completed' => 'Completed',
        'plan_to_watch' => 'Plan to Watch',
        'on_hold' => 'On Hold',
        'dropped' => 'Dropped',
    ];

    public function __construct(
        protected FavoriteService $favoriteService,
    ) {}

    public function myList(Request $request)
    {
        $favorites = $this->favoriteService->myList(
            Favorite::class,
            auth()->id(),
            $request->input('category'),
            $this->categories,
            'anime'
        );

        return view('my-list', [
            'favorites' => $favorites,
            'categories' => $this->categories,
            'activeCategory' => $request->input('category'),
        ]);
    }

    public function toggle(Request $request)
    {
        $request->validate(['anime_id' => 'required|exists:anime,id']);

        return $this->favoriteService->toggle(Favorite::class, 'anime_id', auth()->id(), $request->anime_id);
    }

    public function updateList(Request $request)
    {
        $request->validate([
            'anime_id' => 'required|exists:anime,id',
            'category' => 'nullable|string|in:watching,completed,plan_to_watch,on_hold,dropped',
        ]);

        return $this->favoriteService->updateList(
            Favorite::class,
            'anime_id',
            auth()->id(),
            $request->anime_id,
            $request->input('category')
        );
    }
}
