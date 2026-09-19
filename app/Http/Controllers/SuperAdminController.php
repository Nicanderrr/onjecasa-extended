<?php

namespace App\Http\Controllers;

use App\Models\OrderProduct;
use App\Models\Branch;
use App\Models\ProductPage;
use App\Models\SuperAdminAuditLog;
use App\Models\User;
use App\Support\BranchContext;
use App\Support\BranchProductSync;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SuperAdminController extends Controller
{
    public function dashboard(Request $request): View
    {
        [$dateFrom, $dateTo, $dateFilters] = $this->dateRange($request);
        $branchId = BranchContext::activeId();

        $userQuery = User::query()
            ->when($branchId, function ($query) use ($branchId) {
                $query->join('branch_user', 'branch_user.user_id', '=', 'users.id')
                    ->where('branch_user.branch_id', $branchId);
            });
        $websiteCatalogQuery = ProductPage::query()
            ->when($branchId, function ($query) use ($branchId) {
                $query->whereExists(function ($subquery) use ($branchId) {
                    $subquery->selectRaw('1')
                        ->from('pos_products')
                        ->whereColumn('pos_products.website_product_id', 'product_pages.id')
                        ->where('pos_products.branch_id', $branchId);
                });
            });
        $posProductQuery = DB::table('pos_products')->when($branchId, fn ($query) => $query->where('branch_id', $branchId));
        $onlineOrderQuery = OrderProduct::query()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->whereBetween('created_at', [$dateFrom, $dateTo]);
        $posOrderQuery = DB::table('pos_orders')
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->whereBetween('created_at', [$dateFrom, $dateTo]);
        $posPaymentQuery = DB::table('pos_payments')
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_payments.order_id')
            ->when($branchId, fn ($query) => $query->where('pos_orders.branch_id', $branchId))
            ->whereBetween('pos_payments.created_at', [$dateFrom, $dateTo]);

        $metrics = [
            [
                'label' => 'System Users',
                'value' => (clone $userQuery)->count(),
                'hint' => User::where('is_admin', 2)->orWhere('role', 'superadmin')->count().' superadmin(s)',
                'icon' => 'bi-people',
                'tone' => 'metric-primary',
                'route' => route('superadmin.users'),
            ],
            [
                'label' => 'Website Catalog',
                'value' => (clone $websiteCatalogQuery)->count(),
                'hint' => number_format((int) (clone $websiteCatalogQuery)->sum('stock')).' online stock units',
                'icon' => 'bi-globe2',
                'tone' => 'metric-warning',
                'route' => route('view_product'),
            ],
            [
                'label' => 'POS Catalog',
                'value' => (clone $posProductQuery)->count(),
                'hint' => number_format((int) (clone $posProductQuery)->sum('stock')).' in-store stock units',
                'icon' => 'bi-shop',
                'tone' => 'metric-success',
                'route' => route('pos.admin.products.index'),
            ],
            [
                'label' => 'Online Orders',
                'value' => (clone $onlineOrderQuery)->count(),
                'hint' => 'GHS '.number_format((float) (clone $onlineOrderQuery)->sum('total'), 2).' order value',
                'icon' => 'bi-cart-check',
                'tone' => 'metric-danger',
                'route' => route('all_orders'),
            ],
            [
                'label' => 'POS Sales',
                'value' => (clone $posOrderQuery)->count(),
                'hint' => 'GHS '.number_format((float) (clone $posPaymentQuery)->sum('pos_payments.amount'), 2).' collected',
                'icon' => 'bi-receipt',
                'tone' => 'metric-primary',
                'route' => route('pos.admin.orders.index'),
            ],
        ];

        $recentLogs = SuperAdminAuditLog::with('user')->whereBetween('created_at', [$dateFrom, $dateTo])->latest()->take(8)->get();
        $posAudit = DB::table('pos_audit_trails')
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderByDesc('id')
            ->limit(8)
            ->get();
        $settings = $this->settings();
        $quickLinks = $this->quickLinks();
        $dailyActivity = $this->dailyActivity($dateFrom, $dateTo, $branchId);

        return view('superadmin.dashboard', compact('metrics', 'recentLogs', 'posAudit', 'settings', 'quickLinks', 'dailyActivity', 'dateFilters'));
    }

    public function branches(): View
    {
        $branches = Branch::withCount('users')->orderByDesc('id')->paginate(20);
        $metrics = [
            'total' => Branch::count(),
            'active' => Branch::where('is_active', true)->count(),
            'users' => DB::table('branch_user')->distinct('user_id')->count('user_id'),
            'pos_orders' => DB::table('pos_orders')->count(),
        ];

        return view('superadmin.branches', compact('branches', 'metrics'));
    }

    public function storeBranch(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:branches,code'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'base_delivery_fee' => ['nullable', 'numeric', 'min:0'],
            'delivery_fee_per_km' => ['nullable', 'numeric', 'min:0'],
            'max_delivery_distance_km' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $branch = Branch::create([
            'name' => $data['name'],
            'code' => strtoupper($data['code']),
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'base_delivery_fee' => $data['base_delivery_fee'] ?? 15,
            'delivery_fee_per_km' => $data['delivery_fee_per_km'] ?? 3,
            'max_delivery_distance_km' => $data['max_delivery_distance_km'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->audit($request, 'created_branch', $branch, $branch->only(['name', 'code', 'phone', 'address', 'is_active']));

        if ($branch->is_active) {
            BranchProductSync::syncBranchFromCatalog((int) $branch->id);
        }

        return back()->with('success', 'Branch created.');
    }

    public function updateBranch(Request $request, Branch $branch): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('branches', 'code')->ignore($branch->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'base_delivery_fee' => ['nullable', 'numeric', 'min:0'],
            'delivery_fee_per_km' => ['nullable', 'numeric', 'min:0'],
            'max_delivery_distance_km' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $before = $branch->only(['name', 'code', 'phone', 'address', 'latitude', 'longitude', 'base_delivery_fee', 'delivery_fee_per_km', 'max_delivery_distance_km', 'is_active']);
        $branch->update([
            'name' => $data['name'],
            'code' => strtoupper($data['code']),
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'base_delivery_fee' => $data['base_delivery_fee'] ?? 0,
            'delivery_fee_per_km' => $data['delivery_fee_per_km'] ?? 0,
            'max_delivery_distance_km' => $data['max_delivery_distance_km'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->audit($request, 'updated_branch', $branch, [
            'before' => $before,
            'after' => $branch->only(['name', 'code', 'phone', 'address', 'latitude', 'longitude', 'base_delivery_fee', 'delivery_fee_per_km', 'max_delivery_distance_km', 'is_active']),
        ]);

        return back()->with('success', 'Branch updated.');
    }

    public function users(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $users = User::query()
            ->select('id', 'name', 'email', 'role', 'is_admin', 'is_active', 'email_verified_at', 'created_at')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $metrics = [
            'total' => User::count(),
            'superadmins' => User::where('is_admin', 2)->orWhere('role', 'superadmin')->count(),
            'admins' => User::where('role', 'admin')->orWhere('is_admin', 1)->count(),
            'cashiers' => User::where('role', 'cashier')->count(),
            'customers' => User::where('role', 'customer')->count(),
        ];

        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('superadmin.users', compact('users', 'metrics', 'search', 'branches'));
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:customer,cashier,admin,branch_admin,superadmin'],
            'password' => ['required', 'string', 'min:6'],
            'branch_ids' => ['nullable', 'array'],
            'branch_ids.*' => ['integer', 'exists:branches,id'],
            'is_active' => ['nullable', 'boolean'],
            'email_verified' => ['nullable', 'boolean'],
        ]);

        [$user, $staffId] = DB::transaction(function () use ($request, $data) {
            $user = new User();
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = Hash::make($data['password']);
            $user->role = $data['role'];
            $user->is_admin = $data['role'] === 'superadmin' ? 2 : ($data['role'] === 'admin' ? 1 : 0);
            $user->is_active = $request->boolean('is_active', true);
            $user->email_verified_at = $request->boolean('email_verified', true) ? now() : null;
            $user->save();

            $staffId = null;
            if ($data['role'] === 'cashier') {
                $branchId = collect($request->input('branch_ids', []))->map(fn ($id) => (int) $id)->filter()->first()
                    ?: BranchContext::defaultBranchId();

                $staffId = DB::table('pos_staff')->insertGetId([
                    'branch_id' => $branchId,
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'number' => 'STAFF-' . strtoupper(Str::random(6)),
                    'email' => $user->email,
                    'pincode' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return [$user, $staffId];
        });

        BranchContext::syncUserBranches($user, $request->input('branch_ids', []));

        $this->audit($request, 'created_user', $user, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_admin' => $user->is_admin,
            'staff_id' => $staffId,
            'branch_ids' => $request->input('branch_ids', []),
        ]);

        return redirect()->route('superadmin.users')->with('success', 'User account created.');
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'in:customer,cashier,admin,branch_admin,superadmin'],
            'password' => ['nullable', 'string', 'min:6'],
            'branch_ids' => ['nullable', 'array'],
            'branch_ids.*' => ['integer', 'exists:branches,id'],
            'is_active' => ['nullable', 'boolean'],
            'email_verified' => ['nullable', 'boolean'],
        ]);

        if ($user->id === $request->user()->id && $data['role'] !== 'superadmin') {
            return back()->with('error', 'You cannot remove your own superadmin access.');
        }

        $before = $user->only(['name', 'email', 'role', 'is_admin', 'is_active', 'email_verified_at']);
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        $user->is_admin = $data['role'] === 'superadmin' ? 2 : ($data['role'] === 'admin' ? 1 : 0);
        $user->is_active = $request->boolean('is_active');
        $user->email_verified_at = $request->boolean('email_verified') ? ($user->email_verified_at ?: now()) : null;
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();
        BranchContext::syncUserBranches($user, $request->input('branch_ids', []));

        if ($data['role'] === 'cashier') {
            $staff = DB::table('pos_staff')->where('user_id', $user->id)->first();

            if ($staff) {
                DB::table('pos_staff')->where('id', $staff->id)->update([
                    'name' => $user->name,
                    'email' => $user->email,
                    'branch_id' => collect($request->input('branch_ids', []))->map(fn ($id) => (int) $id)->filter()->first() ?: $staff->branch_id,
                    'updated_at' => now(),
                ]);
            } else {
                $branchId = collect($request->input('branch_ids', []))->map(fn ($id) => (int) $id)->filter()->first()
                    ?: BranchContext::defaultBranchId();

                DB::table('pos_staff')->insert([
                    'branch_id' => $branchId,
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'number' => 'STAFF-' . strtoupper(Str::random(6)),
                    'email' => $user->email,
                    'pincode' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } else {
            DB::table('pos_staff')->where('user_id', $user->id)->update([
                'name' => $user->name,
                'email' => $user->email,
                'updated_at' => now(),
            ]);
        }

        $this->audit($request, 'updated_user', $user, [
            'before' => $before,
            'after' => $user->only(['name', 'email', 'role', 'is_admin', 'is_active', 'email_verified_at']),
            'branch_ids' => $request->input('branch_ids', []),
            'password_changed' => ! empty($data['password']),
        ]);

        return back()->with('success', 'User account updated.');
    }

    public function destroyUser(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $snapshot = $user->only(['id', 'name', 'email', 'role', 'is_admin']);
        $user->delete();
        $this->audit($request, 'deleted_user', null, ['deleted_user' => $snapshot]);

        return back()->with('success', 'User account deleted.');
    }

    public function auditLogs(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $source = $request->query('source', 'superadmin');

        if ($source === 'pos') {
            $logs = DB::table('pos_audit_trails')
                ->when($search !== '', function ($query) use ($search) {
                    $query->where('event', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('user_name', 'like', "%{$search}%")
                        ->orWhere('user_role', 'like', "%{$search}%");
                })
                ->orderByDesc('id')
                ->paginate(50)
                ->withQueryString();
        } else {
            $logs = SuperAdminAuditLog::with('user')
                ->when($search !== '', function ($query) use ($search) {
                    $query->where('action', 'like', "%{$search}%")
                        ->orWhere('subject_type', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                })
                ->latest()
                ->paginate(50)
                ->withQueryString();
        }

        $summary = [
            'superadmin' => SuperAdminAuditLog::count(),
            'pos' => DB::table('pos_audit_trails')->count(),
            'today' => SuperAdminAuditLog::whereDate('created_at', today())->count() + DB::table('pos_audit_trails')->whereDate('created_at', today())->count(),
        ];

        return view('superadmin.audit', compact('logs', 'summary', 'search', 'source'));
    }

    public function security(): View
    {
        $settings = $this->settings();
        $adminUsers = User::whereIn('role', ['admin', 'superadmin'])
            ->orWhereIn('is_admin', [1, 2])
            ->orderByDesc('id')
            ->get();

        $sessions = DB::getSchemaBuilder()->hasTable('sessions')
            ? DB::table('sessions')->whereNotNull('user_id')->orderByDesc('last_activity')->take(50)->get()
            : collect();

        return view('superadmin.security', compact('settings', 'adminUsers', 'sessions'));
    }

    public function updateSecurity(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ai_enabled' => ['nullable', 'boolean'],
            'maintenance_enabled' => ['nullable', 'boolean'],
            'maintenance_note' => ['nullable', 'string', 'max:500'],
        ]);

        $settings = $this->settings();
        $settings['ai_enabled'] = $request->boolean('ai_enabled');
        $settings['maintenance_enabled'] = $request->boolean('maintenance_enabled');
        $settings['maintenance_note'] = $data['maintenance_note'] ?? '';
        $this->setSettings($settings);
        $this->audit($request, 'updated_security_settings', null, $settings);

        return back()->with('success', 'Security settings updated.');
    }

    public function settingsPage(): View
    {
        $settings = $this->settings();
        $posSettings = DB::table('pos_settings')->pluck('value', 'key');

        return view('superadmin.settings', compact('settings', 'posSettings'));
    }

    public function maintenance(): View
    {
        $settings = $this->settings();

        return view('superadmin.maintenance', compact('settings'));
    }

    public function clearCache(Request $request): RedirectResponse
    {
        Artisan::call('optimize:clear');
        $this->audit($request, 'cleared_application_cache');

        return back()->with('success', 'Application cache cleared.');
    }

    public function toggleMaintenance(Request $request): RedirectResponse
    {
        $settings = $this->settings();
        $settings['maintenance_enabled'] = $request->boolean('maintenance_enabled');
        $this->setSettings($settings);
        $this->audit($request, 'toggled_maintenance_flag', null, ['enabled' => $settings['maintenance_enabled']]);

        return back()->with('success', 'Maintenance flag updated.');
    }

    private function quickLinks(): array
    {
        return [
            ['label' => 'Website Dashboard', 'route' => route('admin.panel'), 'description' => 'Online storefront controls'],
            ['label' => 'Homepage Photos', 'route' => route('home_slide'), 'description' => 'Hero images, homepage headlines, and media'],
            ['label' => 'POS Dashboard', 'route' => route('pos.admin.dashboard'), 'description' => 'In-store sales controls'],
            ['label' => 'Branches', 'route' => route('superadmin.branches'), 'description' => 'Create stores and assign access'],
            ['label' => 'Users & Roles', 'route' => route('superadmin.users'), 'description' => 'Create, reset, promote, or remove accounts'],
            ['label' => 'Audit Logs', 'route' => route('superadmin.audit'), 'description' => 'Review superadmin and POS activity'],
            ['label' => 'Security Center', 'route' => route('superadmin.security'), 'description' => 'AI and maintenance policy'],
            ['label' => 'Global Logo', 'route' => route('pos.admin.settings.index'), 'description' => 'Logo, favicon, and POS settings'],
        ];
    }

    private function dateRange(Request $request): array
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $dateFrom = ! empty($filters['date_from'])
            ? Carbon::parse($filters['date_from'])->startOfDay()
            : now()->startOfDay()->subDays(6);
        $dateTo = ! empty($filters['date_to'])
            ? Carbon::parse($filters['date_to'])->endOfDay()
            : now()->endOfDay();

        if ($dateFrom->gt($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo->copy()->startOfDay(), $dateFrom->copy()->endOfDay()];
        }

        return [$dateFrom, $dateTo, [
            'date_from' => $dateFrom->toDateString(),
            'date_to' => $dateTo->toDateString(),
        ]];
    }

    private function dailyActivity(Carbon $dateFrom, Carbon $dateTo, ?int $branchId)
    {
        $start = $dateFrom->copy()->startOfDay();
        $end = $dateTo->copy()->endOfDay();
        $days = (int) max(0, min(60, $start->diffInDays($end)));
        $superadmin = SuperAdminAuditLog::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('day')
            ->pluck('total', 'day');
        $pos = DB::table('pos_audit_trails')
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('day')
            ->pluck('total', 'day');

        return collect(range(0, $days))->map(function (int $offset) use ($start, $superadmin, $pos) {
            $date = $start->copy()->addDays($offset);
            $key = $date->toDateString();

            return [
                'label' => $date->format('D'),
                'total' => (int) ($superadmin[$key] ?? 0) + (int) ($pos[$key] ?? 0),
            ];
        });
    }

    private function settings(): array
    {
        $raw = DB::table('pos_settings')->where('key', 'superadmin_security')->value('value');
        $settings = $raw ? json_decode($raw, true) : null;

        return is_array($settings) ? array_merge($this->defaultSettings(), $settings) : $this->defaultSettings();
    }

    private function setSettings(array $settings): void
    {
        DB::table('pos_settings')->updateOrInsert(
            ['key' => 'superadmin_security'],
            ['value' => json_encode(array_merge($this->defaultSettings(), $settings)), 'updated_at' => now(), 'created_at' => now()]
        );
    }

    private function defaultSettings(): array
    {
        return [
            'ai_enabled' => true,
            'maintenance_enabled' => false,
            'maintenance_note' => '',
        ];
    }

    private function audit(Request $request, string $action, mixed $subject = null, array $properties = []): void
    {
        SuperAdminAuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => $action,
            'subject_type' => is_object($subject) ? $subject::class : null,
            'subject_id' => is_object($subject) ? $subject->id : null,
            'properties' => $properties ?: null,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);
    }
}
