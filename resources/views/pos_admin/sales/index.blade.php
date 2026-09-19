@extends('layouts.pos-admin')

@section('title', 'Sales - ONJECASA POS')
@section('page-eyebrow', 'Reports')
@section('page-title', 'Sales')
@section('page-description', 'Review sales totals, cashiers, payment methods, and receipt-linked orders.')
@section('page-actions')
  <a href="{{ route('pos.admin.payments.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-wallet2"></i> Payments</a>
  <a href="{{ route('pos.admin.orders.index') }}" class="btn btn-primary btn-sm"><i class="bi bi-receipt-cutoff"></i> Orders</a>
@endsection

@section('content')
@php
  $latestSaleAt = $summary['latest_sale_at']
    ? \Illuminate\Support\Carbon::parse($summary['latest_sale_at'])
    : null;
@endphp

<section class="row g-2 dashboard-metrics entity-metrics sales-metrics" aria-label="Sales summary">
  <div class="col-12 col-md-6 col-xl-3">
    <article class="metric-card">
      <div class="metric-top"><span class="metric-label">Sales</span><span class="metric-icon"><i class="bi bi-graph-up-arrow"></i></span></div>
      <div class="metric-value">{{ number_format($summary['sale_count']) }}</div>
      <div class="metric-meta"><span class="text-success">All time</span><span>sale records</span></div>
    </article>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <article class="metric-card">
      <div class="metric-top"><span class="metric-label">Total Sales</span><span class="metric-icon"><i class="bi bi-cash-stack"></i></span></div>
      <div class="metric-value">{{ number_format($summary['total_sales'], 2) }}</div>
      <div class="metric-meta"><span class="text-primary">Gross revenue</span><span>paid orders</span></div>
    </article>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <article class="metric-card">
      <div class="metric-top"><span class="metric-label">Today</span><span class="metric-icon"><i class="bi bi-calendar-day"></i></span></div>
      <div class="metric-value">{{ number_format($summary['today_sales'], 2) }}</div>
      <div class="metric-meta"><span class="text-info">Current day</span><span>sales value</span></div>
    </article>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <article class="metric-card">
      <div class="metric-top"><span class="metric-label">Average Sale</span><span class="metric-icon"><i class="bi bi-calculator"></i></span></div>
      <div class="metric-value">{{ number_format($summary['average_sale'], 2) }}</div>
      <div class="metric-meta"><span class="text-warning">{{ $latestSaleAt ? $latestSaleAt->format('d M') : 'No activity' }}</span><span>latest {{ $latestSaleAt ? $latestSaleAt->format('h:i A') : '' }}</span></div>
    </article>
  </div>
</section>

<div class="card shadow entity-card sales-card mt-3">
  <div class="card-header border-0 entity-toolbar sales-toolbar">
    <div class="sales-table-heading">
      <span class="sales-table-icon"><i class="bi bi-receipt-cutoff"></i></span>
      <div>
        <strong>Sales ledger</strong>
        <span>
          {{ $sales->total() }} {{ \Illuminate\Support\Str::plural('sale', $sales->total()) }} recorded
          @if($latestSaleAt)
            · latest {{ $latestSaleAt->format('d M Y, h:i A') }}
          @endif
        </span>
      </div>
    </div>
    <div class="entity-filter-wrap">
      <i class="bi bi-search"></i>
      <input type="search" class="form-control form-control-sm entity-filter" placeholder="Search sales" aria-label="Filter sales" data-table-filter="#sales-table">
    </div>
  </div>

  <div class="table-responsive">
    <table class="table align-items-center table-flush sales-table" id="sales-table">
      <thead class="thead-light">
        <tr>
          <th>Sale</th>
          <th>Order</th>
          <th>Customer</th>
          <th>Cashier</th>
          <th>Method</th>
          <th>Items</th>
          <th>Total</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        @forelse($sales as $sale)
          @php
            $methodKey = \Illuminate\Support\Str::lower($sale->method ?: 'cash');
            $isMobileMoney = str_contains($methodKey, 'mobile');
            $customerName = $sale->customer_name ?: 'Walk-in';
            $customerInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($customerName, 0, 1));
            $itemCount = (int) ($sale->item_count ?? 0);
            $statusKey = \Illuminate\Support\Str::slug($sale->status ?: 'paid');
          @endphp
          <tr data-filter-row>
            <td class="sale-cell">
              <div class="sale-identity">
                <span class="sale-symbol"><i class="bi bi-activity"></i></span>
                <div>
                  <strong>#{{ $sale->id }}</strong>
                  <span>{{ \Illuminate\Support\Carbon::parse($sale->created_at)->format('d M Y') }}</span>
                </div>
              </div>
            </td>
            <td>
              <div class="sale-order">
                <a href="{{ route('pos.admin.orders.show', $sale->id) }}">{{ $sale->code }}</a>
                <span class="order-status order-status-{{ $statusKey }}"><span></span>{{ ucfirst($sale->status ?: 'paid') }}</span>
              </div>
            </td>
            <td>
              <div class="sale-customer">
                <span class="customer-initial">{{ $customerInitial }}</span>
                <strong>{{ $customerName }}</strong>
              </div>
            </td>
            <td>
              <span class="sale-cashier">
                <i class="bi bi-person-badge"></i>
                {{ $sale->cashier_name ?: 'Unknown' }}
              </span>
            </td>
            <td>
              <span class="payment-method {{ $isMobileMoney ? 'mobile-money' : 'cash' }}">
                <i class="bi {{ $isMobileMoney ? 'bi-phone' : 'bi-cash' }}"></i>
                {{ $sale->method }}
              </span>
            </td>
            <td><span class="sale-items">{{ $itemCount }} {{ \Illuminate\Support\Str::plural('item', $itemCount) }}</span></td>
            <td><span class="order-total">{{ number_format($sale->grand_total, 2) }}</span></td>
            <td>
              <div class="sale-date">
                <strong>{{ \Illuminate\Support\Carbon::parse($sale->created_at)->format('d M Y') }}</strong>
                <span>{{ \Illuminate\Support\Carbon::parse($sale->created_at)->format('h:i A') }}</span>
              </div>
            </td>
          </tr>
        @empty
          <tr data-filter-empty>
            <td colspan="8" class="text-center py-5 text-muted">No sales recorded yet.</td>
          </tr>
        @endforelse
        @if($sales->count())
          <tr data-filter-empty style="display:none;">
            <td colspan="8" class="text-center py-5 text-muted">No matching sales found.</td>
          </tr>
        @endif
      </tbody>
    </table>
  </div>

  @if($sales->hasPages())
    <div class="sales-pagination">{{ $sales->links() }}</div>
  @endif
</div>
@endsection

