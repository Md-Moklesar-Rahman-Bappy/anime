<?php

namespace App\Http\Controllers;

use App\Models\ChapterBookmark;
use App\Models\MangaFavorite;
use App\Services\FavoriteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MangaFavoritesController extends Controller
{
    protected array $categories = [
        'reading' => 'Reading',
        'completed' => 'Completed',
        'plan_to_read' => 'Plan to Read',
        'on_hold' => 'On Hold',
        'dropped' => 'Dropped',
    ];

    public function __construct(
        protected FavoriteService $favoriteService,
    ) {}

    public function toggle(Request $request)
    {
        $data = $request->validate([
            'manga_id' => 'required|exists:manga,id'
        ]);

        try {
            $user = auth()->user();

            if (!$user) {
                return $this->error('Authentication required', 401);
            }

            $favorite = MangaFavorite::where('user_id', $user->id)
                ->where('manga_id', $data['manga_id'])
                ->first();

            if ($favorite) {
                $favorite->delete();

                return $this->success(['status' => 'removed']);
            }

            MangaFavorite::create([
                'user_id' => $user->id,
                'manga_id' => $data['manga_id'],
                'category' => 'plan_to_read',
            ]);

            return $this->success(['status' => 'added']);

        } catch (\Throwable $e) {
            Log::error('Manga favorite toggle failed', [
                'user_id' => auth()->id(),
                'manga_id' => $data['manga_id'],
                'error' => $e->getMessage(),
            ]);

            return $this->error('Failed to toggle favorite', 500);
        }
    }

    public function updateList(Request $request)
    {
        $data = $request->validate([
            'manga_id' => 'required|exists:manga,id',
            'category' => 'required|string',
        ]);

        try {
            $user = auth()->user();

            if (!$user) {
                return $this->error('Authentication required', 401);
            }

            // ✅ Validate category safely
            if (!array_key_exists($data['category'], $this->categories)) {
                return $this->error('Invalid category', 422);
            }

            MangaFavorite::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'manga_id' => $data['manga_id']
                ],
                [
                    'category' => $data['category']
                ]
            );

            return $this->success(['status' => 'updated']);

        } catch (\Throwable $e) {
            Log::error('Manga favorite update failed', [
                'user_id' => auth()->id(),
                'manga_id' => $data['manga_id'],
                'error' => $e->getMessage(),
            ]);

            return $this->error('Failed to update list', 500);
        }
    }

    public function bookmark(Request $request)
    {
        $data = $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'page_number' => 'required|integer|min:1',
        ]);

        try {
            $user = auth()->user();

            if (!$user) {
                return $this->error('Authentication required', 401);
            }

            ChapterBookmark::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'chapter_id' => $data['chapter_id']
                ],
                [
                    'page_number' => $data['page_number']
                ]
            );

            return $this->success(['status' => 'saved']);

        } catch (\Throwable $e) {
            Log::error('Chapter bookmark failed', [
                'user_id' => auth()->id(),
                'chapter_id' => $data['chapter_id'],
                'error' => $e->getMessage(),
            ]);

            return $this->error('Failed to save bookmark', 500);
        }
    }
}
