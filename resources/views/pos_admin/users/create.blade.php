@extends('layouts.pos-admin')

@section('title', 'Add User - ONJECASA POS')
@section('page-eyebrow', 'Access')
@section('page-title', 'Add User')
@section('page-description', 'Create a customer, cashier, or admin account from the POS admin panel.')
@section('page-actions')
  <a href="{{ route('pos.admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> Back to Users
  </a>
@endsection

@section('content')
<div class="card shadow entity-card cashier-form-card">
  <div class="card-header border-0">
    <div class="cashier-form-heading">
      <span><i class="bi bi-person-plus"></i></span>
      <div>
        <h3 class="mb-1">User account</h3>
        <p class="mb-0">Assign the correct role so the user lands in the right part of the system after login.</p>
      </div>
    </div>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('pos.admin.users.store') }}" class="compact-form">
      @csrf
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Full Name</label>
          <input name="name" value="{{ old('name') }}" class="form-control" required>
          @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <label class="form-label">Login Email</label>
          <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autocomplete="username">
          @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <label class="form-label">Role</label>
          <select name="role" id="userRole" class="form-select" required>
            <option value="customer" @selected(old('role') === 'customer')>Customer - website shopping</option>
            <option value="cashier" @selected(old('role') === 'cashier')>Cashier - POS sales</option>
            <option value="branch_admin" @selected(old('role') === 'branch_admin')>Branch Admin - selected store admin</option>
            <option value="admin" @selected(old('role') === 'admin')>Admin - full admin access</option>
          </select>
          @error('role')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6" id="staffNumberWrap">
          <label class="form-label">Cashier Staff Number</label>
          <input name="staff_number" value="{{ old('staff_number') }}" class="form-control" placeholder="Leave empty to auto-generate">
          <div class="form-hint">Used only when the role is Cashier.</div>
          @error('staff_number')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required minlength="6" autocomplete="new-password">
          @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <label class="form-label">Confirm Password</label>
          <input type="password" name="password_confirmation" class="form-control" required minlength="6" autocomplete="new-password">
        </div>
        <div class="col-md-6">
          <input type="hidden" name="is_active" value="0">
          <label class="cashier-active-toggle">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') === '1' ? 'checked' : '' }}>
            <span><strong>Active account</strong><small>Allow this user to sign in immediately.</small></span>
          </label>
        </div>
        <div class="col-md-6">
          <input type="hidden" name="email_verified" value="0">
          <label class="cashier-active-toggle">
            <input type="checkbox" name="email_verified" value="1" {{ old('email_verified', '1') === '1' ? 'checked' : '' }}>
            <span><strong>Email verified</strong><small>Skip email verification for admin-created users.</small></span>
          </label>
        </div>
      </div>
      <div class="d-flex flex-wrap gap-2 mt-2">
        <button class="btn btn-primary"><i class="bi bi-person-plus"></i> Create User</button>
        <a href="{{ route('pos.admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const role = document.getElementById('userRole');
    const staffNumberWrap = document.getElementById('staffNumberWrap');
    const syncStaffField = () => {
      if (!role || !staffNumberWrap) return;
      staffNumberWrap.style.display = role.value === 'cashier' ? '' : 'none';
    };
    role?.addEventListener('change', syncStaffField);
    syncStaffField();
  });
</script>
@endpush

