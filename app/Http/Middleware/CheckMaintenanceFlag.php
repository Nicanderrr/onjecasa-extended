<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceFlag
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Schema::hasTable('pos_settings')) {
            return $next($request);
        }

        $settings = $this->settings();

        if (! (bool) ($settings['maintenance_enabled'] ?? false)) {
            return $next($request);
        }

        if ($this->shouldBypass($request)) {
            return $next($request);
        }

        return response()->view('errors.maintenance', [
            'note' => $settings['maintenance_note'] ?? '',
        ], 503);
    }

    private function settings(): array
    {
        $raw = DB::table('pos_settings')->where('key', 'superadmin_security')->value('value');
        $settings = $raw ? json_decode($raw, true) : [];

        return is_array($settings) ? $settings : [];
    }

    private function shouldBypass(Request $request): bool
    {
        $user = Auth::user();

        if ($user && in_array((int) ($user->is_admin ?? 0), [1, 2], true)) {
            return true;
        }

        if ($user && in_array(($user->role ?? null), ['admin', 'superadmin', 'cashier'], true)) {
            return true;
        }

        return $request->is(
            'login',
            'register',
            'logout',
            'admin*',
            'pos-admin*',
            'superadmin*',
            'cashier*',
            'livewire*',
            'media/product*'
        );
    }
}
