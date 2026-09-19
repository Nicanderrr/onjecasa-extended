@extends('layouts.pos-admin')

@section('title', 'Add Cashier - ONJECASA POS')
@section('page-eyebrow', 'Access')
@section('page-title', 'Add Cashier')
@section('page-description', 'Create a cashier profile and login account.')
@section('page-actions')
  <a href="{{ route('pos.admin.staff.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> Back to Cashiers
  </a>
@endsection

@section('content')
<div class="card shadow entity-card cashier-form-card">
  <div class="card-header border-0">
    <div class="cashier-form-heading">
      <span><i class="bi bi-person-badge"></i></span>
      <div>
        <h3 class="mb-1">Cashier account</h3>
        <p class="mb-0">These credentials allow the cashier to sign in and process sales.</p>
      </div>
    </div>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('pos.admin.staff.store') }}" class="compact-form">
      @csrf
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Full Name</label>
          <input name="name" value="{{ old('name') }}" class="form-control" required>
          @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <label class="form-label">Phone Number</label>
          <input name="number" value="{{ old('number') }}" class="form-control" required>
          @error('number')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        @if($canChooseBranch)
          <div class="col-md-6">
            <label class="form-label">Branch</label>
            <select name="branch_id" class="form-select" required>
              <option value="">Choose cashier branch</option>
              @foreach($branches as $branch)
                <option value="{{ $branch->id }}" @selected((int) old('branch_id', $activeBranch?->id) === (int) $branch->id)>{{ $branch->name }}</option>
              @endforeach
            </select>
            <div class="form-hint">This cashier will only see and sell from the selected branch.</div>
            @error('branch_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
          </div>
        @else
          <div class="col-md-6">
            <label class="form-label">Branch</label>
            <input class="form-control" value="{{ $activeBranch?->name ?? 'Current branch' }}" disabled>
            <div class="form-hint">Branch admins create cashiers for their active branch.</div>
          </div>
        @endif
        <div class="col-md-6">
          <label class="form-label">Login Username</label>
          <input name="username" value="{{ old('username') }}" class="form-control" required autocomplete="username">
          <div class="form-hint">The cashier can sign in with this username and password.</div>
          @error('username')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <label class="form-label">Login Email <span class="text-muted">(optional)</span></label>
          <input type="email" name="email" value="{{ old('email') }}" class="form-control" autocomplete="email">
          <div class="form-hint">Leave blank if this cashier should only use username login.</div>
          @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
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
        <div class="col-12">
          <input type="hidden" name="is_active" value="0">
          <label class="cashier-active-toggle">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') === '1' ? 'checked' : '' }}>
            <span><strong>Active account</strong><small>Allow this cashier to sign in immediately.</small></span>
          </label>
        </div>
        <div class="col-12">
          <input type="hidden" name="email_notifications_enabled" value="0">
          <label class="cashier-active-toggle">
            <input type="checkbox" name="email_notifications_enabled" value="1" {{ old('email_notifications_enabled', '1') === '1' ? 'checked' : '' }}>
            <span><strong>Email notifications</strong><small>Send this cashier email alerts when online orders are assigned to their branch.</small></span>
          </label>
        </div>
      </div>
      <div class="d-flex flex-wrap gap-2 mt-2">
        <button class="btn btn-primary"><i class="bi bi-person-plus"></i> Create Cashier</button>
        <a href="{{ route('pos.admin.staff.index') }}" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
@endsection

