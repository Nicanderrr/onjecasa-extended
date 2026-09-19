@extends('layouts.pos-admin')

@section('title', 'Edit User - ONJECASA POS')
@section('page-eyebrow', 'Access')
@section('page-title', 'Edit User')
@section('page-description', 'Update account role, status, email verification, or issue a new password.')
@section('page-actions')
  <a href="{{ route('pos.admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> Back to Users
  </a>
@endsection

@section('content')
<div class="card shadow entity-card cashier-form-card">
  <div class="card-header border-0">
    <div class="cashier-form-heading">
      <span><i class="bi bi-person-gear"></i></span>
      <div>
        <h3 class="mb-1">{{ $user->name }}</h3>
        <p class="mb-0">{{ $user->email }}</p>
      </div>
    </div>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('pos.admin.users.update', $user) }}" class="compact-form">
      @csrf
      @method('PUT')
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Full Name</label>
          <input name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
          @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <label class="form-label">Login Email</label>
          <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required autocomplete="username">
          @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <label class="form-label">Role</label>
          <select name="role" class="form-select" required>
            <option value="customer" @selected(old('role', $user->role) === 'customer')>Customer - website shopping</option>
            <option value="cashier" @selected(old('role', $user->role) === 'cashier')>Cashier - POS sales</option>
            <option value="branch_admin" @selected(old('role', $user->role) === 'branch_admin')>Branch Admin - selected store admin</option>
            <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin - full admin access</option>
          </select>
          @error('role')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <label class="form-label">New Password</label>
          <input type="password" name="password" class="form-control" minlength="6" autocomplete="new-password" placeholder="Leave blank to keep current password">
          @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <label class="form-label">Confirm New Password</label>
          <input type="password" name="password_confirmation" class="form-control" minlength="6" autocomplete="new-password">
        </div>
        <div class="col-md-3">
          <input type="hidden" name="is_active" value="0">
          <label class="cashier-active-toggle">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active ? '1' : '0') === '1' ? 'checked' : '' }}>
            <span><strong>Active</strong><small>Can sign in.</small></span>
          </label>
        </div>
        <div class="col-md-3">
          <input type="hidden" name="email_verified" value="0">
          <label class="cashier-active-toggle">
            <input type="checkbox" name="email_verified" value="1" {{ old('email_verified', $user->email_verified_at ? '1' : '0') === '1' ? 'checked' : '' }}>
            <span><strong>Verified</strong><small>Email trusted.</small></span>
          </label>
        </div>
      </div>
      <div class="d-flex flex-wrap gap-2 mt-2">
        <button class="btn btn-primary"><i class="bi bi-check2-circle"></i> Save User</button>
        <a href="{{ route('pos.admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
@endsection

