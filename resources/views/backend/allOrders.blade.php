@extends('backend.admin')

@section('title', 'All Online Orders - ONJECASA')
@section('page-icon', 'bi bi-receipt-cutoff')
@section('page-eyebrow', 'Website Admin')
@section('page-title', 'All Online Orders')
@section('page-description', 'Review every storefront order line, update fulfillment status, and separate delivery from pickup orders.')
@section('page-actions')
  <a href="{{ route('admin_orders') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-clock-history"></i> New Orders
  </a>
@endsection

@section('admin')
@php
  $orders = collect($orders);
  $totalRevenue = $orders->sum('total');
  $pending = $orders->filter(fn ($order) => ($order->delivery_status ?? $order->status ?? 'pending') === 'pending')->count();
  $processing = $orders->filter(fn ($order) => in_array(($order->delivery_status ?? $order->status ?? 'pending'), ['confirmed', 'preparing', 'out_for_delivery', 'processing'], true))->count();
  $completed = $orders->filter(fn ($order) => in_array(($order->delivery_status ?? $order->status ?? 'pending'), ['completed', 'paid', 'delivered'], true))->count();
  $cancelled = $orders->filter(fn ($order) => ($order->delivery_status ?? $order->status ?? 'pending') === 'cancelled')->count();
  $deliveryCount = $orders->where('fulfillment_method', 'delivery')->count();
  $pickupCount = $orders->where('fulfillment_method', 'pickup')->count();
  $statusClass = [
      'pending' => 'status-pending',
      'confirmed' => 'status-processing',
      'preparing' => 'status-processing',
      'out_for_delivery' => 'status-processing',
      'processing' => 'status-processing',
      'delivered' => 'status-completed',
      'completed' => 'status-completed',
      'paid' => 'status-completed',
      'cancelled' => 'status-cancelled',
  ];
  $statusLabels = [
      'pending' => 'Pending',
      'confirmed' => 'Confirmed',
      'preparing' => 'Preparing',
      'out_for_delivery' => 'Out for delivery',
      'delivered' => 'Delivered',
      'completed' => 'Completed',
      'cancelled' => 'Cancelled',
  ];
@endphp

<style>
  .orders-workspace {
    display: grid;
    gap: 1.25rem;
    min-width: 0;
    max-width: 100%;
  }

  .orders-metrics {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
  }

  .order-metric-card {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    min-width: 0;
    padding: 1.05rem;
    border: 1px solid var(--admin-border);
    border-radius: 8px;
    background: var(--admin-surface);
    box-shadow: 0 12px 28px rgba(15, 23, 42, .06);
  }

  .order-metric-card strong,
  .order-metric-card span {
    display: block;
  }

  .order-metric-card strong {
    color: var(--admin-text);
    font-size: 1.45rem;
    line-height: 1.1;
    overflow-wrap: anywhere;
  }

  .order-metric-card span {
    margin-top: .25rem;
    color: var(--admin-muted);
    font-size: .78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .04em;
  }

  .order-metric-icon {
    width: 42px;
    height: 42px;
    display: inline-grid;
    place-items: center;
    flex: 0 0 auto;
    border-radius: 8px;
    background: #eaf2ff;
    color: var(--admin-primary);
    font-size: 1.15rem;
  }

  .orders-toolbar,
  .orders-list-card {
    min-width: 0;
    max-width: 100%;
    border: 1px solid var(--admin-border);
    border-radius: 8px;
    background: var(--admin-surface);
    box-shadow: 0 16px 34px rgba(15, 23, 42, .06);
  }

  .orders-toolbar {
    display: grid;
    grid-template-columns: minmax(240px, 1fr) auto;
    gap: 1rem;
    align-items: end;
    padding: 1rem;
  }

  .orders-toolbar label {
    display: block;
    margin-bottom: .35rem;
    color: var(--admin-muted);
    font-size: .72rem;
    font-weight: 900;
    letter-spacing: .05em;
    text-transform: uppercase;
  }

  .bulk-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: .65rem;
    flex-wrap: wrap;
  }

  .bulk-actions .form-select {
    min-width: 160px;
  }

  .orders-table-wrap {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  .orders-table {
    min-width: 860px;
    margin-bottom: 0;
  }

  .orders-table th {
    padding: .9rem 1rem;
    color: var(--admin-muted);
    font-size: .72rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .05em;
    border-bottom: 1px solid var(--admin-border);
    white-space: nowrap;
  }

  .orders-table td {
    padding: 1rem;
    vertical-align: middle;
    border-bottom: 1px solid var(--admin-border);
  }

  .orders-table tbody tr:last-child td {
    border-bottom: 0;
  }

  .customer-cell {
    display: flex;
    align-items: center;
    gap: .8rem;
    min-width: 0;
  }

  .customer-avatar {
    width: 38px;
    height: 38px;
    display: inline-grid;
    place-items: center;
    flex: 0 0 auto;
    border-radius: 8px;
    background: var(--admin-primary);
    color: #fff;
    font-weight: 900;
  }

  .cell-title {
    color: var(--admin-text);
    font-weight: 900;
    line-height: 1.25;
    overflow-wrap: anywhere;
  }

  .cell-subtitle {
    margin-top: .15rem;
    color: var(--admin-muted);
    font-size: .78rem;
    font-weight: 700;
    line-height: 1.35;
  }

  .order-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .35rem;
    border-radius: 999px;
    padding: .35rem .65rem;
    font-size: .72rem;
    font-weight: 900;
    white-space: nowrap;
  }

  .status-pending { background: #fff7ed; color: #c2410c; }
  .status-processing { background: #eff6ff; color: #1d4ed8; }
  .status-completed { background: #ecfdf5; color: #047857; }
  .status-cancelled { background: #fef2f2; color: #b91c1c; }
  .fulfillment-delivery { background: #eef6ff; color: #2563eb; }
  .fulfillment-pickup { background: #f0fdf4; color: #15803d; }

  .order-row-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: .5rem;
    flex-wrap: nowrap;
  }

  .order-row-actions form {
    margin: 0;
  }

  .order-row-actions .form-select {
    min-width: 134px;
  }

  .empty-orders {
    padding: 3rem 1rem;
    text-align: center;
    color: var(--admin-muted);
  }

  @media (max-width: 991.98px) {
    .orders-metrics {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .orders-toolbar {
      grid-template-columns: 1fr;
    }

    .bulk-actions {
      justify-content: stretch;
    }

    .bulk-actions .form-select,
    .bulk-actions .btn {
      flex: 1 1 180px;
    }

    .orders-table {
      min-width: 760px;
    }
  }

  @media (max-width: 575.98px) {
    .orders-metrics {
      grid-template-columns: 1fr;
    }

    .order-metric-card {
      padding: .9rem;
    }

    .orders-table {
      min-width: 680px;
    }
  }
</style>

<div class="orders-workspace">
  @if(session('success'))
    <div class="alert alert-success mb-0" role="alert">{{ session('success') }}</div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger mb-0" role="alert">{{ session('error') }}</div>
  @endif

  <section class="orders-metrics" aria-label="Order summary">
    <div class="order-metric-card">
      <div>
        <strong>{{ number_format($orders->count()) }}</strong>
        <span>Order lines</span>
      </div>
      <div class="order-metric-icon"><i class="bi bi-receipt"></i></div>
    </div>
    <div class="order-metric-card">
      <div>
        <strong>{{ number_format($pending) }}</strong>
        <span>Pending</span>
      </div>
      <div class="order-metric-icon"><i class="bi bi-hourglass-split"></i></div>
    </div>
    <div class="order-metric-card">
      <div>
        <strong>{{ number_format($completed) }}</strong>
        <span>Completed</span>
      </div>
      <div class="order-metric-icon"><i class="bi bi-check2-circle"></i></div>
    </div>
    <div class="order-metric-card">
      <div>
        <strong>GHC {{ number_format($totalRevenue, 2) }}</strong>
        <span>Revenue</span>
      </div>
      <div class="order-metric-icon"><i class="bi bi-cash-coin"></i></div>
    </div>
  </section>

  <section class="orders-toolbar">
    <div>
      <label for="searchInput">Search Orders</label>
      <input type="search" id="searchInput" class="form-control" placeholder="Search customer, product, phone, address, status...">
    </div>
    <form action="{{ route('admin.orders.bulk-update') }}" method="POST" id="bulk-form" class="bulk-actions">
      @csrf
      <select name="status" class="form-select" required>
        <option value="">Bulk status</option>
        @foreach($statusLabels as $value => $label)
          <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
      </select>
      <button type="submit" class="btn btn-primary" onclick="return confirm('Update selected orders?')">
        <i class="bi bi-check2-all"></i> Apply
      </button>
    </form>
  </section>

  <section class="orders-list-card">
    <div class="card-header border-0 d-flex align-items-center justify-content-between gap-3 flex-wrap">
      <div>
        <h3 class="mb-1">Storefront order lines</h3>
        <p class="text-muted mb-0">{{ number_format($deliveryCount) }} delivery, {{ number_format($pickupCount) }} pickup, {{ number_format($processing) }} processing, {{ number_format($cancelled) }} cancelled.</p>
      </div>
      <label class="d-inline-flex align-items-center gap-2 fw-bold text-muted mb-0">
        <input type="checkbox" id="select-all">
        Select all
      </label>
    </div>

    <div class="orders-table-wrap">
      <table class="table orders-table" id="allOrdersTable">
        <thead>
          <tr>
            <th>Select</th>
            <th>Order</th>
            <th>Customer</th>
            <th>Product</th>
            <th class="text-center">Qty</th>
            <th class="text-end">Total</th>
            <th>Fulfillment</th>
            <th>Delivery</th>
            <th>Status</th>
            <th class="text-end">Manage</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($orders as $order)
            @php
              $status = $order->delivery_status ?? $order->status ?? 'pending';
              $fulfillment = $order->fulfillment_method ?? 'delivery';
              $fulfillmentClass = $fulfillment === 'pickup' ? 'fulfillment-pickup' : 'fulfillment-delivery';
            @endphp
            <tr>
              <td>
                <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" form="bulk-form" class="order-checkbox">
              </td>
              <td>
                <div class="cell-title">#{{ $order->order_id ?? $order->id }}</div>
                <div class="cell-subtitle">{{ $order->created_at ? \Illuminate\Support\Carbon::parse($order->created_at)->format('M j, Y g:ia') : 'No date' }}</div>
              </td>
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
              <td class="text-center">
                <span class="order-pill status-processing">{{ $order->quantity }}</span>
              </td>
              <td class="text-end">
                <div class="cell-title">GHC {{ number_format($order->total, 2) }}</div>
              </td>
              <td>
                <span class="order-pill {{ $fulfillmentClass }}"><i class="bi {{ $fulfillment === 'pickup' ? 'bi-shop' : 'bi-truck' }}"></i>{{ ucfirst($fulfillment) }}</span>
                <div class="cell-subtitle mt-2">{{ $order->phone }}</div>
                <div class="cell-subtitle">{{ \Illuminate\Support\Str::limit($order->address, 48) }}</div>
              </td>
              <td>
                @if($fulfillment === 'delivery')
                  <div class="cell-title">GHC {{ number_format((float) ($order->delivery_fee ?? 0), 2) }}</div>
                  <div class="cell-subtitle">{{ number_format((float) ($order->delivery_distance_km ?? 0), 2) }} km</div>
                  @if(! empty($order->delivery_latitude) && ! empty($order->delivery_longitude))
                    <a class="cell-subtitle d-inline-block mt-1" target="_blank" rel="noopener" href="https://www.google.com/maps?q={{ $order->delivery_latitude }},{{ $order->delivery_longitude }}">Open map</a>
                  @endif
                @else
                  <span class="order-pill fulfillment-pickup">No fee</span>
                @endif
              </td>
              <td>
                <span class="order-pill {{ $statusClass[$status] ?? 'status-pending' }}">{{ $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}</span>
              </td>
              <td>
                <div class="order-row-actions">
                  <form action="{{ route('admin.order.update-status', $order->id) }}" method="POST">
                    @csrf
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                      @foreach($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                      @endforeach
                    </select>
                  </form>
                  <a href="{{ route('deleteorder', $order->id) }}" id="delete" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-trash"></i>
                  </a>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="10">
                <div class="empty-orders">
                  <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                  No online orders found.
                </div>
              </td>
            </tr>
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
    document.querySelectorAll('#allOrdersTable tbody tr').forEach(function(row) {
      row.style.display = row.textContent.toLowerCase().indexOf(value) > -1 ? '' : 'none';
    });
  });

  document.getElementById('select-all')?.addEventListener('change', function() {
    document.querySelectorAll('.order-checkbox').forEach(function(checkbox) {
      checkbox.checked = this.checked;
    }, this);
  });
</script>
@endpush


