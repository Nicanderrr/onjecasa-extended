@extends('layouts.pos-admin')

@section('title', 'Cashiers - ONJECASA POS')
@section('page-eyebrow', 'Access')
@section('page-title', 'Cashiers')
@section('page-description', 'Manage cashier access and review sales performance.')
@section('page-actions')
  <a href="{{ route('pos.admin.staff.create') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-person-plus"></i> Add Cashier
  </a>
@endsection

@section('content')
@php
  $linkedCashiers = $rows->whereNotNull('user_id');
  $activeCashiers = $linkedCashiers->where('is_active', true)->count();
  $cashierSales = (float) $linkedCashiers->sum(fn ($row) => (float) ($row->sales_total ?? 0));
@endphp

<div class="entity-page">
  <section class="row g-2 dashboard-metrics entity-metrics" aria-label="Cashier metrics">
    <div class="col-12 col-md-4">
      <article class="metric-card metric-primary">
        <div class="metric-top"><span class="metric-label">Cashier Accounts</span><span class="metric-icon"><i class="bi bi-people"></i></span></div>
        <div class="metric-value">{{ $linkedCashiers->count() }}</div>
        <div class="metric-meta"><span class="text-primary">Access</span><span>login accounts</span></div>
      </article>
    </div>
    <div class="col-12 col-md-4">
      <article class="metric-card metric-success">
        <div class="metric-top"><span class="metric-label">Active Cashiers</span><span class="metric-icon"><i class="bi bi-person-check"></i></span></div>
        <div class="metric-value">{{ $activeCashiers }}</div>
        <div class="metric-meta"><span class="text-success">Online ready</span><span>can sign in</span></div>
      </article>
    </div>
    <div class="col-12 col-md-4">
      <article class="metric-card metric-warning">
        <div class="metric-top"><span class="metric-label">Cashier Sales</span><span class="metric-icon"><i class="bi bi-cash-stack"></i></span></div>
        <div class="metric-value">{{ number_format($cashierSales, 2) }}</div>
        <div class="metric-meta"><span class="text-warning">Lifetime</span><span>processed value</span></div>
      </article>
    </div>
  </section>

  <div class="card shadow entity-card cashier-table-card">
    <div class="card-header border-0 entity-toolbar">
      <div class="cashier-table-heading">
        <span><i class="bi bi-person-vcard"></i></span>
        <div><strong>Cashier directory</strong><small>Accounts, access status, and performance</small></div>
      </div>
      <div class="entity-filter-wrap">
        <i class="bi bi-search"></i>
        <input type="search" class="form-control form-control-sm entity-filter" placeholder="Search cashiers" aria-label="Filter cashiers" data-table-filter="#cashier-table">
      </div>
    </div>

    <div class="table-responsive">
      <table class="table align-items-center table-flush cashier-table" id="cashier-table">
        <thead class="thead-light">
          <tr><th>Cashier</th><th>Contact</th><th>Branch</th><th>Access</th><th>Orders</th><th>Sales</th><th>Last Sale</th><th>Actions</th></tr>
        </thead>
        <tbody>
          @forelse($rows as $cashier)
            @php
              $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($cashier->name, 0, 1));
              $isLinked = ! empty($cashier->user_id);
              $isActive = $isLinked && (bool) $cashier->is_active;
            @endphp
            <tr data-filter-row>
              <td>
                <div class="cashier-identity"><span>{{ $initial }}</span><div><strong>{{ $cashier->name }}</strong><small>{{ $isLinked ? 'Cashier account' : 'Legacy staff record' }}</small></div></div>
              </td>
              <td>
                <div class="cashier-contact">
                  <strong>{{ $cashier->email ?: 'No email' }}</strong>
                  <span>{{ $cashier->username ?? $cashier->account_username ?? $cashier->number }}</span>
                </div>
              </td>
              <td><span class="shop-chip">{{ $cashier->branch_name ?? 'No branch' }}</span></td>
              <td>
                <span class="cashier-access {{ $isActive ? 'active' : ($isLinked ? 'inactive' : 'unlinked') }}">
                  <span></span>{{ $isActive ? 'Active' : ($isLinked ? 'Inactive' : 'Setup needed') }}
                </span>
              </td>
              <td><span class="cashier-number">{{ (int) ($cashier->order_count ?? 0) }}</span></td>
              <td><span class="cashier-sales">{{ number_format((float) ($cashier->sales_total ?? 0), 2) }}</span></td>
              <td><span class="cashier-last-sale">{{ $cashier->last_sale_at ? \Illuminate\Support\Carbon::parse($cashier->last_sale_at)->format('d M Y') : 'No sales yet' }}</span></td>
              <td>
                <div class="action-group cashier-actions">
                  <a href="{{ route('pos.admin.staff.edit', $cashier->id) }}" class="btn btn-sm btn-outline-primary" title="Edit cashier"><i class="bi bi-pencil-square"></i></a>
                  @if($isActive || ! $isLinked)
                    <form method="POST" action="{{ route('pos.admin.staff.destroy', $cashier->id) }}">
                      @csrf @method('DELETE')
                      <button class="btn btn-sm btn-outline-danger" title="{{ $isLinked ? 'Deactivate cashier' : 'Delete legacy record' }}"><i class="bi {{ $isLinked ? 'bi-person-slash' : 'bi-trash3' }}"></i></button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr data-filter-empty><td colspan="8" class="text-center py-5 text-muted">No cashiers yet. Add one to create the first cashier login.</td></tr>
          @endforelse
          @if($rows->count())
            <tr data-filter-empty style="display:none;"><td colspan="8" class="text-center py-5 text-muted">No matching cashiers found.</td></tr>
          @endif
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

