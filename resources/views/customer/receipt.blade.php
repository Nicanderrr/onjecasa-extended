<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Receipt {{ $order->code }} - ONJECASA</title>
  <link rel="stylesheet" href="{{ asset('assets/adminhmd/css/bootstrap.min.css') }}">
  <style>
    body { background:#f4f6f8; color:#17202a; } .receipt { max-width:720px; margin:2rem auto; }
    .receipt-card { background:#fff; border:1px solid #e1e5e9; border-radius:12px; box-shadow:0 16px 40px rgba(15,23,42,.08); }
    .receipt-total { font-size:1.5rem; font-weight:800; } @media print { body{background:#fff}.receipt{margin:0}.no-print{display:none!important}.receipt-card{box-shadow:none;border:0} }
  </style>
</head>
<body><main class="receipt px-3"><div class="receipt-card p-4">
  <div class="border-bottom pb-3 mb-3 d-flex justify-content-between gap-3">
    <div><h1 class="h4 mb-1">{{ $branch->name ?? 'ONJECASA' }}</h1><p class="text-muted mb-0">Customer receipt</p></div>
    <div class="text-end"><strong>{{ $order->code }}</strong><small class="d-block text-muted">{{ \Illuminate\Support\Carbon::parse($order->created_at)->format('d M Y, h:i A') }}</small></div>
  </div>
  <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Product</th><th class="text-center">Qty</th><th class="text-end">Amount</th></tr></thead><tbody>
    @foreach($items as $item)<tr><td>{{ $item->product_name }}@if($item->extras)<small class="d-block text-muted">{{ \App\Support\MealExtras::label($item->extras) }}</small>@endif</td><td class="text-center">{{ $item->qty }}</td><td class="text-end">GHS {{ number_format($item->total, 2) }}</td></tr>@endforeach
  </tbody></table></div>
  <div class="border-top pt-3 mt-3 d-flex justify-content-between"><span>Payment</span><strong>{{ $payment->method ?? 'Paid' }}</strong></div>
  <div class="d-flex justify-content-between align-items-center mt-2"><span>Total</span><span class="receipt-total">GHS {{ number_format($order->grand_total, 2) }}</span></div>
  <p class="text-muted small mt-4 mb-0">Thank you for shopping with us.</p>
  <button class="btn btn-dark w-100 mt-4 no-print" onclick="window.print()">Print receipt</button>
</div></main></body></html>
