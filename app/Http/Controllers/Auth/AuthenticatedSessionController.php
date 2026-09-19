<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\AuditTrail;
use App\Support\BrandAssets;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login-register');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        if ($this->requiresAdminOtp($user)) {
            $request->session()->put('pending_admin_user_id', $user->id);
            $request->session()->put('pending_admin_user_name', $user->name);
            $otpMailNotice = $this->issueAdminOtp($user, $request);

            Auth::guard('web')->logout();

            return redirect()->route('admin.otp.show')->with('otp_mail_notice', $otpMailNotice);
        }

        $home = $this->homeFor($user);

        return redirect()->to($this->safeIntendedUrl($request, $user, $home));
    }

    public function showAdminOtp(Request $request): View|RedirectResponse
    {
        if (Auth::check() && $this->requiresAdminOtp($request->user())) {
            return redirect()->route('pos.admin.dashboard');
        }

        $pendingId = $request->session()->get('pending_admin_user_id');
        if (! $pendingId) {
            return redirect()->route('login');
        }

        $user = User::find($pendingId);
        if (! $user || ! $this->requiresAdminOtp($user)) {
            $request->session()->forget($this->adminOtpSessionKeys());

            return redirect()->route('login');
        }

        if (! $request->session()->has('pending_admin_otp_hash')) {
            $this->issueAdminOtp($user, $request);
        }

        return view('auth.admin-otp', [
            'adminName' => $request->session()->get('pending_admin_user_name', 'Admin'),
            'adminEmail' => $user->email,
            'testOtp' => $request->session()->get('pending_admin_otp'),
            'otpExpiresAt' => optional($request->session()->get('pending_admin_otp_expires_at'))->format('H:i'),
            'splashMedia' => $this->authSplashMedia('admin_pin_image', 'assets/adminhmd/images/png/dasher-ai.png'),
            'overlayStyle' => $this->overlayStyle('admin_pin_overlay', 'admin_pin_overlay_color', '16,185,129'),
            'brandLogo' => BrandAssets::logoUrl(),
            'favicon' => BrandAssets::faviconUrl(),
            'systemName' => BrandAssets::systemName(),
            'otpMailNotice' => session('otp_mail_notice') ?: $this->mailDeliveryNotice(),
        ]);
    }

    public function verifyAdminOtp(Request $request): RedirectResponse
    {
        $request->merge([
            'admin_otp' => trim((string) $request->input('admin_otp', '')),
        ]);

        $request->validate([
            'admin_otp' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
        ]);

        $pendingId = $request->session()->get('pending_admin_user_id');
        if (! $pendingId) {
            return redirect()->route('login');
        }

        $user = User::find($pendingId);
        $otpHash = $request->session()->get('pending_admin_otp_hash');
        $expiresAt = $request->session()->get('pending_admin_otp_expires_at');
        $isExpired = ! $expiresAt || now()->greaterThan($expiresAt);

        if (! $user || ! $this->requiresAdminOtp($user) || ! $otpHash || $isExpired || ! Hash::check($request->admin_otp, $otpHash)) {
            AuditTrail::record('admin_otp_failed', 'Failed admin OTP attempt', [
                'auditable_type' => 'user',
                'auditable_id' => $pendingId,
                'properties' => [
                    'pending_admin_user_id' => $pendingId,
                    'expired' => $isExpired,
                ],
            ]);

            return back()->withErrors(['admin_otp' => $isExpired ? 'This OTP has expired. Please sign in again.' : 'Incorrect OTP code']);
        }

        $request->session()->forget($this->adminOtpSessionKeys());
        Auth::login($user);
        $request->session()->regenerate();
        AuditTrail::record('login', $user->name . ' logged in as admin using OTP');

        return redirect()->to($this->homeFor($user));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function homeFor(User $user): string
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

    private function requiresAdminOtp(User $user): bool
    {
        $role = $user->role ?? null;
        $isSuperadmin = (int) ($user->is_admin ?? 0) === 2 || $role === 'superadmin';
        $isAdmin = (int) ($user->is_admin ?? 0) === 1 || in_array($role, ['admin', 'branch_admin'], true);

        return $isAdmin && ! $isSuperadmin;
    }

    /**
     * @return array<int, string>
     */
    private function adminOtpSessionKeys(): array
    {
        return [
            'pending_admin_user_id',
            'pending_admin_user_name',
            'pending_admin_otp',
            'pending_admin_otp_hash',
            'pending_admin_otp_expires_at',
        ];
    }

    private function issueAdminOtp(User $user, Request $request): ?string
    {
        $otp = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(10);

        $request->session()->put('pending_admin_otp', $otp);
        $request->session()->put('pending_admin_otp_hash', Hash::make($otp));
        $request->session()->put('pending_admin_otp_expires_at', $expiresAt);

        return $this->sendOtpEmail($user, 'Admin login OTP', "Your admin login OTP is {$otp}. This code expires in 10 minutes.");
    }

    private function sendOtpEmail(User $user, string $subject, string $body): ?string
    {
        $notice = $this->mailDeliveryNotice();

        try {
            Mail::raw(
                $body,
                function ($message) use ($user, $subject) {
                    $message->to($user->email, $user->name)->subject($subject);
                }
            );
        } catch (\Throwable $exception) {
            Log::warning('OTP email could not be sent', [
                'user_id' => $user->id,
                'email' => $user->email,
                'mailer' => config('mail.default'),
                'message' => $exception->getMessage(),
            ]);

            return 'Email delivery failed. The testing code is shown on this page while mail settings are fixed.';
        }

        return $notice;
    }

    private function mailDeliveryNotice(): ?string
    {
        $mailer = (string) config('mail.default');

        if (in_array($mailer, ['log', 'array'], true)) {
            return "Email is currently in {$mailer} mode, so OTPs are not delivered to inboxes. The testing code is shown on this page.";
        }

        return null;
    }

    private function authSplashMedia(string $key, string $fallback): array
    {
        if (! Schema::hasTable('pos_settings')) {
            return [
                'url' => asset($fallback),
                'isVideo' => $this->isVideoFile($fallback),
            ];
        }

        $media = DB::table('pos_settings')->where('key', $key)->value('value');
        if (! empty($media)) {
            return [
                'url' => asset('assets/admin/img/settings/' . $media),
                'isVideo' => $this->isVideoFile((string) $media),
            ];
        }

        return [
            'url' => asset($fallback),
            'isVideo' => $this->isVideoFile($fallback),
        ];
    }

    private function settingAsset(string $key, string $fallback): string
    {
        if (! Schema::hasTable('pos_settings')) {
            return asset($fallback);
        }

        $value = DB::table('pos_settings')->where('key', $key)->value('value');

        return ! empty($value)
            ? asset('assets/admin/img/settings/' . $value)
            : asset($fallback);
    }

    private function settingValue(string $key): ?string
    {
        if (! Schema::hasTable('pos_settings')) {
            return null;
        }

        $value = DB::table('pos_settings')->where('key', $key)->value('value');

        return $value !== null ? (string) $value : null;
    }

    private function isVideoFile(string $path): bool
    {
        return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['mp4', 'webm', 'ogg', 'mov'], true);
    }

    private function overlayStyle(string $strengthKey, string $colorKey, string $fallbackRgb): string
    {
        if (! Schema::hasTable('pos_settings')) {
            $strength = 72;
            $rgb = $fallbackRgb;
        } else {
            $strengthValue = DB::table('pos_settings')->where('key', $strengthKey)->value('value');
            $colorValue = DB::table('pos_settings')->where('key', $colorKey)->value('value');

            $strength = is_numeric($strengthValue) ? (int) $strengthValue : 72;
            $rgb = match ((string) $colorValue) {
                'red' => '185,28,28',
                'blue' => '37,99,235',
                'green' => '16,185,129',
                'amber' => '217,119,6',
                'slate' => '51,65,85',
                'purple' => '124,58,237',
                default => $fallbackRgb,
            };
        }

        $alpha = max(0.4, min(0.85, $strength / 100));

        return "background: linear-gradient(160deg, rgba({$rgb}, {$alpha}), rgba(2, 6, 23, 0.55));";
    }

    private function safeIntendedUrl(Request $request, User $user, string $fallback): string
    {
        $intended = $request->session()->pull('url.intended');

        if (! $intended) {
            return $fallback;
        }

        $path = '/'.ltrim((string) (parse_url($intended, PHP_URL_PATH) ?: ''), '/');

        return $this->canUsePath($user, $path) ? $intended : $fallback;
    }

    private function canUsePath(User $user, string $path): bool
    {
        $role = $user->role ?? null;
        $isSuperadmin = (int) ($user->is_admin ?? 0) === 2 || $role === 'superadmin';
        $isAdmin = (int) ($user->is_admin ?? 0) === 1 || $role === 'admin';
        $isBranchAdmin = $role === 'branch_admin';

        if ($isSuperadmin) {
            return true;
        }

        if ($isAdmin) {
            return ! str_starts_with($path, '/superadmin') && ! str_starts_with($path, '/cashier');
        }

        if ($isBranchAdmin) {
            return str_starts_with($path, '/pos-admin')
                || str_starts_with($path, '/profile')
                || str_starts_with($path, '/notifications')
                || str_starts_with($path, '/branches/switch');
        }

        if ($role === 'cashier') {
            return str_starts_with($path, '/cashier')
                || str_starts_with($path, '/profile')
                || str_starts_with($path, '/notifications');
        }

        foreach (['/admin', '/pos-admin', '/superadmin', '/cashier', '/dashboard', '/homeslides', '/categories', '/product/Add', '/product/display', '/product/update', '/delete', '/edit', '/hero'] as $adminPath) {
            if (str_starts_with($path, $adminPath)) {
                return false;
            }
        }

        return true;
    }
}

