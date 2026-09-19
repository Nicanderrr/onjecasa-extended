<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $loginImage = $this->settingValue('admin_login_image');
        $loginOverlay = $this->intSetting('admin_login_overlay', 72);
        $loginOverlayColor = $this->settingValue('admin_login_overlay_color') ?: 'slate';
        $pinImage = $this->settingValue('admin_pin_image');
        $pinOverlay = $this->intSetting('admin_pin_overlay', 72);
        $pinOverlayColor = $this->settingValue('admin_pin_overlay_color') ?: 'slate';
        $darkMode = $this->settingValue('admin_dark_mode') === '1';
        $mouseTrailEnabled = $this->settingValue('global_mouse_trail') !== '0';
        $systemName = $this->settingValue('system_name') ?: 'POS';
        $sidebarLogo = $this->settingValue('sidebar_logo');
        return view('pos_admin.settings.index', compact('loginImage', 'loginOverlay', 'loginOverlayColor', 'pinImage', 'pinOverlay', 'pinOverlayColor', 'darkMode', 'mouseTrailEnabled', 'systemName', 'sidebarLogo'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:6'],
            'pincode' => ['nullable', 'string', 'min:4', 'max:10'],
            'login_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'login_overlay' => ['nullable', 'integer', 'min:40', 'max:85'],
            'login_overlay_color' => ['nullable', 'in:black,slate,red,blue,amber'],
            'pin_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'pin_overlay' => ['nullable', 'integer', 'min:40', 'max:85'],
            'pin_overlay_color' => ['nullable', 'in:black,slate,red,blue,amber'],
            'dark_mode' => ['nullable', 'in:0,1'],
            'global_mouse_trail' => ['nullable', 'in:0,1'],
            'system_name' => ['nullable', 'string', 'max:30'],
            'sidebar_logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg,ico', 'max:2048'],
        ]);

        $user = $request->user();
        $user->name = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        if (!empty($data['pincode'])) {
            $user->pincode_hash = Hash::make($data['pincode']);
        }
        $user->save();

        if ($request->hasFile('login_image')) {
            $oldImage = $this->settingValue('admin_login_image');
            $dir = public_path('assets/admin/img/settings');
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            $file = $request->file('login_image');
            $fileName = 'login-' . now()->format('YmdHis') . '-' . random_int(100, 999) . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $fileName);

            DB::table('pos_settings')->updateOrInsert(
                ['key' => 'admin_login_image'],
                ['value' => $fileName, 'updated_at' => now(), 'created_at' => now()]
            );

            if (!empty($oldImage)) {
                $oldPath = $dir . DIRECTORY_SEPARATOR . $oldImage;
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }
        }

        DB::table('pos_settings')->updateOrInsert(
            ['key' => 'admin_login_overlay'],
            [
                'value' => (string) ((int) ($data['login_overlay'] ?? 72)),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
        DB::table('pos_settings')->updateOrInsert(
            ['key' => 'admin_login_overlay_color'],
            [
                'value' => (string) ($data['login_overlay_color'] ?? 'slate'),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        if ($request->hasFile('pin_image')) {
            $oldImage = $this->settingValue('admin_pin_image');
            $dir = public_path('assets/admin/img/settings');
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            $file = $request->file('pin_image');
            $fileName = 'pin-' . now()->format('YmdHis') . '-' . random_int(100, 999) . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $fileName);

            DB::table('pos_settings')->updateOrInsert(
                ['key' => 'admin_pin_image'],
                ['value' => $fileName, 'updated_at' => now(), 'created_at' => now()]
            );

            if (!empty($oldImage)) {
                $oldPath = $dir . DIRECTORY_SEPARATOR . $oldImage;
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }
        }

        DB::table('pos_settings')->updateOrInsert(
            ['key' => 'admin_pin_overlay'],
            [
                'value' => (string) ((int) ($data['pin_overlay'] ?? 72)),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
        DB::table('pos_settings')->updateOrInsert(
            ['key' => 'admin_pin_overlay_color'],
            [
                'value' => (string) ($data['pin_overlay_color'] ?? 'slate'),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('pos_settings')->updateOrInsert(
            ['key' => 'admin_dark_mode'],
            [
                'value' => $request->boolean('dark_mode') ? '1' : '0',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
        DB::table('pos_settings')->updateOrInsert(
            ['key' => 'global_mouse_trail'],
            [
                'value' => $request->boolean('global_mouse_trail') ? '1' : '0',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
        DB::table('pos_settings')->updateOrInsert(['key' => 'system_name'], ['value' => (string)($data['system_name'] ?? 'POS'), 'updated_at' => now(), 'created_at' => now()]);

        if ($request->hasFile('sidebar_logo')) {
            $oldLogo = DB::table('pos_settings')->where('key', 'sidebar_logo')->value('value');
            $dir = public_path('assets/admin/img/settings');
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $file = $request->file('sidebar_logo');
            $fileName = 'logo-' . now()->format('YmdHis') . '-' . random_int(100, 999) . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $fileName);
            DB::table('pos_settings')->updateOrInsert(
                ['key' => 'sidebar_logo'],
                ['value' => $fileName, 'updated_at' => now(), 'created_at' => now()]
            );
            if (!empty($oldLogo)) {
                $oldPath = $dir . DIRECTORY_SEPARATOR . $oldLogo;
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }
        }

        AuditTrail::record('settings_updated', 'Updated POS settings', [
            'auditable_type' => 'settings',
            'properties' => [
                'user' => [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password_changed' => !empty($data['password']),
                    'pincode_changed' => !empty($data['pincode']),
                ],
                'appearance' => [
                    'login_image_changed' => $request->hasFile('login_image'),
                    'login_overlay' => (int) ($data['login_overlay'] ?? 72),
                    'login_overlay_color' => $data['login_overlay_color'] ?? 'slate',
                    'pin_image_changed' => $request->hasFile('pin_image'),
                    'pin_overlay' => (int) ($data['pin_overlay'] ?? 72),
                    'pin_overlay_color' => $data['pin_overlay_color'] ?? 'slate',
                    'dark_mode' => $request->boolean('dark_mode'),
                    'mouse_trail' => $request->boolean('global_mouse_trail'),
                    'system_name' => $data['system_name'] ?? 'POS',
                    'sidebar_logo_changed' => $request->hasFile('sidebar_logo'),
                ],
            ],
        ]);

        return redirect()->route('pos.admin.settings.index')->with('success', 'Settings updated');
    }

    private function settingValue(string $key): ?string
    {
        if (!Schema::hasTable('pos_settings')) {
            return null;
        }

        $value = DB::table('pos_settings')->where('key', $key)->value('value');

        return $value !== null ? (string) $value : null;
    }

    private function intSetting(string $key, int $default): int
    {
        $value = $this->settingValue($key);

        return is_numeric($value) ? (int) $value : $default;
    }
}
