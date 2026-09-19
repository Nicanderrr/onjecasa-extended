@extends('layouts.pos-admin')

@section('title', 'Add Category - ONJECASA POS')
@section('page-eyebrow', 'Catalog')
@section('page-title', 'Add Category')
@section('page-description', 'Create a clean product group with a short code and name.')
@section('page-actions')
  <a href="{{ route('pos.admin.categories.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="fas fa-arrow-left"></i> Back to Categories
  </a>
@endsection

@section('content')
<div class="entity-page">
  <section class="entity-hero">
    <div class="entity-hero-copy">
      <p class="eyebrow">New category</p>
      <h1>Add Category</h1>
      <p>Keep it short so it stays readable in product selectors.</p>
    </div>
    <div class="entity-chip">
      <i class="fas fa-tags"></i>
      Category codes auto-fill when blank
    </div>
  </section>

  <div class="card shadow entity-card">
    <div class="card-header border-0">
      <h3 class="mb-0">Category details</h3>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('pos.admin.categories.store') }}" class="compact-form">
        @csrf
        <div class="form-row">
          <div class="col-md-6 form-group">
            <label>Name</label>
            <input name="name" class="form-control" required>
          </div>
          <div class="col-md-6 form-group">
            <label>Code</label>
            <input name="code" class="form-control">
            <div class="form-hint">Leave blank to auto-generate a category code.</div>
          </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <button class="btn btn-success">Save</button>
          <a href="{{ route('pos.admin.categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

