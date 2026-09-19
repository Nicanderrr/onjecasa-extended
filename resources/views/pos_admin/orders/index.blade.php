@extends('layouts.pos-admin')

@section('title', 'Orders - ONJECASA POS')
@section('page-eyebrow', 'Sales')
@section('page-title', 'Orders')
@section('page-description', 'Review customers, cashiers, payments, and order activity.')
@section('page-actions')
  <a href="{{ route('pos.admin.orders.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-cart-plus"></i> New Order</a>
@endsection

@section('content')
<div class="card shadow entity-card orders-card">
  <div class="card-header border-0 entity-toolbar orders-toolbar">
    <div class="orders-table-heading">
      <span class="orders-table-icon"><i class="bi bi-receipt-cutoff"></i></span>
      <div><strong>Order register</strong><span>{{ $orders->total() }} {{ \Illuminate\Support\Str::plural('order', $orders->total()) }} recorded</span></div>
    </div>
    <div class="entity-filter-wrap">
      <i class="bi bi-search"></i>
      <input type="search" class="form-control form-control-sm entity-filter" placeholder="Search orders" aria-label="Filter orders" data-table-filter="#orders-table">
    </div>
  </div>

  <div class="table-responsive">
    <table class="table align-items-center table-flush orders-table" id="orders-table">
      <thead class="thead-light"><tr><th>Order</th><th>Customer</th><th>Cashier</th><th>Payment</th><th>Total</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
      <tbody>
        @forelse($orders as $order)
          @php
            $statusKey = \Illuminate\Support\Str::slug($order->status ?: 'pending');
            $paymentMethod = $order->payment_method ?: 'Not recorded';
            $isMobileMoney = strcasecmp($paymentMethod, 'Mobile Money') === 0;
            $customerName = $order->customer_name ?: 'Walk-in';
            $customerInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($customerName, 0, 1));
            $itemCount = (int) ($order->item_count ?? 0);
          @endphp
          <tr data-filter-row>
            <td class="order-cell">
              <div class="order-identity"><span class="order-symbol"><i class="bi bi-bag-check"></i></span><div><a href="{{ route('pos.admin.orders.show', $order->id) }}">{{ $order->code }}</a><span>#{{ $order->id }} · {{ $itemCount }} {{ \Illuminate\Support\Str::plural('item', $itemCount) }}</span></div></div>
            </td>
            <td><div class="order-customer"><span class="customer-initial">{{ $customerInitial }}</span><strong>{{ $customerName }}</strong></div></td>
            <td><span class="order-cashier"><i class="bi bi-person-badge"></i>{{ $order->cashier_name ?: 'Unknown' }}</span></td>
            <td><span class="payment-method {{ $isMobileMoney ? 'mobile-money' : 'cash' }}"><i class="bi {{ $isMobileMoney ? 'bi-phone' : 'bi-cash' }}"></i>{{ $paymentMethod }}</span></td>
            <td><span class="order-total">{{ number_format($order->grand_total, 2) }}</span></td>
            <td><span class="order-status order-status-{{ $statusKey }}"><span></span>{{ ucfirst($order->status) }}</span></td>
            <td><div class="order-date"><strong>{{ \Illuminate\Support\Carbon::parse($order->created_at)->format('d M Y') }}</strong><span>{{ \Illuminate\Support\Carbon::parse($order->created_at)->format('h:i A') }}</span></div></td>
            <td><a class="btn btn-sm btn-outline-primary order-view-btn" href="{{ route('pos.admin.orders.show', $order->id) }}"><i class="bi bi-eye"></i> View</a></td>
          </tr>
        @empty
          <tr data-filter-empty><td colspan="8" class="text-center py-5 text-muted">No orders yet. Create the first order to begin the register.</td></tr>
        @endforelse
        @if($orders->count())
          <tr data-filter-empty style="display:none;"><td colspan="8" class="text-center py-5 text-muted">No matching orders found.</td></tr>
        @endif
      </tbody>
    </table>
  </div>
  @if($orders->hasPages())<div class="orders-pagination">{{ $orders->links() }}</div>@endif
</div>
@endsection

