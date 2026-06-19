<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StaticController extends Controller
{
    protected array $allowedPages = [
        'faq',
        'about',
        'contact',
        'dmca',
        'terms',
    ];

    public function show(Request $request)
    {
        try {
            /*
            |--------------------------------------------------------------------------
            | Get Page from Route Name
            |--------------------------------------------------------------------------
            */
            $page = $request->route()->getName();

            /*
            |--------------------------------------------------------------------------
            | Validate Allowed Pages
            |--------------------------------------------------------------------------
            */
            if (!in_array($page, $this->allowedPages, true)) {
                abort(404);
            }

            /*
            |--------------------------------------------------------------------------
            | Render View
            |--------------------------------------------------------------------------
            */
            return view("static.{$page}");
        } catch (\Throwable $e) {

            $this->logError('Static page load failed', $e, [
                'page' => $request->route()?->getName(),
            ]);

            abort(404);
        }
    }
}
