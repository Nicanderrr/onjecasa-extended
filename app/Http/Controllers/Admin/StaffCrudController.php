<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\BranchContext;
use App\Support\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffCrudController extends Controller
{
    public function index(): View
    {
        $branchId = BranchContext::activeId();
        $performance = DB::table('pos_orders')
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->selectRaw('cashier_user_id, COUNT(*) as order_count, SUM(grand_total) as sales_total, MAX(created_at) as last_sale_at')
            ->groupBy('cashier_user_id');

        $rows = DB::table('pos_staff as staff')
            ->leftJoin('users as account', 'account.id', '=', 'staff.user_id')
            ->leftJoin('branches', 'branches.id', '=', 'staff.branch_id')
            ->leftJoinSub($performance, 'performance', fn ($join) => $join->on('performance.cashier_user_id', '=', 'account.id'))
            ->when($branchId, fn ($query) => $query->where('staff.branch_id', $branchId))
            ->select([
                'staff.*',
                'branches.name as branch_name',
                'account.username as account_username',
                'account.role as account_role',
                'account.is_active',
                'account.email_notifications_enabled',
                'performance.order_count',
                'performance.sales_total',
                'performance.last_sale_at',
            ])
            ->orderByDesc('staff.id')
            ->get();

        return view('pos_admin.staff.index', compact('rows'));
    }

    public function create(): View
    {
        $branches = BranchContext::availableBranches(auth()->user());
        $canChooseBranch = BranchContext::isAdmin(auth()->user());
        $activeBranch = BranchContext::active();

        return view('pos_admin.staff.create', compact('branches', 'canChooseBranch', 'activeBranch'));
    }

    public function store(Request $request): RedirectResponse
    {
        $canChooseBranch = BranchContext::isAdmin($request->user());
        $data = $request->validate([
            'branch_id' => [$canChooseBranch ? 'required' : 'nullable', 'integer', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:100', 'unique:pos_staff,number'],
            'username' => ['required', 'string', 'max:100', 'alpha_dash', 'unique:users,username', 'unique:pos_staff,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email', 'unique:pos_staff,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
            'email_notifications_enabled' => ['nullable', 'boolean'],
        ]);

        [$staffId, $userId] = DB::transaction(function () use ($data, $request) {
            $branchId = $this->cashierBranchId($request);
            if (! $branchId) {
                abort(422, 'Choose a branch before creating a cashier.');
            }
            $user = User::create([
                'name' => $data['name'],
                'username' => $this->normalizeUsername($data['username']),
                'email' => $data['email'] ?? null,
                'password' => Hash::make($data['password']),
                'role' => 'cashier',
                'is_active' => $request->boolean('is_active'),
                'email_notifications_enabled' => $request->boolean('email_notifications_enabled', true),
            ]);

            $staffId = DB::table('pos_staff')->insertGetId([
                'branch_id' => $branchId,
                'user_id' => $user->id,
                'name' => $data['name'],
                'number' => $data['number'],
                'username' => $this->normalizeUsername($data['username']),
                'email' => $data['email'] ?? null,
                'pincode' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            BranchContext::syncUserBranches($user, [$branchId]);

            return [$staffId, $user->id];
        });

        AuditTrail::record('cashier_created', 'Created cashier account ' . $data['name'], [
            'auditable_type' => 'user',
            'auditable_id' => $userId,
            'properties' => [
                'staff_id' => $staffId,
                'branch_id' => $this->cashierBranchId($request),
                'name' => $data['name'],
                'username' => $this->normalizeUsername($data['username']),
                'email' => $data['email'] ?? null,
                'number' => $data['number'],
                'is_active' => $request->boolean('is_active'),
                'email_notifications_enabled' => $request->boolean('email_notifications_enabled', true),
            ],
        ]);

        return redirect()->route('pos.admin.staff.index')->with('success', 'Cashier account created');
    }

    public function edit(int $id): View
    {
        $row = DB::table('pos_staff as staff')
            ->leftJoin('users as account', 'account.id', '=', 'staff.user_id')
            ->leftJoin('branches', 'branches.id', '=', 'staff.branch_id')
            ->select('staff.*', 'branches.name as branch_name', 'account.username as account_username', 'account.is_active', 'account.email_notifications_enabled')
            ->where('staff.id', $id)
            ->when(BranchContext::activeId(), fn ($query, $branchId) => $query->where('staff.branch_id', $branchId))
            ->first();

        abort_unless($row, 404);

        $branches = BranchContext::availableBranches(auth()->user());
        $canChooseBranch = BranchContext::isAdmin(auth()->user());
        $activeBranch = BranchContext::active();

        return view('pos_admin.staff.edit', compact('row', 'branches', 'canChooseBranch', 'activeBranch'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $staff = DB::table('pos_staff')
            ->where('id', $id)
            ->when(BranchContext::activeId(), fn ($query, $branchId) => $query->where('branch_id', $branchId))
            ->first();
        abort_unless($staff, 404);

        $canChooseBranch = BranchContext::isAdmin($request->user());
        $data = $request->validate([
            'branch_id' => [$canChooseBranch ? 'required' : 'nullable', 'integer', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:100', Rule::unique('pos_staff', 'number')->ignore($id)],
            'username' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('pos_staff', 'username')->ignore($id),
                Rule::unique('users', 'username')->ignore($staff->user_id),
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('pos_staff', 'email')->ignore($id),
                Rule::unique('users', 'email')->ignore($staff->user_id),
            ],
            'password' => [Rule::requiredIf(empty($staff->user_id)), 'nullable', 'string', 'min:6', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
            'email_notifications_enabled' => ['nullable', 'boolean'],
        ]);

        $userId = DB::transaction(function () use ($staff, $data, $request, $id) {
            $branchId = $this->cashierBranchId($request);
            if (! $branchId) {
                abort(422, 'Choose a branch before updating a cashier.');
            }

            $user = $staff->user_id ? User::find($staff->user_id) : null;

            if (! $user) {
                $user = new User();
                $user->role = 'cashier';
            }

            $user->name = $data['name'];
            $user->username = $this->normalizeUsername($data['username']);
            $user->email = $data['email'] ?? null;
            $user->is_active = $request->boolean('is_active');
            $user->email_notifications_enabled = $request->boolean('email_notifications_enabled', true);
            if (! empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
            $user->save();

            DB::table('pos_staff')->where('id', $id)->update([
                'user_id' => $user->id,
                'branch_id' => $branchId,
                'name' => $data['name'],
                'number' => $data['number'],
                'username' => $this->normalizeUsername($data['username']),
                'email' => $data['email'] ?? null,
                'updated_at' => now(),
            ]);

            BranchContext::syncUserBranches($user, [$branchId]);

            return $user->id;
        });

        AuditTrail::record('cashier_updated', 'Updated cashier account ' . $data['name'], [
            'auditable_type' => 'user',
            'auditable_id' => $userId,
            'properties' => [
                'staff_id' => $id,
                'branch_id' => $this->cashierBranchId($request),
                'name' => $data['name'],
                'username' => $this->normalizeUsername($data['username']),
                'email' => $data['email'] ?? null,
                'number' => $data['number'],
                'password_changed' => ! empty($data['password']),
                'is_active' => $request->boolean('is_active'),
                'email_notifications_enabled' => $request->boolean('email_notifications_enabled', true),
            ],
        ]);

        return redirect()->route('pos.admin.staff.index')->with('success', 'Cashier account updated');
    }

    public function destroy(int $id): RedirectResponse
    {
        $staff = DB::table('pos_staff')
            ->where('id', $id)
            ->when(BranchContext::activeId(), fn ($query, $branchId) => $query->where('branch_id', $branchId))
            ->first();
        abort_unless($staff, 404);

        if ($staff->user_id) {
            User::whereKey($staff->user_id)->update(['is_active' => false]);
            $message = 'Cashier account deactivated';
        } else {
            DB::table('pos_staff')->where('id', $id)->delete();
            $message = 'Legacy staff record deleted';
        }

        AuditTrail::record('cashier_deactivated', 'Deactivated cashier ' . $staff->name, [
            'auditable_type' => 'user',
            'auditable_id' => $staff->user_id,
            'properties' => ['staff_id' => $id],
        ]);

        return redirect()->route('pos.admin.staff.index')->with('success', $message);
    }

    private function normalizeUsername(string $username): string
    {
        return Str::lower(trim($username));
    }

    private function cashierBranchId(Request $request): ?int
    {
        $branchId = BranchContext::isAdmin($request->user())
            ? (int) $request->input('branch_id')
            : BranchContext::activeId();

        if (! $branchId || ! BranchContext::canAccess($request->user(), $branchId)) {
            return null;
        }

        return $branchId;
    }
}
