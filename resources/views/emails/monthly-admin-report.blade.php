@php
  $money = fn ($value) => 'GHS '.number_format((float) $value, 2);
  $number = fn ($value) => number_format((float) $value);
@endphp
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Monthly Business Report</title>
</head>
<body style="margin:0;background:#f6f2ea;color:#25151b;font-family:Arial,Helvetica,sans-serif;">
  <div style="max-width:900px;margin:0 auto;padding:24px;">
    <div style="background:#2f9b55;color:#f4b63f;border-radius:10px;padding:24px;">
      <h1 style="margin:0 0 6px;font-size:28px;line-height:1.2;">Monthly Business Report</h1>
      <p style="margin:0;color:#fff5d6;">{{ $report['period_label'] }} | {{ $report['date_from'] }} to {{ $report['date_to'] }}</p>
    </div>

    <div style="background:#fff;border-radius:10px;margin-top:16px;padding:20px;border:1px solid #eadfcb;">
      <h2 style="margin:0 0 14px;font-size:18px;">Executive Summary</h2>
      <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
          <td style="padding:10px;border:1px solid #eadfcb;"><strong>Total Revenue</strong><br>{{ $money($report['totals']['total_revenue']) }}</td>
          <td style="padding:10px;border:1px solid #eadfcb;"><strong>POS Revenue</strong><br>{{ $money($report['totals']['pos_revenue']) }}</td>
          <td style="padding:10px;border:1px solid #eadfcb;"><strong>Online Revenue</strong><br>{{ $money($report['totals']['online_revenue']) }}</td>
        </tr>
        <tr>
          <td style="padding:10px;border:1px solid #eadfcb;"><strong>Total Orders</strong><br>{{ $number($report['totals']['total_orders']) }}</td>
          <td style="padding:10px;border:1px solid #eadfcb;"><strong>Average Order</strong><br>{{ $money($report['totals']['average_order_value']) }}</td>
          <td style="padding:10px;border:1px solid #eadfcb;"><strong>Units Sold</strong><br>{{ $number($report['totals']['pos_units'] + $report['totals']['online_units']) }}</td>
        </tr>
      </table>
    </div>

    <div style="background:#fff;border-radius:10px;margin-top:16px;padding:20px;border:1px solid #eadfcb;">
      <h2 style="margin:0 0 14px;font-size:18px;">Highest Performing POS Products</h2>
      @include('emails.partials.report-table', ['rows' => $report['top_pos_products'], 'empty' => 'No POS product sales for this month.'])
    </div>

    <div style="background:#fff;border-radius:10px;margin-top:16px;padding:20px;border:1px solid #eadfcb;">
      <h2 style="margin:0 0 14px;font-size:18px;">Highest Performing Website Products</h2>
      @include('emails.partials.report-table', ['rows' => $report['top_online_products'], 'empty' => 'No website product sales for this month.'])
    </div>

    <div style="background:#fff;border-radius:10px;margin-top:16px;padding:20px;border:1px solid #eadfcb;">
      <h2 style="margin:0 0 14px;font-size:18px;">Branch Performance</h2>
      <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;">
        <thead>
          <tr>
            <th align="left" style="padding:9px;border-bottom:1px solid #eadfcb;">Branch</th>
            <th align="right" style="padding:9px;border-bottom:1px solid #eadfcb;">POS Orders</th>
            <th align="right" style="padding:9px;border-bottom:1px solid #eadfcb;">Online Orders</th>
            <th align="right" style="padding:9px;border-bottom:1px solid #eadfcb;">Revenue</th>
          </tr>
        </thead>
        <tbody>
          @forelse($report['branch_performance'] as $branch)
            <tr>
              <td style="padding:9px;border-bottom:1px solid #f2eadc;">{{ $branch->name }}</td>
              <td align="right" style="padding:9px;border-bottom:1px solid #f2eadc;">{{ $number($branch->pos_orders) }}</td>
              <td align="right" style="padding:9px;border-bottom:1px solid #f2eadc;">{{ $number($branch->online_orders) }}</td>
              <td align="right" style="padding:9px;border-bottom:1px solid #f2eadc;">{{ $money($branch->total_revenue) }}</td>
            </tr>
          @empty
            <tr><td colspan="4" style="padding:14px;color:#7b6b61;">No branch activity for this month.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div style="background:#fff;border-radius:10px;margin-top:16px;padding:20px;border:1px solid #eadfcb;">
      <h2 style="margin:0 0 14px;font-size:18px;">Payment And Fulfillment</h2>
      <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
          <td valign="top" width="50%" style="padding-right:10px;">
            <h3 style="margin:0 0 8px;font-size:15px;">POS Payment Methods</h3>
            @forelse($report['payment_methods'] as $method)
              <p style="margin:0 0 8px;">{{ ucfirst($method->method) }}: <strong>{{ $money($method->revenue) }}</strong> from {{ $number($method->payments) }} payment(s)</p>
            @empty
              <p style="margin:0;color:#7b6b61;">No POS payments.</p>
            @endforelse
          </td>
          <td valign="top" width="50%" style="padding-left:10px;">
            <h3 style="margin:0 0 8px;font-size:15px;">Website Fulfillment</h3>
            @forelse($report['fulfillment_methods'] as $method)
              <p style="margin:0 0 8px;">{{ ucfirst($method->method) }}: <strong>{{ $money($method->revenue) }}</strong> from {{ $number($method->orders) }} order(s)</p>
            @empty
              <p style="margin:0;color:#7b6b61;">No website fulfillment data.</p>
            @endforelse
          </td>
        </tr>
      </table>
    </div>

    <div style="background:#fff;border-radius:10px;margin-top:16px;padding:20px;border:1px solid #eadfcb;">
      <h2 style="margin:0 0 14px;font-size:18px;">Order Statuses</h2>
      <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
          <td valign="top" width="50%" style="padding-right:10px;">
            <h3 style="margin:0 0 8px;font-size:15px;">POS Orders</h3>
            @forelse($report['order_statuses']['pos'] as $status)
              <p style="margin:0 0 8px;">{{ ucfirst($status->status) }}: <strong>{{ $number($status->total) }}</strong></p>
            @empty
              <p style="margin:0;color:#7b6b61;">No POS orders.</p>
            @endforelse
          </td>
          <td valign="top" width="50%" style="padding-left:10px;">
            <h3 style="margin:0 0 8px;font-size:15px;">Website Orders</h3>
            @forelse($report['order_statuses']['online'] as $status)
              <p style="margin:0 0 8px;">{{ ucfirst($status->status) }}: <strong>{{ $number($status->total) }}</strong></p>
            @empty
              <p style="margin:0;color:#7b6b61;">No website orders.</p>
            @endforelse
          </td>
        </tr>
      </table>
    </div>

    <div style="background:#fff;border-radius:10px;margin-top:16px;padding:20px;border:1px solid #eadfcb;">
      <h2 style="margin:0 0 14px;font-size:18px;">Inventory Watch</h2>
      <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
          <td valign="top" width="50%" style="padding-right:10px;">
            <h3 style="margin:0 0 8px;font-size:15px;">Low POS Stock</h3>
            @forelse($report['inventory']['pos_low_stock'] as $product)
              <p style="margin:0 0 7px;">{{ $product->name }}: <strong>{{ $number($product->stock) }}</strong></p>
            @empty
              <p style="margin:0;color:#7b6b61;">No low POS stock.</p>
            @endforelse
          </td>
          <td valign="top" width="50%" style="padding-left:10px;">
            <h3 style="margin:0 0 8px;font-size:15px;">Low Website Stock</h3>
            @forelse($report['inventory']['online_low_stock'] as $product)
              <p style="margin:0 0 7px;">{{ $product->name }}: <strong>{{ $number($product->stock) }}</strong></p>
            @empty
              <p style="margin:0;color:#7b6b61;">No low website stock.</p>
            @endforelse
          </td>
        </tr>
      </table>
    </div>

    <div style="background:#fff;border-radius:10px;margin-top:16px;padding:20px;border:1px solid #eadfcb;">
      <h2 style="margin:0 0 14px;font-size:18px;">Admin Activity</h2>
      <p style="margin:0 0 8px;">Superadmin actions: <strong>{{ $number($report['activity']['superadmin_actions']) }}</strong></p>
      <p style="margin:0;">POS/admin activity log entries: <strong>{{ $number($report['activity']['pos_actions']) }}</strong></p>
    </div>

    <p style="color:#7b6b61;font-size:12px;margin:16px 0 0;">Generated {{ $report['generated_at'] }}.</p>
  </div>
</body>
</html>
