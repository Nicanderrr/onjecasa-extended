@extends('layouts.pos-admin')

@section('title', 'Superadmin - Security Center')
@section('page-eyebrow', 'Security')
@section('page-title', 'Security Center')
@section('page-description', 'Control system-wide admin AI access and maintenance policy.')

@section('content')
<div class="entity-page">
  <section class="row g-3">
    <div class="col-12 col-xl-5">
      <div class="card shadow entity-card h-100">
        <div class="card-header border-0 entity-toolbar">
          <div class="cashier-table-heading"><span><i class="bi bi-shield-lock"></i></span><div><strong>Policy switches</strong><small>Applies to admin control surfaces</small></div></div>
        </div>
        <form method="POST" action="{{ route('superadmin.security.update') }}" class="card-body">
          @csrf @method('PUT')
          <div class="rounded-4 border p-3 mb-3">
            <label class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" name="ai_enabled" value="1" @checked($settings['ai_enabled'] ?? true)>
              <span class="form-check-label fw-bold">Enable POS AI assistant</span>
            </label>
          </div>
          <div class="rounded-4 border p-3 mb-3">
            <label class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" name="maintenance_enabled" value="1" @checked($settings['maintenance_enabled'] ?? false)>
              <span class="form-check-label fw-bold">Maintenance flag</span>
            </label>
          </div>
          <div class="mb-4">
            <label class="form-label">Maintenance note</label>
            <textarea name="maintenance_note" rows="4" class="form-control">{{ old('maintenance_note', $settings['maintenance_note'] ?? '') }}</textarea>
          </div>
          <button class="btn btn-primary"><i class="bi bi-save"></i> Save Security Policy</button>
        </form>
      </div>
    </div>

    <div class="col-12 col-xl-7">
      <div class="card shadow entity-card">
        <div class="card-header border-0 entity-toolbar">
          <div class="cashier-table-heading"><span><i class="bi bi-person-badge"></i></span><div><strong>Privileged users</strong><small>Admin and superadmin accounts</small></div></div>
        </div>
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead><tr><th>User</th><th>Role</th><th>Status</th><th>Joined</th></tr></thead>
            <tbody>
              @forelse($adminUsers as $user)
                <tr>
                  <td><strong>{{ $user->name }}</strong><small class="d-block text-muted">{{ $user->email }}</small></td>
                  <td><span class="shop-chip">{{ (int) $user->is_admin === 2 ? 'Superadmin' : ucfirst($user->role ?? 'admin') }}</span></td>
                  <td><span class="cashier-access {{ $user->is_active ? 'active' : 'inactive' }}"><span></span>{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td>
                  <td>{{ $user->created_at?->format('d M Y') }}</td>
                </tr>
              @empty
                <tr><td colspan="4" class="text-center py-5 text-muted">No privileged users found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</div>
@endsection
