<?php

namespace App\Http\Controllers;

use App\Models\Manga;
use Illuminate\Http\RedirectResponse;

class MangaRandomController extends Controller
{
    public function index(): RedirectResponse
    {
        try {
            /*
            |--------------------------------------------------------------------------
            | Random Manga (Efficient)
            |--------------------------------------------------------------------------
            */
            $manga = Manga::query()
                ->select('id', 'slug')
                ->whereNotNull('slug') // ✅ safety
                ->inRandomOrder()
                ->first();

            /*
            |--------------------------------------------------------------------------
            | Fallback if nothing found
            |--------------------------------------------------------------------------
            */
            if (!$manga) {
                return $this->redirectErrorRoute(
                    'manga.index',
                    'No manga found.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Redirect to detail page
            |--------------------------------------------------------------------------
            */
            return redirect()->route('manga.detail', $manga->slug);
        } catch (\Throwable $e) {

            $this->logError('Random manga selection failed', $e);

            return $this->redirectErrorRoute(
                'manga.index',
                'Failed to load random manga.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | OPTIONAL HELPER (CLEAN REDIRECT)
    |--------------------------------------------------------------------------
    */

    protected function redirectErrorRoute(string $route, string $message): RedirectResponse
    {
        return redirect()
            ->route($route)
            ->with('error', $message);
    }
}
