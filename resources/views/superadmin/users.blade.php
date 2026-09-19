@extends('layouts.pos-admin')

@section('title', 'Superadmin - Users & Roles')
@section('page-eyebrow', 'Access Control')
@section('page-title', 'Users & Roles')
@section('page-description', 'Create accounts, assign customer/cashier/admin/superadmin roles, reset passwords, and remove users.')

@section('content')
<div class="entity-page">
  <section class="row g-2 dashboard-metrics entity-metrics">
    @foreach(['total' => 'Users', 'superadmins' => 'Superadmins', 'admins' => 'Admins', 'cashiers' => 'Cashiers', 'customers' => 'Customers'] as $key => $label)
      <div class="col-6 col-xl">
        <article class="metric-card {{ $key === 'superadmins' ? 'metric-warning' : 'metric-primary' }}">
          <div class="metric-top"><span class="metric-label">{{ $label }}</span><span class="metric-icon"><i class="bi bi-people"></i></span></div>
          <div class="metric-value">{{ number_format($metrics[$key]) }}</div>
          <div class="metric-meta"><span>Account scope</span></div>
        </article>
      </div>
    @endforeach
  </section>

  <section class="row g-3 mt-1">
    <div class="col-12 col-xl-4">
      <div class="card shadow entity-card h-100">
        <div class="card-header border-0 entity-toolbar">
          <div class="cashier-table-heading">
            <span><i class="bi bi-person-plus"></i></span>
            <div><strong>Add user</strong><small>Create direct access credentials</small></div>
          </div>
        </div>
        <form method="POST" action="{{ route('superadmin.users.store') }}" class="card-body">
          @csrf
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input name="name" value="{{ old('name') }}" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select" required>
              @foreach(['customer' => 'Customer', 'cashier' => 'Cashier', 'branch_admin' => 'Branch Admin', 'admin' => 'Admin', 'superadmin' => 'Superadmin'] as $value => $label)
                <option value="{{ $value }}" @selected(old('role', 'customer') === $value)>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Branch Access</label>
            <select name="branch_ids[]" class="form-select" multiple size="4">
              @foreach($branches as $branch)
                <option value="{{ $branch->id }}" @selected(collect(old('branch_ids', []))->contains($branch->id))>{{ $branch->name }}</option>
              @endforeach
            </select>
            <small class="text-muted">Superadmins can access every branch even if none is selected.</small>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="text" name="password" class="form-control" required minlength="6" placeholder="Temporary password">
          </div>
          <div class="d-flex flex-wrap gap-3 mb-4">
            <label class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked> <span class="form-check-label">Active</span></label>
            <label class="form-check"><input class="form-check-input" type="checkbox" name="email_verified" value="1" checked> <span class="form-check-label">Email verified</span></label>
          </div>
          <button class="btn btn-primary w-100"><i class="bi bi-save"></i> Create User</button>
        </form>
      </div>
    </div>

    <div class="col-12 col-xl-8">
      <div class="card shadow entity-card">
        <div class="card-header border-0 entity-toolbar">
          <div class="cashier-table-heading">
            <span><i class="bi bi-person-lines-fill"></i></span>
            <div><strong>User directory</strong><small>Role and password management</small></div>
          </div>
          <form method="GET" action="{{ route('superadmin.users') }}" class="entity-filter-wrap">
            <i class="bi bi-search"></i>
            <input type="search" name="q" value="{{ $search }}" class="form-control form-control-sm entity-filter" placeholder="Search users">
          </form>
        </div>
        <div class="table-responsive">
          <table class="table align-items-center table-flush">
            <thead class="thead-light"><tr><th>User</th><th style="min-width:150px;">Role</th><th style="min-width:190px;">Branches</th><th>Status</th><th style="min-width:170px;">Password</th><th>Actions</th></tr></thead>
            <tbody>
              @forelse($users as $user)
                <tr>
                  <td>
                    <div class="cashier-identity">
                      <span>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}</span>
                      <div><strong>{{ $user->name }}</strong><small>{{ $user->email }}</small></div>
                    </div>
                  </td>
                  <form method="POST" action="{{ route('superadmin.users.update', $user) }}">
                    @csrf @method('PATCH')
                    <td>
                      <input type="hidden" name="name" value="{{ $user->name }}">
                      <input type="hidden" name="email" value="{{ $user->email }}">
                      <select name="role" class="form-select form-select-sm">
                        @foreach(['customer' => 'Customer', 'cashier' => 'Cashier', 'branch_admin' => 'Branch Admin', 'admin' => 'Admin', 'superadmin' => 'Superadmin'] as $value => $label)
                          <option value="{{ $value }}" @selected(($user->role ?? 'customer') === $value || ($value === 'superadmin' && (int) $user->is_admin === 2))>{{ $label }}</option>
                        @endforeach
                      </select>
                    </td>
                    <td>
                      @php $assignedBranches = $user->branches()->pluck('branches.id')->all(); @endphp
                      <select name="branch_ids[]" class="form-select form-select-sm" multiple size="3">
                        @foreach($branches as $branch)
                          <option value="{{ $branch->id }}" @selected(in_array($branch->id, $assignedBranches, true))>{{ $branch->name }}</option>
                        @endforeach
                      </select>
                    </td>
                    <td>
                      <div class="d-grid gap-1">
                        <label class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($user->is_active)> <span class="form-check-label">Active</span></label>
                        <label class="form-check"><input class="form-check-input" type="checkbox" name="email_verified" value="1" @checked($user->email_verified_at)> <span class="form-check-label">Verified</span></label>
                      </div>
                    </td>
                    <td><input type="text" name="password" class="form-control form-control-sm" placeholder="Leave unchanged"></td>
                    <td>
                      <div class="action-group cashier-actions">
                        <button class="btn btn-sm btn-outline-primary" title="Save changes"><i class="bi bi-check2"></i></button>
                  </form>
                        @if($user->id !== auth()->id())
                          <form method="POST" action="{{ route('superadmin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user account?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Delete user"><i class="bi bi-trash"></i></button>
                          </form>
                        @endif
                      </div>
                    </td>
                </tr>
              @empty
                <tr><td colspan="6" class="text-center py-5 text-muted">No users found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if($users->hasPages())
          <div class="card-footer border-0">{{ $users->links() }}</div>
        @endif
      </div>
    </div>
  </section>
</div>
@endsection
