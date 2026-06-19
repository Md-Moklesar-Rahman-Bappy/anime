<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use Illuminate\Http\RedirectResponse;

class RandomController extends Controller
{
    public function index(): RedirectResponse
    {
        try {
            /*
            |--------------------------------------------------------------------------
            | Random Anime (Safe + Efficient)
            |--------------------------------------------------------------------------
            */
            $anime = Anime::query()
                ->select('id', 'slug')
                ->whereNotNull('slug') // ✅ safety
                ->inRandomOrder()
                ->first();

            /*
            |--------------------------------------------------------------------------
            | Fallback
            |--------------------------------------------------------------------------
            */
            if (!$anime) {
                return redirect()
                    ->route('home')
                    ->with('error', 'No anime found.');
            }

            /*
            |--------------------------------------------------------------------------
            | Redirect
            |--------------------------------------------------------------------------
            */
            return redirect()->route('anime.detail', $anime->slug);

        } catch (\Throwable $e) {

            $this->logError('Random anime selection failed', $e);

            return redirect()
                ->route('home')
                ->with('error', 'Failed to load random anime.');
        }
    }
}