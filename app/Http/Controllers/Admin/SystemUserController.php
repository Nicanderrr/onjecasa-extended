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

class SystemUserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->select('id', 'name', 'email', 'role', 'is_admin', 'is_active', 'email_verified_at', 'created_at')
            ->orderByDesc('id')
            ->paginate(20);

        $metrics = [
            'total' => User::count(),
            'admins' => User::where('role', 'admin')->orWhereIn('is_admin', [1, 2])->count(),
            'cashiers' => User::where('role', 'cashier')->count(),
            'customers' => User::where('role', 'customer')->count(),
        ];

        return view('pos_admin.users.index', compact('users', 'metrics'));
    }

    public function create(): View
    {
        return view('pos_admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email', 'unique:pos_staff,email'],
            'role' => ['required', 'in:customer,cashier,admin,branch_admin'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'staff_number' => ['nullable', 'string', 'max:100', 'unique:pos_staff,number'],
            'is_active' => ['nullable', 'boolean'],
            'email_verified' => ['nullable', 'boolean'],
        ]);

        [$userId, $staffId] = DB::transaction(function () use ($request, $data) {
            $user = new User();
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = Hash::make($data['password']);
            $user->role = $data['role'];
            $user->is_admin = $data['role'] === 'admin' ? 1 : 0;
            $user->is_active = $request->boolean('is_active');
            $user->email_verified_at = $request->boolean('email_verified') ? now() : null;
            $user->save();

            $staffId = null;
            if ($data['role'] === 'cashier') {
                $staffNumber = $data['staff_number'] ?: 'STAFF-' . strtoupper(Str::random(6));
                $staffId = DB::table('pos_staff')->insertGetId([
                    'branch_id' => BranchContext::activeId(),
                    'user_id' => $user->id,
                    'name' => $data['name'],
                    'number' => $staffNumber,
                    'email' => $data['email'],
                    'pincode' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (in_array($data['role'], ['cashier', 'admin', 'branch_admin'], true)) {
                BranchContext::syncUserBranches($user, [BranchContext::activeId()]);
            }

            return [$user->id, $staffId];
        });

        AuditTrail::record('system_user_created', 'Created system user ' . $data['email'], [
            'auditable_type' => 'user',
            'auditable_id' => $userId,
            'properties' => [
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'],
                'staff_id' => $staffId,
                'is_active' => $request->boolean('is_active'),
                'email_verified' => $request->boolean('email_verified'),
            ],
        ]);

        return redirect()->route('pos.admin.users.index')->with('success', 'User account created');
    }

    public function edit(User $user): View
    {
        return view('pos_admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'in:customer,cashier,admin,branch_admin'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
            'email_verified' => ['nullable', 'boolean'],
        ]);

        $before = $user->only(['name', 'email', 'role', 'is_admin', 'is_active', 'email_verified_at']);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        $user->is_admin = $data['role'] === 'admin' ? 1 : 0;
        $user->is_active = $request->boolean('is_active');
        $user->email_verified_at = $request->boolean('email_verified') ? ($user->email_verified_at ?: now()) : null;
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        DB::table('pos_staff')->where('user_id', $user->id)->update([
            'name' => $user->name,
            'email' => $user->email,
            'branch_id' => BranchContext::activeId(),
            'updated_at' => now(),
        ]);

        if (in_array($data['role'], ['cashier', 'admin', 'branch_admin'], true)) {
            BranchContext::syncUserBranches($user, [BranchContext::activeId()]);
        }

        AuditTrail::record('system_user_updated', 'Updated system user ' . $user->email, [
            'auditable_type' => 'user',
            'auditable_id' => $user->id,
            'properties' => [
                'before' => $before,
                'after' => $user->only(['name', 'email', 'role', 'is_admin', 'is_active', 'email_verified_at']),
                'password_changed' => ! empty($data['password']),
            ],
        ]);

        return redirect()->route('pos.admin.users.index')->with('success', 'User account updated');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->forceFill(['is_active' => false])->save();

        AuditTrail::record('system_user_deactivated', 'Deactivated system user ' . $user->email, [
            'auditable_type' => 'user',
            'auditable_id' => $user->id,
            'properties' => ['email' => $user->email],
        ]);

        return redirect()->route('pos.admin.users.index')->with('success', 'User account deactivated');
    }
}
