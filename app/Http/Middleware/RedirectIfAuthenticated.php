<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect($this->homeFor(Auth::guard($guard)->user()));
            }
        }

        return $next($request);
    }

    private function homeFor($user): string
    {
        if ((int) ($user->is_admin ?? 0) === 2 || ($user->role ?? null) === 'superadmin') {
            return route('superadmin.dashboard');
        }

        if ((int) ($user->is_admin ?? 0) === 1 || in_array(($user->role ?? null), ['admin', 'branch_admin'], true)) {
            return route('pos.admin.dashboard');
        }

        if (($user->role ?? null) === 'cashier') {
            return route('cashier.pages.show', 'dashboard');
        }

        return route('home_page');
    }
}
