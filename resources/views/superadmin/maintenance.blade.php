@extends('layouts.pos-admin')

@section('title', 'Superadmin - Maintenance')
@section('page-eyebrow', 'Maintenance')
@section('page-title', 'Maintenance')
@section('page-description', 'Clear cached config/routes/views and manage the maintenance flag.')

@push('styles')
<style>
  .maintenance-switch-card {
    align-items: center;
    background:
      radial-gradient(circle at 100% 0, rgba(242, 199, 92, .18), transparent 34%),
      linear-gradient(135deg, rgba(111, 29, 53, .07), rgba(255, 255, 255, .95));
    border: 1px solid rgba(111, 29, 53, .14);
    border-radius: 1.25rem;
    display: flex;
    gap: 1rem;
    justify-content: space-between;
    padding: 1rem;
  }

  .maintenance-switch-copy {
    min-width: 0;
  }

  .maintenance-switch-kicker {
    color: #2f9b55;
    display: block;
    font-size: .68rem;
    font-weight: 900;
    letter-spacing: .14em;
    line-height: 1.1;
    text-transform: uppercase;
  }

  .maintenance-switch-title {
    color: #24111a;
    display: block;
    font-size: 1rem;
    font-weight: 800;
    line-height: 1.25;
    margin-top: .2rem;
  }

  .maintenance-switch-note {
    color: #7b6570;
    display: block;
    font-size: .84rem;
    line-height: 1.4;
    margin-top: .2rem;
  }

  .maintenance-switch {
    cursor: pointer;
    flex: 0 0 auto;
    position: relative;
  }

  .maintenance-switch input {
    height: 1px;
    opacity: 0;
    position: absolute;
    width: 1px;
  }

  .maintenance-switch-track {
    align-items: center;
    background: #e9edf2;
    border: 1px solid rgba(111, 29, 53, .12);
    border-radius: 999px;
    box-shadow: inset 0 2px 5px rgba(15, 23, 42, .08);
    display: flex;
    height: 42px;
    padding: 4px;
    transition: background .18s ease, border-color .18s ease;
    width: 82px;
  }

  .maintenance-switch-thumb {
    align-items: center;
    background: #ffffff;
    border-radius: 999px;
    box-shadow: 0 8px 18px rgba(15, 23, 42, .16);
    color: #2f9b55;
    display: grid;
    height: 32px;
    place-items: center;
    transform: translateX(0);
    transition: transform .18s ease, color .18s ease;
    width: 32px;
  }

  .maintenance-switch input:checked + .maintenance-switch-track {
    background: linear-gradient(135deg, #2f9b55, #173d2d);
    border-color: rgba(242, 199, 92, .5);
  }

  .maintenance-switch input:checked + .maintenance-switch-track .maintenance-switch-thumb {
    color: #c99635;
    transform: translateX(40px);
  }

  @media (max-width: 575.98px) {
    .maintenance-switch-card {
      align-items: flex-start;
      flex-direction: column;
    }
  }
</style>
@endpush

@section('content')
<div class="entity-page">
  <section class="row g-3">
    <div class="col-12 col-xl-6">
      <div class="panel h-100">
        <div class="panel-header">
          <div>
            <h2 class="h5 mb-1 section-title"><i class="bi bi-stars"></i><span>Clear Application Cache</span></h2>
            <p class="text-muted mb-0">Runs Laravel optimize:clear for config, route, view, and app cache.</p>
          </div>
        </div>
        <form method="POST" action="{{ route('superadmin.maintenance.clear-cache') }}">
          @csrf
          <button class="btn btn-primary"><i class="bi bi-arrow-clockwise"></i> Clear Cache</button>
        </form>
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="panel h-100">
        <div class="panel-header">
          <div>
            <h2 class="h5 mb-1 section-title"><i class="bi bi-cone-striped"></i><span>Maintenance Flag</span></h2>
            <p class="text-muted mb-0">This stores the maintenance preference for the system.</p>
          </div>
        </div>
        <form method="POST" action="{{ route('superadmin.maintenance.toggle') }}" class="d-grid gap-3">
          @csrf
          <input type="hidden" name="maintenance_enabled" value="0">
          <label class="maintenance-switch-card">
            <span class="maintenance-switch-copy">
              <span class="maintenance-switch-kicker">Public Storefront</span>
              <span class="maintenance-switch-title">Maintenance mode is {{ ($settings['maintenance_enabled'] ?? false) ? 'on' : 'off' }}</span>
              <span class="maintenance-switch-note">
                {{ ($settings['maintenance_enabled'] ?? false) ? 'Visitors see the maintenance page. Admin, POS, cashier, and superadmin routes remain available.' : 'Visitors can browse and checkout normally.' }}
              </span>
            </span>
            <span class="maintenance-switch" aria-label="Toggle maintenance mode">
              <input type="checkbox" name="maintenance_enabled" value="1" @checked($settings['maintenance_enabled'] ?? false) onchange="this.form.submit()">
              <span class="maintenance-switch-track">
                <span class="maintenance-switch-thumb">
                  <i class="bi {{ ($settings['maintenance_enabled'] ?? false) ? 'bi-lock-fill' : 'bi-unlock-fill' }}"></i>
                </span>
              </span>
            </span>
          </label>
          @if(! empty($settings['maintenance_note']))
            <div class="alert alert-warning mb-0">{{ $settings['maintenance_note'] }}</div>
          @endif
          <button class="btn btn-outline-secondary"><i class="bi bi-save"></i> Save Flag</button>
        </form>
      </div>
    </div>
  </section>
</div>
@endsection
