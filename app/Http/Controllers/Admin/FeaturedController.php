<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FeaturedController extends Controller
{
    public function index()
    {
        return view('admin.featured.index');
    }

    public function update(Request $request)
    {
        $ids = collect($request->input('featured_ids', []))
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'No anime selected.');
        }

        try {
            DB::transaction(function () use ($ids) {
                $this->resetFeatured();

                foreach ($ids as $order => $id) {
                    Anime::where('id', $id)->update([
                        'featured' => true,
                        'featured_order' => $order + 1,
                    ]);
                }
            });

            return redirect()
                ->route('admin.featured.index')
                ->with('success', 'Featured slider updated!');
        } catch (\Throwable $e) {
            Log::error('Featured update failed', [
                'ids' => $ids,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to update featured slider.');
        }
    }

    public function autoFill(Request $request)
    {
        $mode = $request->input('mode', 'recent');

        $count = min(max((int) $request->input('count', 5), 1), 20);

        try {
            DB::transaction(function () use ($mode, $count) {

                $this->resetFeatured();

                $query = match ($mode) {
                    'top_rated' => Anime::whereNotNull('rating')
                        ->orderByDesc('rating'),

                    'most_viewed' => Anime::orderByDesc('views'),

                    'recent' => Anime::latest(),

                    default => Anime::latest(),
                };

                $animeList = $query
                    ->whereNotNull('thumbnail') // ✅ avoid broken UI
                    ->take($count)
                    ->get();

                foreach ($animeList as $order => $anime) {
                    $anime->update([
                        'featured' => true,
                        'featured_order' => $order + 1,
                    ]);
                }
            });

            $labels = [
                'recent' => 'Recent Uploads',
                'top_rated' => 'Top Rated',
                'most_viewed' => 'Most Popular',
            ];

            return redirect()
                ->route('admin.featured.index')
                ->with('success', 'Featured slider auto-filled from ' . ($labels[$mode] ?? $mode) . '!');
        } catch (\Throwable $e) {
            Log::error('Featured autofill failed', [
                'mode' => $mode,
                'count' => $count,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Auto-fill failed.');
        }
    }

    protected function resetFeatured(): void
    {
        Anime::where('featured', true)->update([
            'featured' => false,
            'featured_order' => null,
        ]);
    }
}
