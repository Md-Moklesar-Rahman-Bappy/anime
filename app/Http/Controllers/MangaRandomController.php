<?php

namespace App\Http\Controllers;

use App\Models\Manga;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class MangaRandomController extends Controller
{
    public function index(): RedirectResponse
    {
        try {
            // ✅ Single efficient query
            $manga = Manga::query()
                ->select('id', 'slug')
                ->inRandomOrder()
                ->first();

            if (!$manga || !$manga->slug) {
                return redirect()
                    ->route('manga.index')
                    ->with('error', 'No manga found.');
            }

            return redirect()->route('manga.detail', $manga->slug);

        } catch (\Throwable $e) {
            Log::error('Random manga selection failed', [
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('manga.index')
                ->with('error', 'Failed to load random manga.');
        }
    }
}
