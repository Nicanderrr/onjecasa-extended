@extends('layouts.pos-admin')

@section('title', 'Edit Category - ONJECASA POS')
@section('page-eyebrow', 'Catalog')
@section('page-title', 'Edit Category')
@section('page-description', 'Adjust the category label without changing the rest of the catalog.')
@section('page-actions')
  <a href="{{ route('pos.admin.categories.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="fas fa-arrow-left"></i> Back to Categories
  </a>
@endsection

@section('content')
<div class="entity-page">
  <section class="entity-hero">
    <div class="entity-hero-copy">
      <p class="eyebrow">Update category</p>
      <h1>Edit Category</h1>
      <p>Keep the naming consistent so filters stay easy to scan.</p>
    </div>
    <div class="entity-chip">
      <i class="fas fa-tags"></i>
      {{ $row->code }}
    </div>
  </section>

  <div class="card shadow entity-card">
    <div class="card-header border-0">
      <h3 class="mb-0">Category details</h3>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('pos.admin.categories.update', $row->id) }}" class="compact-form">
        @csrf
        @method('PUT')
        <div class="form-row">
          <div class="col-md-6 form-group">
            <label>Name</label>
            <input name="name" value="{{ $row->name }}" class="form-control" required>
          </div>
          <div class="col-md-6 form-group">
            <label>Code</label>
            <input name="code" value="{{ $row->code }}" class="form-control" required>
          </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <button class="btn btn-success">Update</button>
          <a href="{{ route('pos.admin.categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

