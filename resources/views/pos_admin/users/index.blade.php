@extends('layouts.pos-admin')

@section('title', 'System Users - ONJECASA POS')
@section('page-eyebrow', 'Access')
@section('page-title', 'System Users')
@section('page-description', 'Create and manage customer, cashier, and admin login accounts.')
@section('page-actions')
  <a href="{{ route('pos.admin.users.create') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-person-plus"></i> Add User
  </a>
@endsection

@section('content')
<div class="entity-page">
  <section class="row g-2 dashboard-metrics entity-metrics" aria-label="User metrics">
    <div class="col-6 col-lg-3">
      <article class="metric-card metric-primary">
        <div class="metric-top"><span class="metric-label">Users</span><span class="metric-icon"><i class="bi bi-people"></i></span></div>
        <div class="metric-value">{{ number_format($metrics['total']) }}</div>
        <div class="metric-meta"><span>All accounts</span></div>
      </article>
    </div>
    <div class="col-6 col-lg-3">
      <article class="metric-card metric-warning">
        <div class="metric-top"><span class="metric-label">Admins</span><span class="metric-icon"><i class="bi bi-shield-check"></i></span></div>
        <div class="metric-value">{{ number_format($metrics['admins']) }}</div>
        <div class="metric-meta"><span>Back office</span></div>
      </article>
    </div>
    <div class="col-6 col-lg-3">
      <article class="metric-card metric-success">
        <div class="metric-top"><span class="metric-label">Cashiers</span><span class="metric-icon"><i class="bi bi-person-badge"></i></span></div>
        <div class="metric-value">{{ number_format($metrics['cashiers']) }}</div>
        <div class="metric-meta"><span>POS access</span></div>
      </article>
    </div>
    <div class="col-6 col-lg-3">
      <article class="metric-card metric-danger">
        <div class="metric-top"><span class="metric-label">Customers</span><span class="metric-icon"><i class="bi bi-bag-check"></i></span></div>
        <div class="metric-value">{{ number_format($metrics['customers']) }}</div>
        <div class="metric-meta"><span>Website access</span></div>
      </article>
    </div>
  </section>

  <div class="card shadow entity-card">
    <div class="card-header border-0 entity-toolbar">
      <div class="cashier-table-heading">
        <span><i class="bi bi-person-lines-fill"></i></span>
        <div><strong>User directory</strong><small>Login accounts, roles, and access status</small></div>
      </div>
      <div class="entity-filter-wrap">
        <i class="bi bi-search"></i>
        <input type="search" class="form-control form-control-sm entity-filter" placeholder="Search users" aria-label="Filter users" data-table-filter="#users-table">
      </div>
    </div>

    <div class="table-responsive">
      <table class="table align-items-center table-flush" id="users-table">
        <thead class="thead-light">
          <tr><th>User</th><th>Role</th><th>Status</th><th>Email</th><th>Joined</th><th>Actions</th></tr>
        </thead>
        <tbody>
          @forelse($users as $user)
            <tr data-filter-row>
              <td>
                <div class="cashier-identity">
                  <span>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}</span>
                  <div><strong>{{ $user->name }}</strong><small>ID {{ $user->id }}</small></div>
                </div>
              </td>
              <td><span class="shop-chip">{{ ucfirst($user->role ?? 'customer') }}</span></td>
              <td>
                <span class="cashier-access {{ $user->is_active ? 'active' : 'inactive' }}">
                  <span></span>{{ $user->is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td><strong>{{ $user->email }}</strong></td>
              <td>{{ $user->created_at ? $user->created_at->format('d M Y') : 'Unknown' }}</td>
              <td>
                <div class="action-group cashier-actions">
                  <a href="{{ route('pos.admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="Edit user"><i class="bi bi-pencil-square"></i></a>
                  @if($user->id !== auth()->id() && $user->is_active)
                    <form method="POST" action="{{ route('pos.admin.users.destroy', $user) }}">
                      @csrf @method('DELETE')
                      <button class="btn btn-sm btn-outline-danger" title="Deactivate user"><i class="bi bi-person-slash"></i></button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr data-filter-empty><td colspan="6" class="text-center py-5 text-muted">No users found.</td></tr>
          @endforelse
          @if($users->count())
            <tr data-filter-empty style="display:none;"><td colspan="6" class="text-center py-5 text-muted">No matching users found.</td></tr>
          @endif
        </tbody>
      </table>
    </div>
    @if($users->hasPages())
      <div class="card-footer border-0">{{ $users->links() }}</div>
    @endif
  </div>
</div>
@endsection

