<?php

namespace App\Http\Controllers;

use App\Http\Requests\ToggleFavoriteRequest;
use App\Http\Requests\UpdateFavoriteListRequest;
use App\Models\Favorite;
use App\Services\FavoriteService;
use Illuminate\Http\Request;

class FavoritesController extends Controller
{
    public const CATEGORIES = [
        'watching'      => 'Watching',
        'completed'     => 'Completed',
        'plan_to_watch' => 'Plan to Watch',
        'on_hold'       => 'On Hold',
        'dropped'       => 'Dropped',
    ];

    public function __construct(
        protected FavoriteService $favoriteService,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | USER LIST
    |--------------------------------------------------------------------------
    */

    public function myList(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return redirect()->route('login')
                    ->with('error', 'Please login to view your list.');
            }

            $category = $request->input('category');

            if ($category && !array_key_exists($category, self::CATEGORIES)) {
                $category = null;
            }

            $favorites = $this->favoriteService->myList(
                Favorite::class,
                $user->id,
                $category,
                self::CATEGORIES,
                'anime'
            );

            return view('my-list', [
                'favorites' => $favorites,
                'categories' => self::CATEGORIES,
                'activeCategory' => $category,
            ]);
        } catch (\Throwable $e) {

            $this->logError('Favorite list load failed', $e, [
                'user_id' => $request->user()?->id,
            ]);

            return $this->redirectError('Failed to load favorites.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | TOGGLE FAVORITE
    |--------------------------------------------------------------------------
    */

    public function toggle(ToggleFavoriteRequest $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->error('Authentication required', 401);
            }

            $result = $this->favoriteService->toggle(
                Favorite::class,
                'anime_id',
                $user->id,
                $request->anime_id
            );

            return $this->success([
                'result' => $result,
            ]);
        } catch (\Throwable $e) {

            $this->logError('Favorite toggle failed', $e, [
                'user_id' => $request->user()?->id,
                'anime_id' => $request->anime_id,
            ]);

            return $this->error('Failed to toggle favorite', 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE CATEGORY
    |--------------------------------------------------------------------------
    */

    public function updateList(UpdateFavoriteListRequest $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->error('Authentication required', 401);
            }

            $category = $request->input('category');

            if (!array_key_exists($category, self::CATEGORIES)) {
                return $this->error('Invalid category', 422);
            }

            $result = $this->favoriteService->updateList(
                Favorite::class,
                'anime_id',
                $user->id,
                $request->anime_id,
                $category
            );

            return $this->success([
                'result' => $result,
            ]);
        } catch (\Throwable $e) {

            $this->logError('Favorite update list failed', $e, [
                'user_id' => $request->user()?->id,
                'anime_id' => $request->anime_id,
            ]);

            return $this->error('Failed to update list', 500);
        }
    }
}
