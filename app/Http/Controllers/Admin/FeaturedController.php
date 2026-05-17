<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use Illuminate\Http\Request;

class FeaturedController extends Controller
{
    public function index()
    {
        $featured = Anime::where('featured', true)
            ->orderBy('featured_order')
            ->orderBy('updated_at', 'desc')
            ->get();

        $animeList = Anime::select('id', 'title', 'thumbnail')
            ->orderBy('title')
            ->get();

        return view('admin.featured.index', compact('featured', 'animeList'));
    }

    public function update(Request $request)
    {
        $ids = $request->input('featured_ids', []);

        Anime::where('featured', true)->update(['featured' => false, 'featured_order' => null]);

        foreach ($ids as $order => $id) {
            Anime::where('id', $id)->update(['featured' => true, 'featured_order' => $order + 1]);
        }

        return redirect()->route('admin.featured.index')->with('success', 'Featured slider updated!');
    }

    public function autoFill(Request $request)
    {
        $mode = $request->input('mode', 'recent');
        $count = min(max((int) $request->input('count', 5), 1), 20);

        Anime::where('featured', true)->update(['featured' => false, 'featured_order' => null]);

        $query = match ($mode) {
            'top_rated' => Anime::whereNotNull('rating')->orderBy('rating', 'desc'),
            'most_viewed' => Anime::orderBy('views', 'desc'),
            'recent' => Anime::latest(),
            default => Anime::latest(),
        };

        $anime = $query->take($count)->get();

        foreach ($anime as $order => $a) {
            $a->update(['featured' => true, 'featured_order' => $order + 1]);
        }

        $labels = ['recent' => 'Recent Uploads', 'top_rated' => 'Top Rated', 'most_viewed' => 'Most Popular'];

        return redirect()->route('admin.featured.index')->with('success', 'Featured slider auto-filled from ' . ($labels[$mode] ?? $mode) . '!');
    }
}
