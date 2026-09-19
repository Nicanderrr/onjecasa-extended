@extends('layouts.pos-admin')

@section('title', 'Receipts - ONJECASA POS')
@section('page-eyebrow', 'Sales')
@section('page-title', 'Receipts')
@section('page-description', 'Review issued receipts, payment channels, and the cashiers behind each sale.')
@section('page-actions')
  <a href="{{ route('pos.admin.payments.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-wallet2"></i> Payments</a>
  <a href="{{ route('pos.admin.orders.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> New Order</a>
@endsection

@section('content')
@php
  $latestReceiptAt = $summary['latest_receipt_at']
    ? \Illuminate\Support\Carbon::parse($summary['latest_receipt_at'])
    : null;
@endphp

<section class="row g-2 dashboard-metrics entity-metrics receipts-metrics" aria-label="Receipts summary">
  <div class="col-12 col-md-6 col-xl-3">
    <article class="metric-card">
      <div class="metric-top"><span class="metric-label">Receipts</span><span class="metric-icon"><i class="bi bi-receipt-cutoff"></i></span></div>
      <div class="metric-value">{{ number_format($summary['receipt_count']) }}</div>
      <div class="metric-meta"><span class="text-success">All time</span><span>issued receipts</span></div>
    </article>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <article class="metric-card">
      <div class="metric-top"><span class="metric-label">Total Value</span><span class="metric-icon"><i class="bi bi-cash-stack"></i></span></div>
      <div class="metric-value">{{ number_format($summary['total_value'], 2) }}</div>
      <div class="metric-meta"><span class="text-primary">Sales ledger</span><span>paid orders</span></div>
    </article>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <article class="metric-card">
      <div class="metric-top"><span class="metric-label">Today</span><span class="metric-icon"><i class="bi bi-graph-up-arrow"></i></span></div>
      <div class="metric-value">{{ number_format($summary['today_value'], 2) }}</div>
      <div class="metric-meta"><span class="text-info">Received today</span><span>cash flow</span></div>
    </article>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <article class="metric-card">
      <div class="metric-top"><span class="metric-label">Latest Receipt</span><span class="metric-icon"><i class="bi bi-clock-history"></i></span></div>
      <div class="metric-value">{{ $latestReceiptAt ? $latestReceiptAt->format('d M') : '—' }}</div>
      <div class="metric-meta"><span class="text-warning">{{ $latestReceiptAt ? $latestReceiptAt->format('h:i A') : 'No activity' }}</span><span>last issued</span></div>
    </article>
  </div>
</section>

<div class="card shadow entity-card receipts-card mt-3">
  <div class="card-header border-0 entity-toolbar receipts-toolbar">
    <div class="receipts-table-heading">
      <span class="receipts-table-icon"><i class="bi bi-receipt"></i></span>
      <div>
        <strong>Receipt register</strong>
        <span>
          {{ $receipts->total() }} {{ \Illuminate\Support\Str::plural('receipt', $receipts->total()) }} recorded
          @if($latestReceiptAt)
            · latest {{ $latestReceiptAt->format('d M Y, h:i A') }}
          @endif
        </span>
      </div>
    </div>
    <div class="entity-filter-wrap">
      <i class="bi bi-search"></i>
      <input type="search" class="form-control form-control-sm entity-filter" placeholder="Search receipts" aria-label="Filter receipts" data-table-filter="#receipts-table">
    </div>
  </div>

  <div class="table-responsive">
    <table class="table align-items-center table-flush receipts-table" id="receipts-table">
      <thead class="thead-light">
        <tr>
          <th>Receipt</th>
          <th>Order</th>
          <th>Customer</th>
          <th>Cashier</th>
          <th>Method</th>
          <th>Amount</th>
          <th>Reference</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($receipts as $receipt)
          @php
            $methodKey = \Illuminate\Support\Str::lower($receipt->method ?: 'cash');
            $isMobileMoney = str_contains($methodKey, 'mobile');
            $customerName = $receipt->customer_name ?: 'Walk-in';
            $customerInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($customerName, 0, 1));
            $reference = $receipt->paystack_reference ?: '—';
            $statusKey = \Illuminate\Support\Str::slug($receipt->status ?: 'paid');
          @endphp
          <tr data-filter-row>
            <td class="receipt-cell">
              <div class="receipt-identity">
                <span class="receipt-symbol"><i class="bi bi-file-earmark-text"></i></span>
                <div>
                  <strong>#{{ $receipt->id }}</strong>
                  <span>{{ \Illuminate\Support\Carbon::parse($receipt->created_at)->format('d M Y') }}</span>
                </div>
              </div>
            </td>
            <td>
              <div class="receipt-order">
                <a href="{{ route('pos.admin.receipts.show', $receipt->id) }}">{{ $receipt->code }}</a>
                <span class="order-status order-status-{{ $statusKey }}"><span></span>{{ ucfirst($receipt->status ?: 'paid') }}</span>
              </div>
            </td>
            <td>
              <div class="receipt-customer">
                <span class="customer-initial">{{ $customerInitial }}</span>
                <strong>{{ $customerName }}</strong>
              </div>
            </td>
            <td>
              <span class="receipt-cashier">
                <i class="bi bi-person-badge"></i>
                {{ $receipt->cashier_name ?: 'Unknown' }}
              </span>
            </td>
            <td>
              <span class="payment-method {{ $isMobileMoney ? 'mobile-money' : 'cash' }}">
                <i class="bi {{ $isMobileMoney ? 'bi-phone' : 'bi-cash' }}"></i>
                {{ $receipt->method }}
              </span>
            </td>
            <td><span class="payment-amount">{{ number_format($receipt->grand_total, 2) }}</span></td>
            <td><span class="payment-reference">{{ $reference }}</span></td>
            <td>
              <div class="receipt-actions">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('pos.admin.receipts.show', $receipt->id) }}"><i class="bi bi-eye"></i> Open</a>
                <a class="btn btn-sm btn-outline-success" href="{{ route('pos.admin.receipts.print', $receipt->id) }}" target="_blank"><i class="bi bi-printer"></i></a>
              </div>
            </td>
          </tr>
        @empty
          <tr data-filter-empty>
            <td colspan="8" class="text-center py-5 text-muted">No receipts recorded yet.</td>
          </tr>
        @endforelse
        @if($receipts->count())
          <tr data-filter-empty style="display:none;">
            <td colspan="8" class="text-center py-5 text-muted">No matching receipts found.</td>
          </tr>
        @endif
      </tbody>
    </table>
  </div>

  @if($receipts->hasPages())
    <div class="receipts-pagination">{{ $receipts->links() }}</div>
  @endif
</div>
@endsection

