@php
  $money = fn ($value) => 'GHS '.number_format((float) $value, 2);
  $number = fn ($value) => number_format((float) $value);
@endphp
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;">
  <thead>
    <tr>
      <th align="left" style="padding:9px;border-bottom:1px solid #eadfcb;">Product</th>
      <th align="right" style="padding:9px;border-bottom:1px solid #eadfcb;">Units</th>
      <th align="right" style="padding:9px;border-bottom:1px solid #eadfcb;">Revenue</th>
    </tr>
  </thead>
  <tbody>
    @forelse($rows as $row)
      <tr>
        <td style="padding:9px;border-bottom:1px solid #f2eadc;">{{ $row->name }}</td>
        <td align="right" style="padding:9px;border-bottom:1px solid #f2eadc;">{{ $number($row->units) }}</td>
        <td align="right" style="padding:9px;border-bottom:1px solid #f2eadc;">{{ $money($row->revenue) }}</td>
      </tr>
    @empty
      <tr><td colspan="3" style="padding:14px;color:#7b6b61;">{{ $empty }}</td></tr>
    @endforelse
  </tbody>
</table>
