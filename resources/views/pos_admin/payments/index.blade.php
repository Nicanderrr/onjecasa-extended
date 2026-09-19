@extends('layouts.pos-admin')

@section('title', 'Payments - ONJECASA POS')
@section('page-eyebrow', 'Sales')
@section('page-title', 'Payments')
@section('page-description', 'Track collected money, payment channels, and linked orders from one ledger.')
@section('page-actions')
  <a href="{{ route('pos.admin.orders.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-receipt"></i> Orders</a>
  <a href="{{ route('pos.admin.orders.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> New Order</a>
@endsection

@section('content')
@php
  $latestPaymentAt = $summary['latest_payment_at']
    ? \Illuminate\Support\Carbon::parse($summary['latest_payment_at'])
    : null;
@endphp

<section class="row g-2 dashboard-metrics entity-metrics payments-metrics" aria-label="Payments summary">
  <div class="col-12 col-md-6 col-xl-3">
    <article class="metric-card">
      <div class="metric-top"><span class="metric-label">Payments</span><span class="metric-icon"><i class="bi bi-wallet2"></i></span></div>
      <div class="metric-value">{{ number_format($summary['payment_count']) }}</div>
      <div class="metric-meta"><span class="text-success">All time</span><span>records</span></div>
    </article>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <article class="metric-card">
      <div class="metric-top"><span class="metric-label">Total Collected</span><span class="metric-icon"><i class="bi bi-cash-stack"></i></span></div>
      <div class="metric-value">{{ number_format($summary['total_collected'], 2) }}</div>
      <div class="metric-meta"><span class="text-primary">Gross cash flow</span><span>sales ledger</span></div>
    </article>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <article class="metric-card">
      <div class="metric-top"><span class="metric-label">Cash Collected</span><span class="metric-icon"><i class="bi bi-cash"></i></span></div>
      <div class="metric-value">{{ number_format($summary['cash_total'], 2) }}</div>
      <div class="metric-meta"><span class="text-success">Cash desk</span><span>walk-in payments</span></div>
    </article>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <article class="metric-card">
      <div class="metric-top"><span class="metric-label">Mobile Money</span><span class="metric-icon"><i class="bi bi-phone"></i></span></div>
      <div class="metric-value">{{ number_format($summary['mobile_total'], 2) }}</div>
      <div class="metric-meta"><span class="text-info">Paystack</span><span>mobile channel</span></div>
    </article>
  </div>
</section>

<div class="card shadow entity-card payments-card mt-3">
  <div class="card-header border-0 entity-toolbar payments-toolbar">
    <div class="payments-table-heading">
      <span class="payments-table-icon"><i class="bi bi-receipt-cutoff"></i></span>
      <div>
        <strong>Payment ledger</strong>
        <span>
          {{ $payments->total() }} {{ \Illuminate\Support\Str::plural('payment', $payments->total()) }} recorded
          @if($latestPaymentAt)
            · latest {{ $latestPaymentAt->format('d M Y, h:i A') }}
          @endif
        </span>
      </div>
    </div>
    <div class="entity-filter-wrap">
      <i class="bi bi-search"></i>
      <input type="search" class="form-control form-control-sm entity-filter" placeholder="Search payments" aria-label="Filter payments" data-table-filter="#payments-table">
    </div>
  </div>

  <div class="table-responsive">
    <table class="table align-items-center table-flush payments-table" id="payments-table">
      <thead class="thead-light">
        <tr>
          <th>Payment</th>
          <th>Order</th>
          <th>Customer</th>
          <th>Cashier</th>
          <th>Method</th>
          <th>Amount</th>
          <th>Reference</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        @forelse($payments as $payment)
          @php
            $methodKey = \Illuminate\Support\Str::lower($payment->method ?: 'cash');
            $isMobileMoney = str_contains($methodKey, 'mobile');
            $customerName = $payment->customer_name ?: 'Walk-in';
            $customerInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($customerName, 0, 1));
            $reference = $payment->paystack_reference ?: '—';
            $orderStatus = \Illuminate\Support\Str::slug($payment->order_status ?: 'paid');
          @endphp
          <tr data-filter-row>
            <td class="payment-cell">
              <div class="payment-identity">
                <span class="payment-symbol"><i class="bi bi-credit-card-2-front"></i></span>
                <div>
                  <strong>#{{ $payment->id }}</strong>
                  <span>Order #{{ $payment->order_id }}</span>
                </div>
              </div>
            </td>
            <td>
              <div class="payment-order">
                <a href="{{ route('pos.admin.orders.show', $payment->order_id) }}">{{ $payment->order_code }}</a>
                <span class="order-status order-status-{{ $orderStatus }}"><span></span>{{ ucfirst($payment->order_status ?: 'paid') }}</span>
              </div>
            </td>
            <td>
              <div class="payment-customer">
                <span class="customer-initial">{{ $customerInitial }}</span>
                <strong>{{ $customerName }}</strong>
              </div>
            </td>
            <td>
              <span class="payment-cashier">
                <i class="bi bi-person-badge"></i>
                {{ $payment->cashier_name ?: 'Unknown' }}
              </span>
            </td>
            <td>
              <span class="payment-method {{ $isMobileMoney ? 'mobile-money' : 'cash' }}">
                <i class="bi {{ $isMobileMoney ? 'bi-phone' : 'bi-cash' }}"></i>
                {{ $payment->method }}
              </span>
            </td>
            <td><span class="payment-amount">{{ number_format($payment->amount, 2) }}</span></td>
            <td><span class="payment-reference">{{ $reference }}</span></td>
            <td>
              <div class="payment-date">
                <strong>{{ \Illuminate\Support\Carbon::parse($payment->created_at)->format('d M Y') }}</strong>
                <span>{{ \Illuminate\Support\Carbon::parse($payment->created_at)->format('h:i A') }}</span>
              </div>
            </td>
          </tr>
        @empty
          <tr data-filter-empty>
            <td colspan="8" class="text-center py-5 text-muted">No payments recorded yet.</td>
          </tr>
        @endforelse
        @if($payments->count())
          <tr data-filter-empty style="display:none;">
            <td colspan="8" class="text-center py-5 text-muted">No matching payments found.</td>
          </tr>
        @endif
      </tbody>
    </table>
  </div>

  @if($payments->hasPages())
    <div class="payments-pagination">{{ $payments->links() }}</div>
  @endif
</div>
@endsection

