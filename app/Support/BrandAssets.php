<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BrandAssets
{
    public static function logoUrl(): string
    {
        $logo = self::setting('sidebar_logo');

        if ($logo) {
            return asset('assets/admin/img/settings/' . ltrim($logo, '/'));
        }

        return asset('images/onjecasa-logo.svg');
    }

    public static function faviconUrl(): string
    {
        return self::logoUrl();
    }

    public static function systemName(): string
    {
        return self::setting('system_name') ?: config('app.name', 'ONJECASA');
    }

    private static function setting(string $key): ?string
    {
        if (! Schema::hasTable('pos_settings')) {
            return null;
        }

        $value = DB::table('pos_settings')->where('key', $key)->value('value');

        return $value !== null && trim((string) $value) !== '' ? (string) $value : null;
    }
}
