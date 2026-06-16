<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * ✅ Standard JSON success response
     */
    protected function success(array $data = [], string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * ✅ Standard JSON error response
     */
    protected function error(string $message = 'Error', int $status = 400, array $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

    /**
     * ✅ Safe redirect with message
     */
    protected function redirectSuccess(string $route, string $message = 'Success'): RedirectResponse
    {
        return redirect()->route($route)->with('success', $message);
    }

    /**
     * ✅ Safe redirect with error
     */
    protected function redirectError(string $message = 'Something went wrong'): RedirectResponse
    {
        return back()->with('error', $message);
    }

    /**
     * ✅ Central logging helper
     */
    protected function logError(string $context, \Throwable $e, array $extra = []): void
    {
        Log::error($context, array_merge([
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], $extra));
    }
}
