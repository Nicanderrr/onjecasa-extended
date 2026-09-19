@extends('layouts.pos-admin')

@section('title', 'Superadmin - Audit Logs')
@section('page-eyebrow', 'Audit')
@section('page-title', 'Audit Logs')
@section('page-description', 'Review superadmin changes and POS activity trails.')

@section('content')
<div class="entity-page">
  <section class="row g-2 dashboard-metrics entity-metrics">
    <div class="col-6 col-lg-4"><article class="metric-card metric-primary"><div class="metric-top"><span class="metric-label">Superadmin Logs</span><span class="metric-icon"><i class="bi bi-shield-check"></i></span></div><div class="metric-value">{{ number_format($summary['superadmin']) }}</div><div class="metric-meta"><span>Portal changes</span></div></article></div>
    <div class="col-6 col-lg-4"><article class="metric-card metric-success"><div class="metric-top"><span class="metric-label">POS Logs</span><span class="metric-icon"><i class="bi bi-shop"></i></span></div><div class="metric-value">{{ number_format($summary['pos']) }}</div><div class="metric-meta"><span>Storefront activity</span></div></article></div>
    <div class="col-12 col-lg-4"><article class="metric-card metric-warning"><div class="metric-top"><span class="metric-label">Today</span><span class="metric-icon"><i class="bi bi-calendar2-check"></i></span></div><div class="metric-value">{{ number_format($summary['today']) }}</div><div class="metric-meta"><span>All tracked events</span></div></article></div>
  </section>

  <div class="card shadow entity-card mt-3">
    <div class="card-header border-0 entity-toolbar">
      <div class="cashier-table-heading"><span><i class="bi bi-clock-history"></i></span><div><strong>{{ $source === 'pos' ? 'POS audit trail' : 'Superadmin audit trail' }}</strong><small>Search and source filter</small></div></div>
      <form method="GET" action="{{ route('superadmin.audit') }}" class="d-flex flex-wrap gap-2">
        <select name="source" class="form-select form-select-sm" style="width: 160px;">
          <option value="superadmin" @selected($source !== 'pos')>Superadmin</option>
          <option value="pos" @selected($source === 'pos')>POS</option>
        </select>
        <input type="search" name="q" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search logs" style="width: 220px;">
        <button class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
      </form>
    </div>
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead><tr><th>Event</th><th>User</th><th>Subject</th><th>IP</th><th>Date</th></tr></thead>
        <tbody>
          @forelse($logs as $log)
            <tr>
              @if($source === 'pos')
                <td><strong>{{ str_replace('_', ' ', ucfirst($log->event)) }}</strong><small class="d-block text-muted">{{ $log->description }}</small></td>
                <td>{{ $log->user_name ?? 'System' }}<small class="d-block text-muted">{{ $log->user_role ?? 'Unknown role' }}</small></td>
                <td>{{ $log->auditable_type ?? 'None' }} {{ $log->auditable_id ? '#'.$log->auditable_id : '' }}</td>
                <td>{{ $log->ip_address ?? 'local' }}</td>
                <td>{{ \Illuminate\Support\Carbon::parse($log->created_at)->format('d M Y, h:i A') }}</td>
              @else
                <td><strong>{{ str_replace('_', ' ', ucfirst($log->action)) }}</strong></td>
                <td>{{ $log->user->name ?? 'System' }}<small class="d-block text-muted">{{ $log->user->email ?? '' }}</small></td>
                <td>{{ class_basename($log->subject_type ?? 'None') }} {{ $log->subject_id ? '#'.$log->subject_id : '' }}</td>
                <td>{{ $log->ip_address ?? 'local' }}</td>
                <td>{{ $log->created_at?->format('d M Y, h:i A') }}</td>
              @endif
            </tr>
          @empty
            <tr><td colspan="5" class="text-center py-5 text-muted">No audit records found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($logs->hasPages())
      <div class="card-footer border-0">{{ $logs->links() }}</div>
    @endif
  </div>
</div>
@endsection
