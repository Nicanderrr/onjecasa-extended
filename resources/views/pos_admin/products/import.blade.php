@extends('layouts.pos-admin')

@section('title', 'Import Products - ONJECASA POS')
@section('page-eyebrow', 'Inventory')
@section('page-title', 'Import Products')
@section('page-description', 'Load products from Excel, CSV, or a safe product-only MySQL dump.')
@section('page-actions')
  <a href="{{ route('pos.admin.products.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Products</a>
@endsection

@section('content')
<div class="row g-3">
  <div class="col-12 col-xl-7">
    <div class="card shadow-sm h-100">
      <div class="card-header border-0">
        <h5 class="mb-1"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Excel or CSV</h5>
        <p class="text-muted mb-0">Use columns: code, name, description, cost_price, price, stock, image.</p>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('pos.admin.products.import.store') }}" enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="import_type" value="spreadsheet">
          <div class="mb-3">
            <label class="form-label" for="spreadsheet_file">Product file</label>
            <input id="spreadsheet_file" class="form-control" type="file" name="file" accept=".csv,.txt,.xls,.xlsx" required>
            <div class="form-text">Existing products are updated when the SKU/code matches in the selected branch. Blank code values receive a generated SKU.</div>
          </div>
          <label class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="sync_to_website" value="1">
            <span class="form-check-label">Also sync imported products to the website catalog</span>
          </label>
          <button class="btn btn-primary" type="submit"><i class="bi bi-upload me-1"></i> Import Spreadsheet</button>
          <a class="btn btn-outline-secondary ms-2" href="{{ route('pos.admin.products.import.template') }}"><i class="bi bi-download me-1"></i> Download Template</a>
        </form>
      </div>
    </div>
  </div>

  <div class="col-12 col-xl-5">
    <div class="card shadow-sm h-100">
      <div class="card-header border-0">
        <h5 class="mb-1"><i class="bi bi-database me-2"></i>MySQL product dump</h5>
        <p class="text-muted mb-0">Import INSERT or REPLACE statements for <code>pos_products</code>.</p>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('pos.admin.products.import.store') }}" enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="import_type" value="sql">
          <div class="mb-3">
            <label class="form-label" for="sql_file">SQL file</label>
            <input id="sql_file" class="form-control" type="file" name="file" accept=".sql" required>
            <div class="form-text">For safety, destructive SQL and statements for other tables are rejected. Rows without a branch are assigned to the selected branch.</div>
          </div>
          <button class="btn btn-dark" type="submit"><i class="bi bi-database-add me-1"></i> Import MySQL Products</button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="card shadow-sm mt-3">
  <div class="card-body">
    <h6 class="mb-2">Import notes</h6>
    <ul class="mb-0 text-muted">
      <li>Use <code>cost_price</code> for your unit cost and <code>price</code> for the selling price.</li>
      <li>Prices may be plain numbers such as <code>25.50</code> or include GHC formatting.</li>
      <li>Image values should be existing filenames in <code>public/assets/admin/img/products</code>.</li>
      <li>Imports always apply to the currently selected branch.</li>
    </ul>
  </div>
</div>
@endsection
