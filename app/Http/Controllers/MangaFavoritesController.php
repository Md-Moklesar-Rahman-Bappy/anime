<?php

namespace App\Http\Controllers;

use App\Models\ChapterBookmark;
use App\Models\MangaFavorite;
use App\Services\FavoriteService;
use Illuminate\Http\Request;

class MangaFavoritesController extends Controller
{
    protected array $categories = [
        'reading'      => 'Reading',
        'completed'    => 'Completed',
        'plan_to_read' => 'Plan to Read',
        'on_hold'      => 'On Hold',
        'dropped'      => 'Dropped',
    ];

    public function __construct(
        protected FavoriteService $favoriteService,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | TOGGLE FAVORITE
    |--------------------------------------------------------------------------
    */

    public function toggle(Request $request)
    {
        $data = $request->validate([
            'manga_id' => 'required|exists:manga,id',
        ]);

        try {
            $user = $request->user();

            if (!$user) {
                return $this->error('Authentication required', 401);
            }

            $favorite = MangaFavorite::where('user_id', $user->id)
                ->where('manga_id', $data['manga_id'])
                ->first();

            if ($favorite) {
                $favorite->delete();

                return $this->success([
                    'status' => 'removed',
                ]);
            }

            MangaFavorite::create([
                'user_id' => $user->id,
                'manga_id' => $data['manga_id'],
                'category' => MangaFavorite::CATEGORY_PLAN,
            ]);

            return $this->success([
                'status' => 'added',
            ]);

        } catch (\Throwable $e) {

            $this->logError('Manga favorite toggle failed', $e, [
                'user_id' => $request->user()?->id,
                'manga_id' => $data['manga_id'],
            ]);

            return $this->error('Failed to toggle favorite', 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE CATEGORY
    |--------------------------------------------------------------------------
    */

    public function updateList(Request $request)
    {
        $data = $request->validate([
            'manga_id' => 'required|exists:manga,id',
            'category' => 'required|string',
        ]);

        try {
            $user = $request->user();

            if (!$user) {
                return $this->error('Authentication required', 401);
            }

            if (!array_key_exists($data['category'], $this->categories)) {
                return $this->error('Invalid category', 422);
            }

            MangaFavorite::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'manga_id' => $data['manga_id'],
                ],
                [
                    'category' => $data['category'],
                ]
            );

            return $this->success([
                'status' => 'updated',
            ]);

        } catch (\Throwable $e) {

            $this->logError('Manga favorite update failed', $e, [
                'user_id' => $request->user()?->id,
                'manga_id' => $data['manga_id'],
            ]);

            return $this->error('Failed to update list', 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SAVE BOOKMARK
    |--------------------------------------------------------------------------
    */

    public function bookmark(Request $request)
    {
        $data = $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'page_number' => 'required|integer|min:1',
        ]);

        try {
            $user = $request->user();

            if (!$user) {
                return $this->error('Authentication required', 401);
            }

            ChapterBookmark::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'chapter_id' => $data['chapter_id'],
                ],
                [
                    'page_number' => $data['page_number'],
                ]
            );

            return $this->success([
                'status' => 'saved',
            ]);

        } catch (\Throwable $e) {

            $this->logError('Chapter bookmark failed', $e, [
                'user_id' => $request->user()?->id,
                'chapter_id' => $data['chapter_id'],
            ]);

            return $this->error('Failed to save bookmark', 500);
        }
    }
}