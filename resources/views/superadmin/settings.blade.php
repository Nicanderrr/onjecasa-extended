@extends('layouts.pos-admin')

@section('title', 'Superadmin - System Settings')
@section('page-eyebrow', 'Settings')
@section('page-title', 'System Settings')
@section('page-description', 'Review global settings and jump to the connected settings pages.')

@section('content')
<div class="entity-page">
  <section class="row g-3">
    <div class="col-12 col-xl-6">
      <div class="panel h-100">
        <div class="panel-header">
          <div>
            <h2 class="h5 mb-1 section-title"><i class="bi bi-gear"></i><span>Global POS Settings</span></h2>
            <p class="text-muted mb-0">Logo, favicon, login images, dark mode, and system name are managed here.</p>
          </div>
          <a class="btn btn-primary btn-sm" href="{{ route('pos.admin.settings.index') }}">Open POS Settings</a>
        </div>
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead><tr><th>Setting</th><th>Value</th></tr></thead>
            <tbody>
              @forelse($posSettings as $key => $value)
                <tr>
                  <td><strong>{{ str_replace('_', ' ', ucfirst($key)) }}</strong></td>
                  <td class="text-muted">{{ \Illuminate\Support\Str::limit((string) $value, 80) }}</td>
                </tr>
              @empty
                <tr><td colspan="2" class="text-center py-5 text-muted">No POS settings saved yet.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="panel h-100 quick-actions-panel">
        <div class="panel-header">
          <div>
            <h2 class="h5 mb-1 section-title"><i class="bi bi-sliders"></i><span>Connected Settings</span></h2>
            <p class="text-muted mb-0">These are the settings pages already connected to the website and POS.</p>
          </div>
        </div>
        <div class="d-grid gap-2">
          <a class="btn btn-outline-secondary text-start" href="{{ route('admin.contact.edit') }}"><strong class="d-block">Website Contact Settings</strong><small class="text-muted">Address, contact details, and storefront content</small></a>
          <a class="btn btn-outline-secondary text-start" href="{{ route('home_slide') }}"><strong class="d-block">Homepage Slides</strong><small class="text-muted">Hero media, top promo, and bottom promo</small></a>
          <a class="btn btn-outline-secondary text-start" href="{{ route('pos.admin.settings.index') }}"><strong class="d-block">Logo & Favicon</strong><small class="text-muted">The uploaded logo appears across POS, website, and browser favicon</small></a>
          <a class="btn btn-outline-secondary text-start" href="{{ route('superadmin.security') }}"><strong class="d-block">Security Policy</strong><small class="text-muted">AI assistant and maintenance switches</small></a>
        </div>
      </div>
    </div>
  </section>
</div>
@endsection
