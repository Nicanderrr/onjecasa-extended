@extends('layouts.cashier')

@php
  $pageTitles = [
    'dashboard' => ['Overview', 'Dashboard', 'A focused view of this cashier station.'],
    'products' => ['Catalog', 'Products', 'Browse products and current branch stock.'],
    'orders' => ['Transactions', 'Orders', 'Review recent orders processed by this branch.'],
    'payments' => ['Transactions', 'Payments', 'Review collected payments and payment methods.'],
    'receipts' => ['Transactions', 'Receipts', 'Open recent orders and print customer receipts.'],
    'orders-reports' => ['Reports', 'Order Reports', 'Review order activity from the cashier workspace.'],
    'payments-reports' => ['Reports', 'Payment Reports', 'Review payment activity from the cashier workspace.'],
    'sales' => ['Reports', 'Sales', 'Review paid sales from the cashier workspace.'],
    'settings' => ['System', 'Settings', 'Update your cashier profile and notifications.'],
  ];
  [$pageEyebrow, $pageTitle, $pageDescription] = $pageTitles[$page] ?? ['Workspace', 'Cashier', 'Cashier workspace.'];
@endphp

@section('title', $pageTitle . ' - Cashier')
@section('page-eyebrow', $pageEyebrow)
@section('page-title', $pageTitle)
@section('page-description', $pageDescription)
@section('page-icon', $page === 'products' ? 'bi bi-box-seam' : ($page === 'settings' ? 'bi bi-sliders' : ($page === 'dashboard' ? 'bi bi-speedometer2' : 'bi bi-receipt-cutoff')))
@section('page-actions')
  @if($page === 'dashboard')
    <a class="btn btn-primary btn-sm" href="{{ route('cashier.sales.create') }}"><i class="bi bi-cart-plus"></i> Make Order</a>
  @elseif($page === 'orders' || $page === 'receipts')
    <a class="btn btn-primary btn-sm" href="{{ route('cashier.sales.create') }}"><i class="bi bi-cart-plus"></i> New Order</a>
  @elseif($page === 'products')
    <a class="btn btn-outline-secondary btn-sm" href="{{ route('cashier.sales.create') }}"><i class="bi bi-cart-plus"></i> Make Order</a>
  @endif
@endsection

@section('content')
@if($page === 'dashboard')
<section class="row g-3 mt-1" aria-label="Cashier quick actions">
  <div class="col-12 col-sm-6 col-xl-4">
    <a href="{{ route('cashier.sales.create') }}" class="text-decoration-none">
      <article class="metric-card metric-warning">
        <div class="metric-top">
          <span class="metric-label">Make Orders</span>
          <span class="metric-icon"><i class="bi bi-cart-plus" aria-hidden="true"></i></span>
        </div>
        <div class="metric-value">Start Sale</div>
        <div class="metric-meta"><span class="text-success">Fast</span><span>order entry</span></div>
      </article>
    </a>
  </div>
  <div class="col-12 col-sm-6 col-xl-4">
    <a href="{{ route('cashier.pages.show', 'payments') }}" class="text-decoration-none">
      <article class="metric-card metric-primary">
        <div class="metric-top">
          <span class="metric-label">Complete Orders</span>
          <span class="metric-icon"><i class="bi bi-credit-card" aria-hidden="true"></i></span>
        </div>
        <div class="metric-value">Payments</div>
        <div class="metric-meta"><span class="text-success">Checkout</span><span>ready</span></div>
      </article>
    </a>
  </div>
  <div class="col-12 col-sm-6 col-xl-4">
    <a href="{{ route('cashier.pages.show', 'receipts') }}" class="text-decoration-none">
      <article class="metric-card metric-success">
        <div class="metric-top">
          <span class="metric-label">Receipts</span>
          <span class="metric-icon"><i class="bi bi-printer" aria-hidden="true"></i></span>
        </div>
        <div class="metric-value">Print</div>
        <div class="metric-meta"><span class="text-success">Recent</span><span>transactions</span></div>
      </article>
    </a>
  </div>
</section>

<section class="row g-3 mt-1">
  <div class="col-12 col-xl-8">
    <div class="panel">
      <div class="panel-header">
        <div>
          <h2 class="h5 mb-1 section-title"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i><span>Workspace Summary</span></h2>
          <p class="text-muted mb-0">Daily operational data for the cashier station.</p>
        </div>
      </div>
      <div class="row g-3">
        <div class="col-md-4"><div class="mini-card"><span>Products</span><strong>{{ $stats['product_count'] }}</strong></div></div>
        <div class="col-md-4"><div class="mini-card"><span>Orders</span><strong>{{ $stats['order_count'] }}</strong></div></div>
        <div class="col-md-4"><div class="mini-card"><span>Sales</span><strong>{{ number_format($stats['sales_total'], 2) }}</strong></div></div>
      </div>
    </div>
  </div>

  <div class="col-12 col-xl-4">
    <div class="panel h-100">
      <div class="panel-header">
        <div>
          <h2 class="h5 mb-1 section-title"><i class="bi bi-lightning-charge" aria-hidden="true"></i><span>Quick Actions</span></h2>
          <p class="text-muted mb-0">Common cashier tasks.</p>
        </div>
      </div>
      <div class="d-grid gap-2">
        <a class="btn btn-primary" href="{{ route('cashier.sales.create') }}"><i class="bi bi-cart-plus"></i> Make Orders</a>
        <a class="btn btn-outline-secondary" href="{{ route('cashier.pages.show', 'payments') }}"><i class="bi bi-credit-card"></i> Open Payments</a>
        <a class="btn btn-outline-secondary" href="{{ route('cashier.pages.show', 'receipts') }}"><i class="bi bi-printer"></i> View Receipts</a>
      </div>
    </div>
  </div>
</section>
@endif

@if($page === 'settings')
<section class="row g-3 mt-1">
  <div class="col-12 col-xl-8">
    <div class="panel">
      <div class="panel-header">
        <div>
          <h2 class="h5 mb-1 section-title"><i class="bi bi-sliders" aria-hidden="true"></i><span>Cashier Settings</span></h2>
          <p class="text-muted mb-0">Update your login details and notification preference.</p>
        </div>
      </div>

      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif

      <form method="POST" action="{{ route('cashier.settings.update') }}" class="row g-3">
        @csrf
        @method('PUT')
        <div class="col-md-6">
          <label class="form-label">Full Name</label>
          <input name="name" value="{{ old('name', auth()->user()->name) }}" class="form-control" required>
          @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <label class="form-label">Username</label>
          <input name="username" value="{{ old('username', auth()->user()->username) }}" class="form-control" required autocomplete="username">
          @error('username')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
          <label class="form-label">Email <span class="text-muted">(optional)</span></label>
          <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" class="form-control" autocomplete="email">
          @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <label class="form-label">New Password</label>
          <input type="password" name="password" class="form-control" minlength="6" autocomplete="new-password">
          <div class="form-text">Leave blank to keep your current password.</div>
          @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <label class="form-label">Confirm New Password</label>
          <input type="password" name="password_confirmation" class="form-control" minlength="6" autocomplete="new-password">
        </div>
        <div class="col-12">
          <input type="hidden" name="email_notifications_enabled" value="0">
          <label class="form-check d-flex align-items-start gap-2">
            <input class="form-check-input mt-1" type="checkbox" name="email_notifications_enabled" value="1" {{ old('email_notifications_enabled', auth()->user()->email_notifications_enabled ? '1' : '0') === '1' ? 'checked' : '' }}>
            <span>
              <strong class="d-block">Email notifications</strong>
              <small class="text-muted">Receive email alerts for branch online orders. In-app notifications still appear in the cashier panel.</small>
            </span>
          </label>
        </div>
        <div class="col-12">
          <button class="btn btn-primary"><i class="bi bi-check2-circle"></i> Save Settings</button>
        </div>
      </form>
    </div>
  </div>
</section>
@else
<section class="row g-3 mt-1">
  <div class="col-12">
    @php
      $isProductPage = $page === 'products';
      $isPaymentPage = in_array($page, ['payments', 'payments-reports'], true);
      $isReceiptPage = $page === 'receipts';
      $rows = $isProductPage ? $products : ($isPaymentPage ? $payments : $orders);
    @endphp
    <div class="card shadow entity-card">
      <div class="card-header border-0 entity-toolbar">
        <div>
          <div class="d-flex align-items-center gap-2">
            <span class="page-icon"><i class="bi {{ $isProductPage ? 'bi-box-seam' : ($isPaymentPage ? 'bi-wallet2' : ($isReceiptPage ? 'bi-receipt-cutoff' : 'bi-cart-check')) }}"></i></span>
            <div>
              <strong>{{ $pageTitle }}</strong>
              <span>{{ $rows->count() }} recent {{ \Illuminate\Support\Str::lower($pageTitle) }} shown</span>
            </div>
          </div>
        </div>
        <div class="entity-filter-wrap">
          <i class="bi bi-search"></i>
          <input type="search" class="form-control form-control-sm entity-filter" placeholder="Search {{ \Illuminate\Support\Str::lower($pageTitle) }}" aria-label="Filter {{ $pageTitle }}" data-table-filter="#cashier-data-table">
        </div>
      </div>
      <div class="table-responsive">
        <table class="table align-items-center table-flush" id="cashier-data-table">
          @if($isProductPage)
            <thead><tr><th>Product</th><th>Code</th><th>Price</th><th>Stock</th></tr></thead>
            <tbody>
              @forelse($products as $product)
                <tr data-filter-row><td><strong>{{ $product->name }}</strong><small class="d-block text-muted">{{ $product->description ?: 'No description' }}</small></td><td><span class="text-muted">{{ $product->code }}</span></td><td><strong>{{ number_format($product->price, 2) }}</strong></td><td><span class="badge {{ $product->stock > 0 ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $product->stock }} in stock</span></td></tr>
              @empty
                <tr><td colspan="4" class="text-center py-5 text-muted">No products available.</td></tr>
              @endforelse
            </tbody>
          @elseif($isPaymentPage)
            <thead><tr><th>Payment</th><th>Order</th><th>Method</th><th>Amount</th><th>Date</th></tr></thead>
            <tbody>
              @forelse($payments as $payment)
                <tr data-filter-row><td><strong>#{{ $payment->id }}</strong></td><td>#{{ $payment->order_id }}</td><td><span class="badge text-bg-light">{{ $payment->method }}</span></td><td><strong>{{ number_format($payment->amount, 2) }}</strong></td><td>{{ \Illuminate\Support\Carbon::parse($payment->created_at)->format('d M Y, h:i A') }}</td></tr>
              @empty
                <tr><td colspan="5" class="text-center py-5 text-muted">No payments recorded yet.</td></tr>
              @endforelse
            </tbody>
          @else
            <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th><th></th></tr></thead>
            <tbody>
              @forelse($orders as $order)
                <tr data-filter-row><td><a href="{{ route('cashier.receipts.show', $order->id) }}"><strong>{{ $order->code }}</strong></a></td><td>{{ $order->customer_name ?: 'Walk-in' }}</td><td><strong>{{ number_format($order->grand_total, 2) }}</strong></td><td><span class="badge text-bg-success">{{ ucfirst($order->status) }}</span></td><td>{{ \Illuminate\Support\Carbon::parse($order->created_at)->format('d M Y, h:i A') }}</td><td class="text-end"><a class="btn btn-outline-secondary btn-sm" href="{{ route('cashier.receipts.show', $order->id) }}" title="View receipt" aria-label="View receipt"><i class="bi bi-eye"></i></a></td></tr>
              @empty
                <tr><td colspan="6" class="text-center py-5 text-muted">No orders recorded yet.</td></tr>
              @endforelse
            </tbody>
          @endif
        </table>
      </div>
    </div>
  </div>
</section>
@endif
@endsection
