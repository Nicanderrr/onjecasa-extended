<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && ($user->is_active ?? true) && ((int) ($user->is_admin ?? 0) === 2 || ($user->role ?? null) === 'superadmin')) {
            return $next($request);
        }

        return redirect()->route('pos.admin.dashboard')->with('error', 'Superadmin access is required.');
    }
}
