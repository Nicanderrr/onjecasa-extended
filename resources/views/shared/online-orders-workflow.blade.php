@php
  $orders = collect($orders);
  $currentUser = auth()->user();
  $currentUserId = (int) ($currentUser?->id ?? 0);
  $pendingCount = $orders->filter(fn ($order) => ($order->delivery_status ?? $order->status ?? 'pending') === 'pending')->count();
  $assignedToMe = $orders->filter(fn ($order) => (int) ($order->assigned_staff_user_id ?? 0) === $currentUserId)->count();
  $deliveryCount = $orders->where('fulfillment_method', 'delivery')->count();
  $statusClass = [
      'pending' => 'status-pending',
      'confirmed' => 'status-processing',
      'preparing' => 'status-processing',
      'out_for_delivery' => 'status-processing',
      'delivered' => 'status-completed',
      'completed' => 'status-completed',
      'cancelled' => 'status-cancelled',
  ];
@endphp

<style>
  .online-orders-shell { display: grid; gap: 1rem; min-width: 0; }
  .online-order-metrics { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .85rem; }
  .online-order-card, .online-order-table-card {
    min-width: 0; border: 1px solid rgba(148, 163, 184, .22); border-radius: 8px;
    background: var(--admin-surface, #fff); box-shadow: 0 14px 30px rgba(15, 23, 42, .06);
  }
  .online-order-card { padding: 1rem; }
  .online-order-card strong { display: block; font-size: 1.4rem; color: var(--admin-text, #111827); }
  .online-order-card span { color: var(--admin-muted, #64748b); font-size: .76rem; font-weight: 900; text-transform: uppercase; }
  .online-order-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
  .online-order-table { min-width: 1080px; margin-bottom: 0; }
  .online-order-table th { padding: .85rem 1rem; white-space: nowrap; font-size: .72rem; text-transform: uppercase; color: var(--admin-muted, #64748b); border-bottom: 1px solid rgba(148, 163, 184, .22); }
  .online-order-table td { padding: 1rem; vertical-align: middle; border-bottom: 1px solid rgba(148, 163, 184, .18); }
  .online-order-title { font-weight: 900; color: var(--admin-text, #111827); line-height: 1.25; }
  .online-order-sub { color: var(--admin-muted, #64748b); font-size: .78rem; font-weight: 700; line-height: 1.35; margin-top: .15rem; }
  .workflow-pill { display: inline-flex; align-items: center; gap: .35rem; border-radius: 999px; padding: .36rem .65rem; font-size: .72rem; font-weight: 900; white-space: nowrap; }
  .status-pending { background: #fff7ed; color: #c2410c; }
  .status-processing { background: #eff6ff; color: #1d4ed8; }
  .status-completed { background: #ecfdf5; color: #047857; }
  .status-cancelled { background: #fef2f2; color: #b91c1c; }
  .workflow-actions { display: flex; align-items: center; justify-content: flex-end; gap: .5rem; flex-wrap: wrap; }
  .workflow-actions .form-select { min-width: 160px; }
  @media (max-width: 991.98px) { .online-order-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
  @media (max-width: 575.98px) { .online-order-metrics { grid-template-columns: 1fr; } .online-order-table { min-width: 980px; } }
</style>

<div class="online-orders-shell">
  @if(session('success'))
    <div class="alert alert-success mb-0">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger mb-0">{{ session('error') }}</div>
  @endif

  <section class="online-order-metrics">
    <div class="online-order-card"><strong>{{ number_format($orders->count()) }}</strong><span>Total online orders</span></div>
    <div class="online-order-card"><strong>{{ number_format($pendingCount) }}</strong><span>Pending assignment</span></div>
    <div class="online-order-card"><strong>{{ number_format($assignedToMe) }}</strong><span>Assigned to me</span></div>
    <div class="online-order-card"><strong>{{ number_format($deliveryCount) }}</strong><span>Delivery orders</span></div>
  </section>

  <section class="online-order-table-card">
    <div class="p-3 border-bottom">
      <h2 class="h5 mb-1">{{ $title }}</h2>
      <p class="text-muted mb-0">{{ $description }}</p>
    </div>
    <div class="online-order-table-wrap">
      <table class="table online-order-table">
        <thead>
          <tr>
            <th>Order</th>
            <th>Customer</th>
            <th>Branch</th>
            <th>Fulfillment</th>
            <th>Delivery</th>
            <th>Assigned Staff</th>
            <th>Status</th>
            <th class="text-end">Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($orders as $order)
            @php
              $status = $order->delivery_status ?? $order->status ?? 'pending';
              $isAssigned = ! empty($order->assigned_staff_user_id);
              $isMine = (int) ($order->assigned_staff_user_id ?? 0) === $currentUserId;
              $canProcess = $canOverride || $isMine;
            @endphp
            <tr>
              <td>
                <div class="online-order-title">#{{ $order->order_id }}</div>
                <div class="online-order-sub">{{ $order->created_at ? \Illuminate\Support\Carbon::parse($order->created_at)->format('M j, Y g:ia') : 'No date' }}</div>
              </td>
              <td>
                <div class="online-order-title">{{ $order->username ?: 'Customer' }}</div>
                <div class="online-order-sub">{{ $order->phone }}</div>
              </td>
              <td>
                <div class="online-order-title">{{ $order->branch_name ?: 'No branch' }}</div>
                <div class="online-order-sub">{{ number_format((int) $order->item_count) }} item(s), GHC {{ number_format((float) $order->item_total + (float) $order->delivery_fee, 2) }}</div>
              </td>
              <td><span class="workflow-pill {{ $order->fulfillment_method === 'pickup' ? 'status-completed' : 'status-processing' }}">{{ ucfirst($order->fulfillment_method ?? 'delivery') }}</span></td>
              <td>
                @if(($order->fulfillment_method ?? 'delivery') === 'delivery')
                  <div class="online-order-title">GHC {{ number_format((float) $order->delivery_fee, 2) }}</div>
                  <div class="online-order-sub">{{ number_format((float) $order->delivery_distance_km, 2) }} km</div>
                  <div class="online-order-sub">{{ \Illuminate\Support\Str::limit($order->address, 42) }}</div>
                  @if($order->delivery_latitude && $order->delivery_longitude)
                    <a target="_blank" rel="noopener" href="https://www.google.com/maps?q={{ $order->delivery_latitude }},{{ $order->delivery_longitude }}">Open map</a>
                  @endif
                @else
                  <span class="workflow-pill status-completed">Pickup</span>
                @endif
              </td>
              <td>
                @if($isAssigned)
                  <div class="online-order-title">{{ $order->assigned_staff_name }}</div>
                  <div class="online-order-sub">Last update: {{ $order->status_updated_by_name ?: 'Not updated' }}</div>
                @else
                  <span class="workflow-pill status-pending">Unassigned</span>
                @endif
              </td>
              <td><span class="workflow-pill {{ $statusClass[$status] ?? 'status-pending' }}">{{ $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}</span></td>
              <td class="text-end">
                <div class="workflow-actions">
                  @if(! $isAssigned)
                    <form method="POST" action="{{ route($acceptRoute, $order->order_id) }}">
                      @csrf
                      <button class="btn btn-primary btn-sm"><i class="bi bi-check2-circle"></i> Accept</button>
                    </form>
                  @elseif($canProcess)
                    <form method="POST" action="{{ route($statusRoute, $order->order_id) }}">
                      @csrf
                      <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach($statusLabels as $value => $label)
                          <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                        @endforeach
                      </select>
                    </form>
                  @else
                    <span class="online-order-sub">Locked to {{ $order->assigned_staff_name }}</span>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center py-5 text-muted">No online orders for this branch yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>
</div>
