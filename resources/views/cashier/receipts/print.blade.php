<!doctype html>
<html lang="en">
@php($brandLogoUrl = \App\Support\BrandAssets::logoUrl())
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Receipt {{ $order->code }}</title>
  <style>
    * { box-sizing: border-box; }
    body { margin: 0; background: #eef3ef; font-family: "Segoe UI", Arial, sans-serif; color: #111827; }
    .sheet { width: 210mm; min-height: 297mm; margin: 0 auto; background: #fff; padding: 16mm 14mm; }
    .card { border: 1px solid #d8e4db; border-radius: 14px; overflow: hidden; }
    .hero { background: linear-gradient(135deg, #166534, #16a34a); color: #fff; padding: 16px 18px; display: flex; justify-content: space-between; align-items: flex-start; }
    .brand { display: flex; gap: 10px; align-items: center; }
    .logo { width: 42px; height: 42px; border-radius: 10px; background: rgba(255,255,255,.16); display: grid; place-items: center; overflow: hidden; }
    .logo img { width: 100%; height: 100%; object-fit: contain; padding: 4px; }
    .hero h1 { margin: 0; font-size: 20px; letter-spacing: .02em; }
    .hero p { margin: 3px 0 0; font-size: 12px; opacity: .9; }
    .status { background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.32); border-radius: 999px; padding: 6px 10px; font-size: 11px; font-weight: 700; }
    .meta { padding: 14px 18px 8px; display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .meta .box { border: 1px solid #e5ece7; border-radius: 10px; padding: 10px 12px; }
    .k { font-size: 11px; color: #6b7280; margin-bottom: 4px; }
    .v { font-size: 13px; font-weight: 600; color: #111827; }
    table { width: calc(100% - 36px); margin: 8px 18px 0; border-collapse: collapse; }
    th { font-size: 11px; text-transform: uppercase; color: #4b5563; border-bottom: 2px solid #dbe7df; padding: 10px 8px; text-align: left; }
    td { font-size: 13px; border-bottom: 1px solid #edf2ee; padding: 10px 8px; }
    .extras { margin-top: 3px; color: #6b7280; font-size: 11px; line-height: 1.35; }
    tbody tr:nth-child(even) { background: #fafcfb; }
    .num { text-align: right; white-space: nowrap; }
    .foot { display: flex; justify-content: space-between; gap: 16px; padding: 12px 18px 16px; align-items: flex-end; }
    .note { color: #6b7280; font-size: 12px; max-width: 58%; }
    .sum { min-width: 220px; border: 1px solid #dfe9e2; border-radius: 10px; padding: 10px 12px; }
    .row { display: flex; justify-content: space-between; font-size: 13px; padding: 4px 0; }
    .row.total { border-top: 1px dashed #cfe0d5; margin-top: 6px; padding-top: 8px; font-size: 16px; font-weight: 800; color: #14532d; }
    .actions { width: 210mm; margin: 12px auto 20px; text-align: right; }
    .btn { border: 0; border-radius: 10px; padding: 10px 14px; background: #166534; color: #fff; cursor: pointer; font-weight: 700; }
    .tiny { margin-top: 10px; font-size: 10px; color: #9ca3af; text-align: center; }
    @media print {
      body { background: #fff; }
      .sheet { width: auto; min-height: auto; margin: 0; padding: 0; }
      .actions { display: none; }
      @page { size: A4; margin: 8mm; }
    }
  </style>
</head>
<body>
  <div class="actions">
    <button class="btn" onclick="window.print()">Print Receipt</button>
  </div>
  <div class="sheet">
    <div class="card">
      <div class="hero">
        <div class="brand">
          <div class="logo"><img src="{{ $brandLogoUrl }}" alt="ONJECASA"></div>
          <div>
            <h1>Payment Receipt</h1>
            <p>ONJECASA POS Transaction Record</p>
          </div>
        </div>
        <div class="status">PAID</div>
      </div>

      <div class="meta">
        <div class="box"><div class="k">Receipt Code</div><div class="v">{{ $order->code }}</div></div>
        <div class="box"><div class="k">Date</div><div class="v">{{ $order->created_at }}</div></div>
        <div class="box"><div class="k">Customer</div><div class="v">{{ $order->customer_name }}</div></div>
        <div class="box"><div class="k">Payment Method</div><div class="v">{{ $payment->method ?? 'N/A' }}</div></div>
      </div>

      <table>
        <thead><tr><th>Item</th><th class="num">Qty</th><th class="num">Unit Price</th><th class="num">Amount</th></tr></thead>
        <tbody>
          @foreach($items as $i)
          <tr>
            <td>
              {{ $i->product_name }}
              @if(\App\Support\MealExtras::label($i->extras ?? null))
                <div class="extras">{{ \App\Support\MealExtras::label($i->extras ?? null) }}</div>
              @endif
            </td>
            <td class="num">{{ $i->qty }}</td>
            <td class="num">{{ number_format($i->price, 2) }}</td>
            <td class="num">{{ number_format($i->total, 2) }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>

      <div class="foot">
        <div class="note">Thank you for your purchase.</div>
        <div class="sum">
          <div class="row"><span>Subtotal</span><span>{{ number_format($order->grand_total, 2) }}</span></div>
          <div class="row"><span>Tax</span><span>0.00</span></div>
          <div class="row total"><span>Total</span><span>{{ number_format($order->grand_total, 2) }}</span></div>
        </div>
      </div>
    </div>
    <div class="tiny">Generated by ONJECASA POS</div>
  </div>

  @if(request()->boolean('autoprint'))
  <script>window.addEventListener('load', function(){ window.print(); });</script>
  @endif
</body>
</html>


