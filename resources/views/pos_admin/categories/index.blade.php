@extends('layouts.pos-admin')

@section('title', 'Categories - ONJECASA POS')
@section('page-eyebrow', 'Catalog')
@section('page-title', 'Categories')
@section('page-description', 'Keep product groups clean and easy to scan.')
@section('page-actions')
  <a href="{{ route('pos.admin.categories.create') }}" class="btn btn-primary btn-sm">
    <i class="fas fa-tags"></i> Add Category
  </a>
@endsection

@section('content')
@php
  $featured = $rows->first();
@endphp

<div class="entity-page">
  <section class="row g-2 dashboard-metrics entity-metrics" aria-label="Category metrics">
    <div class="col-12 col-md-4">
      <article class="metric-card metric-primary">
        <div class="metric-top">
          <span class="metric-label">Total Categories</span>
          <span class="metric-icon"><i class="bi bi-collection" aria-hidden="true"></i></span>
        </div>
        <div class="metric-value">{{ $rows->count() }}</div>
        <div class="metric-meta"><span class="text-primary">Catalog</span><span>groups available</span></div>
      </article>
    </div>
    <div class="col-12 col-md-4">
      <article class="metric-card metric-success">
        <div class="metric-top">
          <span class="metric-label">Latest Code</span>
          <span class="metric-icon"><i class="bi bi-upc-scan" aria-hidden="true"></i></span>
        </div>
        <div class="metric-value">{{ $featured->code ?? '—' }}</div>
        <div class="metric-meta"><span class="text-success">Newest</span><span>category code</span></div>
      </article>
    </div>
    <div class="col-12 col-md-4">
      <article class="metric-card metric-warning">
        <div class="metric-top">
          <span class="metric-label">Latest Name</span>
          <span class="metric-icon"><i class="bi bi-tag" aria-hidden="true"></i></span>
        </div>
        <div class="metric-value">{{ $featured->name ?? '—' }}</div>
        <div class="metric-meta"><span class="text-warning">Recent</span><span>category added</span></div>
      </article>
    </div>
  </section>

  <div class="card shadow entity-card">
    <div class="card-header border-0 entity-toolbar">
      <div class="entity-chip">
        <i class="fas fa-layer-group"></i>
        Categories are sorted newest first
      </div>
      <input
        type="search"
        class="form-control form-control-sm entity-filter"
        placeholder="Filter categories"
        aria-label="Filter categories"
        data-table-filter="#categories-table"
      >
    </div>

    <div class="table-responsive">
      <table class="table align-items-center table-flush" id="categories-table">
        <thead class="thead-light">
          <tr>
            <th>Code</th>
            <th>Name</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($rows as $r)
            <tr data-filter-row>
              <td><span class="font-weight-bold">{{ $r->code }}</span></td>
              <td>{{ $r->name }}</td>
              <td class="text-right">
                <div class="action-group justify-content-end">
                  <form method="POST" action="{{ route('pos.admin.categories.destroy', $r->id) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" title="Delete category">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                  <a href="{{ route('pos.admin.categories.edit', $r->id) }}" class="btn btn-sm btn-outline-primary" title="Edit category">
                    <i class="fas fa-edit"></i>
                  </a>
                </div>
              </td>
            </tr>
          @empty
            <tr data-filter-empty>
              <td colspan="3" class="text-center py-5 text-muted">No categories yet. Add one to start grouping products.</td>
            </tr>
          @endforelse
          @if($rows->count())
            <tr data-filter-empty style="display:none;">
              <td colspan="3" class="text-center py-5 text-muted">No matching categories found.</td>
            </tr>
          @endif
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

