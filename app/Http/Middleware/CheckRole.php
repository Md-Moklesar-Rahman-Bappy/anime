<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        try {
            $user = $request->user();

            // ✅ Not authenticated
            if (!$user) {
                return $this->deny($request, 'Unauthenticated user');
            }

            $userRole = strtolower($user->role);
            $allowedRoles = array_map('strtolower', $roles);

            // ✅ Super admin bypass (optional but recommended)
            if ($userRole === 'super_admin') {
                return $next($request);
            }

            // ✅ Role check
            if (!in_array($userRole, $allowedRoles, true)) {
                return $this->deny($request, 'Unauthorized role', $user);
            }

            return $next($request);

        } catch (\Throwable $e) {
            Log::error('Role middleware failed', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
            ]);

            abort(500, 'Authorization error');
        }
    }

    protected function deny(Request $request, string $reason, $user = null)
    {
        Log::warning('Role access denied', [
            'user_id' => $user?->id,
            'role' => $user?->role,
            'reason' => $reason,
            'route' => $request->path(),
        ]);

        // ✅ API / AJAX response
        if ($request->wantsJson()) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'You do not have permission.',
            ], 403);
        }

        // ✅ Web response
        return response()->view('errors.403', [], 403);
    }
}