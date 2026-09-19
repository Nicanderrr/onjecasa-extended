<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && in_array((int) Auth::user()->is_admin, [1, 2], true)) {
            return $next($request);
        }

        return redirect()->route('home_page')->with('error', 'Unauthorized access.');
    }
}
