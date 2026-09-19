@extends('backend.admin')

@section('title', 'New Online Orders - ONJECASA')
@section('page-icon', 'bi bi-bag-check')
@section('page-eyebrow', 'Website Admin')
@section('page-title', 'New Online Orders')
@section('page-description', 'Review current storefront order lines, customer details, purchase units, and fulfillment method.')
@section('page-actions')
  <a href="{{ route('all_orders') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-list-check"></i> All Orders
  </a>
@endsection

@section('admin')
@php
  $orders = collect($orders);
  $totalRevenue = $orders->sum('total');
  $totalQuantity = $orders->sum('quantity');
  $uniqueCustomers = $orders->pluck('user_id')->unique()->count();
  $deliveryCount = $orders->where('fulfillment_method', 'delivery')->count();
  $pickupCount = $orders->where('fulfillment_method', 'pickup')->count();
@endphp

<style>
  .orders-workspace { display: grid; gap: 1.25rem; min-width: 0; max-width: 100%; }
  .orders-metrics { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem; }
  .order-metric-card {
    display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem;
    min-width: 0; padding: 1.05rem; border: 1px solid var(--admin-border);
    border-radius: 8px; background: var(--admin-surface); box-shadow: 0 12px 28px rgba(15, 23, 42, .06);
  }
  .order-metric-card strong { display: block; color: var(--admin-text); font-size: 1.45rem; line-height: 1.1; overflow-wrap: anywhere; }
  .order-metric-card span { display: block; margin-top: .25rem; color: var(--admin-muted); font-size: .78rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; }
  .order-metric-icon {
    width: 42px; height: 42px; display: inline-grid; place-items: center; flex: 0 0 auto;
    border-radius: 8px; background: #eaf2ff; color: var(--admin-primary); font-size: 1.15rem;
  }
  .orders-toolbar, .orders-list-card {
    min-width: 0; max-width: 100%;
    border: 1px solid var(--admin-border); border-radius: 8px; background: var(--admin-surface);
    box-shadow: 0 16px 34px rgba(15, 23, 42, .06);
  }
  .orders-toolbar { display: grid; grid-template-columns: minmax(240px, 1fr) auto; gap: 1rem; align-items: end; padding: 1rem; }
  .orders-toolbar label { display: block; margin-bottom: .35rem; color: var(--admin-muted); font-size: .72rem; font-weight: 900; letter-spacing: .05em; text-transform: uppercase; }
  .orders-table-wrap { width: 100%; max-width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
  .orders-table { min-width: 780px; margin-bottom: 0; }
  .orders-table th {
    padding: .9rem 1rem; color: var(--admin-muted); font-size: .72rem; font-weight: 900;
    text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid var(--admin-border); white-space: nowrap;
  }
  .orders-table td { padding: 1rem; vertical-align: middle; border-bottom: 1px solid var(--admin-border); }
  .orders-table tbody tr:last-child td { border-bottom: 0; }
  .customer-cell { display: flex; align-items: center; gap: .8rem; min-width: 0; }
  .customer-avatar {
    width: 38px; height: 38px; display: inline-grid; place-items: center; flex: 0 0 auto;
    border-radius: 8px; background: var(--admin-primary); color: #fff; font-weight: 900;
  }
  .cell-title { color: var(--admin-text); font-weight: 900; line-height: 1.25; overflow-wrap: anywhere; }
  .cell-subtitle { margin-top: .15rem; color: var(--admin-muted); font-size: .78rem; font-weight: 700; line-height: 1.35; }
  .order-pill {
    display: inline-flex; align-items: center; justify-content: center; gap: .35rem; border-radius: 999px;
    padding: .35rem .65rem; font-size: .72rem; font-weight: 900; white-space: nowrap;
  }
  .pill-info { background: #eff6ff; color: #1d4ed8; }
  .pill-delivery { background: #eef6ff; color: #2563eb; }
  .pill-pickup { background: #f0fdf4; color: #15803d; }
  .empty-orders { padding: 3rem 1rem; text-align: center; color: var(--admin-muted); }
  @media (max-width: 991.98px) {
    .orders-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .orders-toolbar { grid-template-columns: 1fr; }
    .orders-table { min-width: 700px; }
  }
  @media (max-width: 575.98px) {
    .orders-metrics { grid-template-columns: 1fr; }
    .orders-table { min-width: 640px; }
  }
</style>

<div class="orders-workspace">
  <section class="orders-metrics">
    <div class="order-metric-card"><div><strong>{{ number_format($orders->count()) }}</strong><span>Order lines</span></div><div class="order-metric-icon"><i class="bi bi-receipt"></i></div></div>
    <div class="order-metric-card"><div><strong>{{ number_format($uniqueCustomers) }}</strong><span>Customers</span></div><div class="order-metric-icon"><i class="bi bi-people"></i></div></div>
    <div class="order-metric-card"><div><strong>{{ number_format($totalQuantity) }}</strong><span>Units ordered</span></div><div class="order-metric-icon"><i class="bi bi-box-seam"></i></div></div>
    <div class="order-metric-card"><div><strong>GHC {{ number_format($totalRevenue, 2) }}</strong><span>Revenue</span></div><div class="order-metric-icon"><i class="bi bi-cash-coin"></i></div></div>
  </section>

  <section class="orders-toolbar">
    <div>
      <label for="searchInput">Search Orders</label>
      <input type="search" id="searchInput" class="form-control" placeholder="Search customer, product, phone, address...">
    </div>
    <div class="d-flex gap-2 flex-wrap justify-content-lg-end">
      <span class="order-pill pill-delivery"><i class="bi bi-truck"></i>{{ number_format($deliveryCount) }} delivery</span>
      <span class="order-pill pill-pickup"><i class="bi bi-shop"></i>{{ number_format($pickupCount) }} pickup</span>
    </div>
  </section>

  <section class="orders-list-card">
    <div class="card-header border-0">
      <h3 class="mb-1">Current storefront order lines</h3>
      <p class="text-muted mb-0">Use this view for quick review. Open all orders when you need status management.</p>
    </div>
    <div class="orders-table-wrap">
      <table class="table orders-table" id="ordersTable">
        <thead>
          <tr>
            <th>Customer</th>
            <th>Product</th>
            <th class="text-center">Qty</th>
            <th class="text-end">Total</th>
            <th>Fulfillment</th>
            <th>Delivery</th>
            <th class="text-end">Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($orders as $order)
            @php
              $fulfillment = $order->fulfillment_method ?? 'delivery';
              $fulfillmentClass = $fulfillment === 'pickup' ? 'pill-pickup' : 'pill-delivery';
            @endphp
            <tr>
              <td>
                <div class="customer-cell">
                  <div class="customer-avatar">{{ strtoupper(substr($order->username ?? 'C', 0, 1)) }}</div>
                  <div>
                    <div class="cell-title">{{ $order->username ?: 'Customer' }}</div>
                    <div class="cell-subtitle">Customer ID {{ $order->user_id }}</div>
                  </div>
                </div>
              </td>
              <td>
                <div class="cell-title">{{ $order->product_name }}</div>
                <div class="cell-subtitle">
                  {{ $order->size ? 'Unit: '.$order->size : 'Unit: Single Item' }}
                  {{ $order->color ? ' / Variant: '.$order->color : '' }}
                </div>
              </td>
              <td class="text-center"><span class="order-pill pill-info">{{ $order->quantity }}</span></td>
              <td class="text-end"><div class="cell-title">GHC {{ number_format($order->total, 2) }}</div></td>
              <td>
                <span class="order-pill {{ $fulfillmentClass }}"><i class="bi {{ $fulfillment === 'pickup' ? 'bi-shop' : 'bi-truck' }}"></i>{{ ucfirst($fulfillment) }}</span>
                <div class="cell-subtitle mt-2">{{ $order->phone }}</div>
                <div class="cell-subtitle">{{ \Illuminate\Support\Str::limit($order->address, 48) }}</div>
              </td>
              <td>
                @if($fulfillment === 'delivery')
                  <div class="cell-title">GHC {{ number_format((float) ($order->delivery_fee ?? 0), 2) }}</div>
                  <div class="cell-subtitle">{{ number_format((float) ($order->delivery_distance_km ?? 0), 2) }} km</div>
                  <div class="cell-subtitle">{{ ucfirst(str_replace('_', ' ', $order->delivery_status ?? $order->status ?? 'pending')) }}</div>
                  @if(! empty($order->delivery_latitude) && ! empty($order->delivery_longitude))
                    <a class="cell-subtitle d-inline-block mt-1" target="_blank" rel="noopener" href="https://www.google.com/maps?q={{ $order->delivery_latitude }},{{ $order->delivery_longitude }}">Open map</a>
                  @endif
                @else
                  <span class="order-pill pill-pickup">No fee</span>
                @endif
              </td>
              <td class="text-end">
                <a href="{{ route('deleteorder', $order->id) }}" id="delete" class="btn btn-outline-danger btn-sm">
                  <i class="bi bi-trash"></i>
                </a>
              </td>
            </tr>
          @empty
            <tr><td colspan="7"><div class="empty-orders"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No orders to display.</div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>
</div>
@endsection

@push('scripts')
<script>
  document.getElementById('searchInput')?.addEventListener('input', function() {
    var value = this.value.toLowerCase();
    document.querySelectorAll('#ordersTable tbody tr').forEach(function(row) {
      row.style.display = row.textContent.toLowerCase().indexOf(value) > -1 ? '' : 'none';
    });
  });
</script>
@endpush


