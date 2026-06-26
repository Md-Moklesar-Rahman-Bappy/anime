<?php

namespace App\Http\Controllers;

use App\Models\ChapterBookmark;
use App\Models\MangaFavorite;
use Illuminate\Http\Request;

class MangaFavoritesController extends Controller
{
    public function toggle(Request $request)
    {
        $data = $request->validate(['manga_id' => 'required|exists:manga,id']);

        $favorite = MangaFavorite::where('user_id', auth()->id())
            ->where('manga_id', $data['manga_id'])
            ->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json(['status' => 'removed']);
        }

        MangaFavorite::create([
            'user_id' => auth()->id(),
            'manga_id' => $data['manga_id'],
            'category' => 'plan_to_read',
        ]);

        return response()->json(['status' => 'added']);
    }

    public function updateList(Request $request)
    {
        $data = $request->validate([
            'manga_id' => 'required|exists:manga,id',
            'category' => 'required|in:reading,completed,plan_to_read,on_hold,dropped',
        ]);

        MangaFavorite::updateOrCreate(
            ['user_id' => auth()->id(), 'manga_id' => $data['manga_id']],
            ['category' => $data['category']]
        );

        return response()->json(['status' => 'updated']);
    }

    public function bookmark(Request $request)
    {
        $data = $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'page_number' => 'required|integer|min:1',
        ]);

        ChapterBookmark::updateOrCreate(
            ['user_id' => auth()->id(), 'chapter_id' => $data['chapter_id']],
            ['page_number' => $data['page_number']]
        );

        return response()->json(['status' => 'saved']);
    }
}
