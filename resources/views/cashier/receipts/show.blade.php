@extends('layouts.cashier')
@section('content')
<div class="card shadow">
  <div class="card-header border-0 d-flex justify-content-between align-items-center">
    <h3 class="mb-0">Receipt {{ $order->code }}</h3>
    <a href="{{ route('cashier.receipts.print', $order->id) }}" target="_blank" class="btn btn-sm btn-success">
      <i class="fas fa-print"></i> Print Report
    </a>
  </div>
  <div class="card-body">
    <p><b>Customer:</b> {{ $order->customer_name }}</p>
    <p><b>Total:</b> {{ number_format($order->grand_total,2) }}</p>
    <p><b>Method:</b> {{ $payment->method ?? 'N/A' }}</p>
    <hr>
    <table class="table table-sm">
      <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Extras</th><th>Total</th></tr></thead>
      <tbody>
        @foreach($items as $i)
          <tr>
            <td>{{ $i->product_name }}</td>
            <td>{{ $i->qty }}</td>
            <td>{{ number_format($i->price,2) }}</td>
            <td>{{ \App\Support\MealExtras::label($i->extras ?? null) ?: '-' }}</td>
            <td>{{ number_format($i->total,2) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@if(request()->boolean('autoprint'))
<script>
  window.addEventListener('load', function () {
    window.open(@json(route('cashier.receipts.print', $order->id) . '?autoprint=1'), '_blank');
  });
</script>
@endif
@endsection

