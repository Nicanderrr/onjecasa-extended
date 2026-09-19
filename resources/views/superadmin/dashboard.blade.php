@extends('layouts.pos-admin')

@section('title', 'Superadmin - Command Center')
@section('page-eyebrow', 'Full Control')
@section('page-title', 'Superadmin Command Center')
@section('page-description', 'Control users, roles, audit logs, security settings, maintenance, website admin, and POS admin from one portal.')
@section('page-actions')
  <a href="{{ route('superadmin.users') }}" class="btn btn-primary btn-sm"><i class="bi bi-person-plus"></i> Users & Roles</a>
@endsection

@section('content')
<div class="entity-page">
  <section class="panel mb-3">
    <form method="GET" action="{{ route('superadmin.dashboard') }}" class="row g-3 align-items-end">
      <div class="col-12 col-md-4 col-xl-3">
        <label class="form-label small text-uppercase text-muted mb-1">From</label>
        <input type="date" name="date_from" value="{{ $dateFilters['date_from'] ?? now()->subDays(6)->toDateString() }}" class="form-control form-control-sm">
      </div>
      <div class="col-12 col-md-4 col-xl-3">
        <label class="form-label small text-uppercase text-muted mb-1">To</label>
        <input type="date" name="date_to" value="{{ $dateFilters['date_to'] ?? now()->toDateString() }}" class="form-control form-control-sm">
      </div>
      <div class="col-12 col-md-auto d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel"></i> Apply</button>
        <a href="{{ route('superadmin.dashboard') }}" class="btn btn-light btn-sm">Reset</a>
      </div>
    </form>
  </section>

  <section class="row g-2 dashboard-metrics entity-metrics" aria-label="Superadmin metrics">
    @foreach($metrics as $metric)
      <div class="col-6 col-xl">
        <a href="{{ $metric['route'] }}" class="metric-card metric-link {{ $metric['tone'] }}">
          <div class="metric-top">
            <span class="metric-label">{{ $metric['label'] }}</span>
            <span class="metric-icon"><i class="bi {{ $metric['icon'] }}"></i></span>
          </div>
          <div class="metric-value">{{ is_numeric($metric['value']) ? number_format($metric['value']) : $metric['value'] }}</div>
          <div class="metric-meta"><span>{{ $metric['hint'] }}</span></div>
        </a>
      </div>
    @endforeach
  </section>

  <section class="row g-3 mt-1">
    <div class="col-12 col-xl-8">
      <div class="panel h-100">
        <div class="panel-header">
          <div>
            <h2 class="h5 mb-1 section-title"><i class="bi bi-activity"></i><span>System Activity</span></h2>
            <p class="text-muted mb-0">Superadmin and POS actions for the selected date range.</p>
          </div>
          <a class="btn btn-light btn-sm" href="{{ route('superadmin.audit') }}">Open Audit Logs</a>
        </div>
        @php $maxActivity = max(1, $dailyActivity->max('total') ?: 1); @endphp
        <div class="d-flex align-items-end gap-2 rounded-4 bg-light p-3" style="height: 260px;">
          @foreach($dailyActivity as $point)
            @php $height = max(10, round(($point['total'] / $maxActivity) * 100)); @endphp
            <div class="d-flex flex-column align-items-center justify-content-end flex-fill h-100 gap-2">
              <span class="small fw-bold text-muted">{{ $point['total'] }}</span>
              <div class="w-100 rounded-top" style="height: {{ $height }}%; min-height: 10px; background: linear-gradient(180deg, #f4b63f, #2f9b55);"></div>
              <span class="small text-muted">{{ $point['label'] }}</span>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-4">
      <div class="panel h-100 quick-actions-panel">
        <div class="panel-header">
          <div>
            <h2 class="h5 mb-1 section-title"><i class="bi bi-lightning-charge"></i><span>Control Links</span></h2>
            <p class="text-muted mb-0">Jump into the right control surface.</p>
          </div>
        </div>
        <div class="d-grid gap-2">
          @foreach($quickLinks as $link)
            <a class="btn btn-outline-secondary text-start" href="{{ $link['route'] }}">
              <strong class="d-block">{{ $link['label'] }}</strong>
              <small class="text-muted">{{ $link['description'] }}</small>
            </a>
          @endforeach
        </div>
      </div>
    </div>
  </section>

  <section class="row g-3 mt-1">
    <div class="col-12 col-xl-6">
      <div class="panel h-100">
        <div class="panel-header">
          <div>
            <h2 class="h5 mb-1 section-title"><i class="bi bi-shield-check"></i><span>Security State</span></h2>
            <p class="text-muted mb-0">Current superadmin policy switches.</p>
          </div>
          <a class="btn btn-light btn-sm" href="{{ route('superadmin.security') }}">Manage</a>
        </div>
        <div class="row g-2">
          <div class="col-12 col-md-6">
            <div class="rounded-4 border p-3">
              <small class="text-muted d-block">POS AI Assistant</small>
              <strong>{{ ($settings['ai_enabled'] ?? true) ? 'Enabled' : 'Disabled' }}</strong>
            </div>
          </div>
          <div class="col-12 col-md-6">
            <div class="rounded-4 border p-3">
              <small class="text-muted d-block">Maintenance Flag</small>
              <strong>{{ ($settings['maintenance_enabled'] ?? false) ? 'On' : 'Off' }}</strong>
            </div>
          </div>
        </div>
        @if(! empty($settings['maintenance_note']))
          <div class="alert alert-warning mt-3 mb-0">{{ $settings['maintenance_note'] }}</div>
        @endif
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="panel h-100">
        <div class="panel-header">
          <div>
            <h2 class="h5 mb-1 section-title"><i class="bi bi-clock-history"></i><span>Recent Superadmin Actions</span></h2>
            <p class="text-muted mb-0">Latest changes made from this portal.</p>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead><tr><th>Action</th><th>User</th><th>When</th></tr></thead>
            <tbody>
              @forelse($recentLogs as $log)
                <tr>
                  <td>{{ str_replace('_', ' ', ucfirst($log->action)) }}</td>
                  <td>{{ $log->user->name ?? 'System' }}</td>
                  <td>{{ $log->created_at?->diffForHumans() }}</td>
                </tr>
              @empty
                <tr><td colspan="3" class="text-center py-5 text-muted">No superadmin activity yet.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</div>
@endsection
