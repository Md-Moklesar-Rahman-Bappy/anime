<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /*
    |--------------------------------------------------------------------------
    | JSON RESPONSES
    |--------------------------------------------------------------------------
    */

    protected function success(
        array $data = [],
        string $message = 'Success',
        int $status = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    protected function error(
        string $message = 'Error',
        int $status = 400,
        array $errors = []
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

    /*
    |--------------------------------------------------------------------------
    | HYBRID RESPONSE (VERY IMPORTANT)
    |--------------------------------------------------------------------------
    */

    protected function response(
        $request,
        bool $success,
        string $message,
        int $status = 200,
        array $data = []
    ) {
        if ($request->wantsJson()) {
            return response()->json(array_merge([
                'success' => $success,
                'message' => $message,
            ], $data), $status);
        }

        return back()->with($success ? 'success' : 'error', $message);
    }

    /*
    |--------------------------------------------------------------------------
    | REDIRECT HELPERS
    |--------------------------------------------------------------------------
    */

    protected function redirectSuccess(
        string $route,
        string $message = 'Success'
    ): RedirectResponse {
        return redirect()->route($route)->with('success', $message);
    }

    protected function redirectError(
        string $message = 'Something went wrong'
    ): RedirectResponse {
        return back()->with('error', $message);
    }

    /*
    |--------------------------------------------------------------------------
    | LOGGING (PRO LEVEL)
    |--------------------------------------------------------------------------
    */

    protected function logError(
        string $context,
        \Throwable $e,
        array $extra = []
    ): void {
        Log::error($context, array_merge([
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ], $extra));
    }
}
