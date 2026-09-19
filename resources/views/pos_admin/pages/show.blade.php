@extends('layouts.pos-admin')

@section('content')
@if($page === 'dashboard')
<div class="row">
  <div class="col-xl-4 col-lg-6">
    <div class="card card-stats mb-4 mb-xl-0"><div class="card-body"><div class="row"><div class="col"><h5 class="card-title text-uppercase text-muted mb-0">Products</h5><span class="h2 font-weight-bold mb-0">{{ $stats['product_count'] }}</span></div><div class="col-auto"><div class="icon icon-shape bg-primary text-white rounded-circle shadow"><i class="fas fa-utensils"></i></div></div></div></div></div>
  </div>
  <div class="col-xl-4 col-lg-6">
    <div class="card card-stats mb-4 mb-xl-0"><div class="card-body"><div class="row"><div class="col"><h5 class="card-title text-uppercase text-muted mb-0">Orders</h5><span class="h2 font-weight-bold mb-0">{{ $stats['order_count'] }}</span></div><div class="col-auto"><div class="icon icon-shape bg-warning text-white rounded-circle shadow"><i class="fas fa-shopping-cart"></i></div></div></div></div></div>
  </div>
  <div class="col-xl-4 col-lg-6">
    <div class="card card-stats mb-4 mb-xl-0"><div class="card-body"><div class="row"><div class="col"><h5 class="card-title text-uppercase text-muted mb-0">Sales</h5><span class="h2 font-weight-bold mb-0">{{ number_format($stats['sales_total'],2) }}</span></div><div class="col-auto"><div class="icon icon-shape bg-green text-white rounded-circle shadow"><i class="fas fa-dollar-sign"></i></div></div></div></div></div>
  </div>
</div>
@endif

<div class="row mt-5">
  <div class="col-xl-12 mb-5 mb-xl-0">
    <div class="card shadow">
      <div class="card-header border-0">
        <div class="row align-items-center">
          <div class="col"><h3 class="mb-0 text-capitalize">{{ str_replace('-', ' ', $page) }}</h3></div>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table align-items-center table-flush">
          @if(in_array($page, ['products','categories']))
          <thead class="thead-light"><tr><th>Code</th><th>Name</th><th>Price</th><th>Stock</th></tr></thead>
          <tbody>@foreach($products as $p)<tr><td>{{ $p->code }}</td><td>{{ $p->name }}</td><td>{{ number_format($p->price,2) }}</td><td>{{ $p->stock }}</td></tr>@endforeach</tbody>
          @elseif(in_array($page, ['payments','payments-reports','receipts','sales']))
          <thead class="thead-light"><tr><th>#</th><th>Order</th><th>Method</th><th>Amount</th><th>Date</th></tr></thead>
          <tbody>@foreach($payments as $pay)<tr><td>{{ $pay->id }}</td><td>#{{ $pay->order_id }}</td><td>{{ $pay->method }}</td><td>{{ number_format($pay->amount,2) }}</td><td>{{ $pay->created_at }}</td></tr>@endforeach</tbody>
          @else
          <thead class="thead-light"><tr><th>Code</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
          <tbody>@foreach($orders as $order)<tr><td>{{ $order->code }}</td><td>{{ $order->customer_name }}</td><td>{{ number_format($order->grand_total,2) }}</td><td><span class="badge badge-success">{{ $order->status }}</span></td><td>{{ $order->created_at }}</td></tr>@endforeach</tbody>
          @endif
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
