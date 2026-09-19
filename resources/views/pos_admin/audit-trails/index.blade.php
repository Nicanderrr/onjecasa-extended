@extends('layouts.pos-admin')

@section('title', 'Audit Trail - ONJECASA POS')
@section('page-eyebrow', 'System')
@section('page-title', 'Audit Trail')
@section('page-description', 'Review user actions, system events, and target details across the workspace.')
@section('page-actions')
  <form class="d-flex flex-wrap gap-2" method="GET" action="{{ route('pos.admin.audit-trails.index') }}">
    <select class="form-select form-select-sm" name="event" style="min-width: 220px;">
      <option value="">All Events</option>
      @foreach($events as $event)
        <option value="{{ $event }}" @selected(($filters['event'] ?? '') === $event)>{{ ucwords(str_replace('_', ' ', $event)) }}</option>
      @endforeach
    </select>
    <input class="form-control form-control-sm" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search audits" style="min-width: 220px;">
    <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-search"></i> Filter</button>
  </form>
@endsection

@section('content')
@php
  $latestAuditAt = $summary['latest_audit_at']
    ? \Illuminate\Support\Carbon::parse($summary['latest_audit_at'])
    : null;
@endphp

<section class="row g-2 dashboard-metrics entity-metrics audit-metrics" aria-label="Audit trail summary">
  <div class="col-12 col-md-6 col-xl-3">
    <article class="metric-card">
      <div class="metric-top"><span class="metric-label">Audit Entries</span><span class="metric-icon"><i class="bi bi-journal-text"></i></span></div>
      <div class="metric-value">{{ number_format($summary['audit_count']) }}</div>
      <div class="metric-meta"><span class="text-success">All time</span><span>logged actions</span></div>
    </article>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <article class="metric-card">
      <div class="metric-top"><span class="metric-label">Today</span><span class="metric-icon"><i class="bi bi-calendar-day"></i></span></div>
      <div class="metric-value">{{ number_format($summary['today_count']) }}</div>
      <div class="metric-meta"><span class="text-primary">Current day</span><span>activity</span></div>
    </article>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <article class="metric-card">
      <div class="metric-top"><span class="metric-label">Admin Events</span><span class="metric-icon"><i class="bi bi-shield-check"></i></span></div>
      <div class="metric-value">{{ number_format($summary['admin_count']) }}</div>
      <div class="metric-meta"><span class="text-warning">Admin users</span><span>workspace changes</span></div>
    </article>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <article class="metric-card">
      <div class="metric-top"><span class="metric-label">Latest Event</span><span class="metric-icon"><i class="bi bi-clock-history"></i></span></div>
      <div class="metric-value">{{ $latestAuditAt ? $latestAuditAt->format('d M') : '—' }}</div>
      <div class="metric-meta"><span class="text-info">{{ $latestAuditAt ? $latestAuditAt->format('h:i A') : 'No activity' }}</span><span>most recent</span></div>
    </article>
  </div>
</section>

<div class="card shadow entity-card audit-card mt-3">
  <div class="card-header border-0 entity-toolbar audit-toolbar">
    <div class="audit-table-heading">
      <span class="audit-table-icon"><i class="bi bi-shield-lock"></i></span>
      <div>
        <strong>Audit register</strong>
        <span>
          {{ $audits->total() }} {{ \Illuminate\Support\Str::plural('entry', $audits->total()) }} recorded
          @if($latestAuditAt)
            · latest {{ $latestAuditAt->format('d M Y, h:i A') }}
          @endif
        </span>
      </div>
    </div>
    <div class="audit-toolbar-note">
      <span><i class="bi bi-info-circle"></i> Sensitive fields are redacted before storage.</span>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table align-items-center table-flush audit-table" id="audit-table">
      <thead class="thead-light">
        <tr>
          <th>Time</th>
          <th>User</th>
          <th>Event</th>
          <th>Description</th>
          <th>Target</th>
          <th>IP</th>
          <th>Details</th>
        </tr>
      </thead>
      <tbody>
        @forelse($audits as $audit)
          @php
            $eventKey = \Illuminate\Support\Str::slug($audit->event ?: 'event');
            $details = $audit->properties ? json_decode($audit->properties, true) : null;
            $detailsJson = $details ? json_encode($details, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : null;
          @endphp
          <tr data-filter-row>
            <td>
              <div class="audit-time">
                <strong>{{ \Illuminate\Support\Carbon::parse($audit->created_at)->format('d M Y') }}</strong>
                <span>{{ \Illuminate\Support\Carbon::parse($audit->created_at)->format('h:i A') }}</span>
              </div>
            </td>
            <td>
              <div class="audit-user">
                <span class="audit-user-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($audit->user_name ?: 'System', 0, 1)) }}</span>
                <div>
                  <strong>{{ $audit->user_name ?: 'System' }}</strong>
                  <span>{{ $audit->user_role ? ucfirst($audit->user_role) : 'Automated' }}</span>
                </div>
              </div>
            </td>
            <td><span class="audit-event audit-event-{{ $eventKey }}">{{ ucwords(str_replace('_', ' ', $audit->event)) }}</span></td>
            <td class="audit-description">{{ $audit->description }}</td>
            <td>
              @if($audit->auditable_type)
                <div class="audit-target">
                  <strong>{{ class_basename($audit->auditable_type) }}</strong>
                  <span>#{{ $audit->auditable_id }}</span>
                </div>
              @else
                <span class="text-muted">-</span>
              @endif
            </td>
            <td><span class="audit-ip">{{ $audit->ip_address ?: '-' }}</span></td>
            <td>
              @if($detailsJson)
                <details class="audit-details">
                  <summary><i class="bi bi-three-dots"></i> View</summary>
                  <pre>{{ $detailsJson }}</pre>
                </details>
              @else
                <span class="text-muted">-</span>
              @endif
            </td>
          </tr>
        @empty
          <tr data-filter-empty>
            <td colspan="7" class="text-center py-5 text-muted">No audit entries found.</td>
          </tr>
        @endforelse
        @if($audits->count())
          <tr data-filter-empty style="display:none;">
            <td colspan="7" class="text-center py-5 text-muted">No matching audit entries found.</td>
          </tr>
        @endif
      </tbody>
    </table>
  </div>

  @if($audits->hasPages())
    <div class="audit-pagination">{{ $audits->links('pagination::bootstrap-5') }}</div>
  @endif
</div>
@endsection

