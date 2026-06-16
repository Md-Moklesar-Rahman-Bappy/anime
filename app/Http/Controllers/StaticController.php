<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class StaticController extends Controller
{
    protected array $allowedPages = [
        'faq',
        'about',
        'contact',
        'dmca',
        'terms',
    ];

    public function show(string $page): View
    {
        try {
            // ✅ Validate allowed pages
            if (!in_array($page, $this->allowedPages)) {
                abort(404);
            }

            return view("static.{$page}");

        } catch (\Throwable $e) {
            Log::error('Static page load failed', [
                'page' => $page,
                'error' => $e->getMessage(),
            ]);

            abort(404);
        }
    }
}