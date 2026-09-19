@extends('layouts.pos-admin')

@section('title', 'Branches')
@section('page-eyebrow', 'Stores')
@section('page-title', 'Branches')
@section('page-description', 'Create and manage the physical stores that POS users, products, stock, and orders belong to.')

@section('content')
@php
  $branchRoutePrefix = request()->routeIs('pos.admin.branches*') ? 'pos.admin.branches' : 'superadmin.branches';
@endphp
<div class="entity-page">
  <section class="row g-2 dashboard-metrics entity-metrics">
    <div class="col-6 col-xl-3"><article class="metric-card metric-primary"><div class="metric-top"><span class="metric-label">Branches</span><span class="metric-icon"><i class="bi bi-shop"></i></span></div><div class="metric-value">{{ number_format($metrics['total']) }}</div><div class="metric-meta"><span>All stores</span></div></article></div>
    <div class="col-6 col-xl-3"><article class="metric-card metric-success"><div class="metric-top"><span class="metric-label">Active</span><span class="metric-icon"><i class="bi bi-check2-circle"></i></span></div><div class="metric-value">{{ number_format($metrics['active']) }}</div><div class="metric-meta"><span>Selectable stores</span></div></article></div>
    <div class="col-6 col-xl-3"><article class="metric-card metric-warning"><div class="metric-top"><span class="metric-label">Assigned Users</span><span class="metric-icon"><i class="bi bi-people"></i></span></div><div class="metric-value">{{ number_format($metrics['users']) }}</div><div class="metric-meta"><span>Have branch access</span></div></article></div>
    <div class="col-6 col-xl-3"><article class="metric-card metric-danger"><div class="metric-top"><span class="metric-label">POS Orders</span><span class="metric-icon"><i class="bi bi-receipt"></i></span></div><div class="metric-value">{{ number_format($metrics['pos_orders']) }}</div><div class="metric-meta"><span>Across branches</span></div></article></div>
  </section>

  <section class="row g-3 mt-1">
    <div class="col-12 col-xl-4">
      <div class="card shadow entity-card h-100">
        <div class="card-header border-0 entity-toolbar">
          <div class="cashier-table-heading"><span><i class="bi bi-plus-circle"></i></span><div><strong>Add branch</strong><small>Create a new store location</small></div></div>
        </div>
        <form method="POST" action="{{ route($branchRoutePrefix.'.store') }}" class="card-body">
          @csrf
          <div class="mb-3"><label class="form-label">Branch Name</label><input name="name" value="{{ old('name') }}" class="form-control" placeholder="Store 2" required></div>
          <div class="mb-3"><label class="form-label">Code</label><input name="code" value="{{ old('code') }}" class="form-control" placeholder="STORE2" required></div>
          <div class="mb-3"><label class="form-label">Phone</label><input name="phone" value="{{ old('phone') }}" class="form-control" placeholder="+233..."></div>
          <div class="mb-3"><label class="form-label">Address</label><textarea name="address" rows="4" class="form-control" placeholder="Branch address">{{ old('address') }}</textarea></div>
          <div class="row g-2">
            <div class="col-6"><label class="form-label">Latitude</label><input name="latitude" value="{{ old('latitude') }}" type="number" step="0.0000001" class="form-control" placeholder="5.6037"></div>
            <div class="col-6"><label class="form-label">Longitude</label><input name="longitude" value="{{ old('longitude') }}" type="number" step="0.0000001" class="form-control" placeholder="-0.1870"></div>
            <div class="col-6"><label class="form-label">Base Fee</label><input name="base_delivery_fee" value="{{ old('base_delivery_fee', 15) }}" type="number" step="0.01" min="0" class="form-control"></div>
            <div class="col-6"><label class="form-label">Fee/KM</label><input name="delivery_fee_per_km" value="{{ old('delivery_fee_per_km', 3) }}" type="number" step="0.01" min="0" class="form-control"></div>
            <div class="col-12"><label class="form-label">Max Distance KM</label><input name="max_delivery_distance_km" value="{{ old('max_delivery_distance_km') }}" type="number" step="0.01" min="0" class="form-control" placeholder="Leave empty for no limit"></div>
          </div>
          <label class="form-check mb-4"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked> <span class="form-check-label">Active and selectable</span></label>
          <button class="btn btn-primary w-100"><i class="bi bi-save"></i> Create Branch</button>
        </form>
      </div>
    </div>

    <div class="col-12 col-xl-8">
      <div class="card shadow entity-card">
        <div class="card-header border-0 entity-toolbar">
          <div class="cashier-table-heading"><span><i class="bi bi-building"></i></span><div><strong>Branch directory</strong><small>Stores available to admin and POS users</small></div></div>
        </div>
        <div class="table-responsive">
          <table class="table align-middle mb-0" style="min-width: 980px;">
            <thead><tr><th>Branch</th><th>Contact</th><th>Map</th><th>Delivery Fees</th><th>Users</th><th>Status</th><th>Save</th></tr></thead>
            <tbody>
              @forelse($branches as $branch)
                <tr>
                  <form method="POST" action="{{ route($branchRoutePrefix.'.update', $branch) }}">
                    @csrf @method('PATCH')
                    <td style="min-width: 220px;">
                      <input name="name" value="{{ $branch->name }}" class="form-control form-control-sm mb-2" required>
                      <input name="code" value="{{ $branch->code }}" class="form-control form-control-sm" required>
                    </td>
                    <td style="min-width: 260px;">
                      <input name="phone" value="{{ $branch->phone }}" class="form-control form-control-sm mb-2" placeholder="Phone">
                      <input name="address" value="{{ $branch->address }}" class="form-control form-control-sm" placeholder="Address">
                    </td>
                    <td style="min-width: 220px;">
                      <input name="latitude" value="{{ $branch->latitude }}" type="number" step="0.0000001" class="form-control form-control-sm mb-2" placeholder="Latitude">
                      <input name="longitude" value="{{ $branch->longitude }}" type="number" step="0.0000001" class="form-control form-control-sm" placeholder="Longitude">
                    </td>
                    <td style="min-width: 220px;">
                      <input name="base_delivery_fee" value="{{ $branch->base_delivery_fee ?? 0 }}" type="number" step="0.01" min="0" class="form-control form-control-sm mb-2" placeholder="Base fee">
                      <input name="delivery_fee_per_km" value="{{ $branch->delivery_fee_per_km ?? 0 }}" type="number" step="0.01" min="0" class="form-control form-control-sm mb-2" placeholder="Fee per KM">
                      <input name="max_delivery_distance_km" value="{{ $branch->max_delivery_distance_km }}" type="number" step="0.01" min="0" class="form-control form-control-sm" placeholder="Max KM">
                    </td>
                    <td><span class="shop-chip">{{ number_format($branch->users_count) }} assigned</span></td>
                    <td><label class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($branch->is_active)> <span class="form-check-label">Active</span></label></td>
                    <td><button class="btn btn-sm btn-outline-primary"><i class="bi bi-check2"></i></button></td>
                  </form>
                </tr>
              @empty
                <tr><td colspan="5" class="text-center py-5 text-muted">No branches created yet.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if($branches->hasPages())
          <div class="card-footer border-0">{{ $branches->links() }}</div>
        @endif
      </div>
    </div>
  </section>
</div>
@endsection
