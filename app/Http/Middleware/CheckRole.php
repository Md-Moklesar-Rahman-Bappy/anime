<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        try {
            $user = $request->user();

            /*
            |--------------------------------------------------------------------------
            | Not authenticated
            |--------------------------------------------------------------------------
            */
            if (!$user) {
                return $this->deny($request, 'Unauthenticated');
            }

            $userRole = strtolower((string) $user->role);
            $allowedRoles = array_map('strtolower', $roles);

            /*
            |--------------------------------------------------------------------------
            | Super admin bypass
            |--------------------------------------------------------------------------
            */
            if ($userRole === 'super_admin') {
                return $next($request);
            }

            /*
            |--------------------------------------------------------------------------
            | Role check
            |--------------------------------------------------------------------------
            */
            if (!in_array($userRole, $allowedRoles, true)) {
                return $this->deny($request, 'Unauthorized role', $user);
            }

            return $next($request);

        } catch (\Throwable $e) {

            $this->logError('Role middleware failed', $e, [
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
            ]);

            abort(500, 'Authorization error');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DENY ACCESS
    |--------------------------------------------------------------------------
    */
    protected function deny(Request $request, string $reason, $user = null)
    {
        /*
        |--------------------------------------------------------------------------
        | Log denied access
        |--------------------------------------------------------------------------
        */
        logger()->warning('Role access denied', [
            'user_id' => $user?->id,
            'role' => $user?->role,
            'reason' => $reason,
            'route' => $request->path(),
            'ip' => $request->ip(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | API / AJAX
        |--------------------------------------------------------------------------
        */
        if ($request->wantsJson()) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'You do not have permission.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Web response
        |--------------------------------------------------------------------------
        */
        return response()->view('errors.403', [], 403);
    }

    /*
    |--------------------------------------------------------------------------
    | LOG HELPER
    |--------------------------------------------------------------------------
    */
    protected function logError(string $message, \Throwable $e, array $context = []): void
    {
        logger()->error($message, array_merge($context, [
            'error' => $e->getMessage(),
        ]));
    }
}
