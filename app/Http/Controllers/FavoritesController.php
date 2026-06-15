<?php

namespace App\Http\Controllers;

use App\Http\Requests\ToggleFavoriteRequest;
use App\Http\Requests\UpdateFavoriteListRequest;
use App\Models\Favorite;
use App\Services\FavoriteService;
use Illuminate\Http\Request;

class FavoritesController extends Controller
{
    const CATEGORIES = [
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
            self::CATEGORIES,
            'anime'
        );

        return view('my-list', [
            'favorites' => $favorites,
            'categories' => self::CATEGORIES,
            'activeCategory' => $request->input('category'),
        ]);
    }

    public function toggle(ToggleFavoriteRequest $request)
    {
        return $this->favoriteService->toggle(Favorite::class, 'anime_id', auth()->id(), $request->anime_id);
    }

    public function updateList(UpdateFavoriteListRequest $request)
    {
        return $this->favoriteService->updateList(
            Favorite::class,
            'anime_id',
            auth()->id(),
            $request->anime_id,
            $request->input('category')
        );
    }
}
