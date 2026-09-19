<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = auth()->user();

        if (! $user || ! ($user->is_active ?? true)) {
            abort(403);
        }

        if ($role === 'admin' && (in_array((int) ($user->is_admin ?? 0), [1, 2], true) || in_array(($user->role ?? null), ['admin', 'superadmin', 'branch_admin'], true))) {
            return $next($request);
        }

        if ($role === 'superadmin' && ((int) ($user->is_admin ?? 0) === 2 || ($user->role ?? null) === 'superadmin')) {
            return $next($request);
        }

        if (($user->role ?? null) === $role) {
            return $next($request);
        }

        abort(403);
    }
}
