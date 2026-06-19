<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class RandomController extends Controller
{
    public function index(): RedirectResponse
    {
        try {
            // ✅ Single optimized query
            $anime = Anime::query()
                ->select('id', 'slug')
                ->inRandomOrder()
                ->first();

            if (!$anime || !$anime->slug) {
                return redirect()
                    ->route('home')
                    ->with('error', 'No anime found.');
            }

            return redirect()->route('anime.detail', $anime->slug);

        } catch (\Throwable $e) {
            Log::error('Random anime selection failed', [
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('home')
                ->with('error', 'Failed to load random anime.');
        }
    }
}
