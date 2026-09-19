@extends('layouts.pos-admin')
@section('content')
<div class="card shadow">
  <div class="card-header border-0"><h3>Order {{ $order->code }}</h3></div>
  <div class="card-body">
    <p><b>Customer:</b> {{ $order->customer_name }}</p>
    <p><b>Total:</b> {{ number_format($order->grand_total,2) }}</p>
    <p><b>Payment Method:</b> {{ $payment->method ?? 'N/A' }}</p>
    @if(($payment->method ?? '') === 'Mobile Money' && !empty($payment->paystack_reference))
      <p><span class="badge badge-success">Paystack payment successful</span></p>
      <p><b>Reference:</b> {{ $payment->paystack_reference }}</p>
    @endif
    <div class="table-responsive">
      <table class="table align-items-center table-flush">
        <thead class="thead-light"><tr><th>Product</th><th>Qty</th><th>Price</th><th>Extras</th><th>Total</th></tr></thead>
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
</div>
@endsection

