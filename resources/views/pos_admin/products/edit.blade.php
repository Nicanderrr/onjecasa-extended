@extends('layouts.pos-admin')

@section('title', 'Edit POS Product - ONJECASA POS')
@section('page-eyebrow', 'In-Store Inventory')
@section('page-title', 'Edit POS Product')
@section('page-description', 'Update an in-store product and optionally push the changes to the website catalog.')
@section('page-actions')
  <a href="{{ route('pos.admin.products.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="fas fa-arrow-left"></i> Back to Products
  </a>
@endsection

@section('content')
@php
  $currentImage = $product->image
      ? asset('assets/admin/img/products/' . $product->image)
      : asset('upload/no_image.jpg');
@endphp

<style>
  .pos-product-form {
    display: grid;
    gap: 1rem;
  }

  .pos-product-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.1fr) minmax(320px, .9fr);
    gap: 1rem;
    align-items: start;
  }

  .pos-product-fields {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
  }

  .pos-product-panel {
    min-width: 0;
    padding: 1rem;
    border: 1px solid var(--admin-border);
    border-radius: 8px;
    background: var(--admin-surface);
    box-shadow: var(--admin-shadow-sm);
  }

  .pos-product-panel-full {
    grid-column: 1 / -1;
  }

  .pos-panel-heading {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: .75rem;
    margin-bottom: .85rem;
  }

  .pos-panel-heading strong,
  .pos-panel-heading span {
    display: block;
  }

  .pos-panel-heading strong {
    color: var(--admin-text);
    font-size: .94rem;
    line-height: 1.2;
  }

  .pos-panel-heading span {
    margin-top: .16rem;
    color: var(--admin-muted);
    font-size: .76rem;
    line-height: 1.35;
  }

  .pos-panel-icon {
    width: 36px;
    height: 36px;
    display: inline-grid;
    place-items: center;
    flex: 0 0 auto;
    border-radius: 8px;
    background: rgba(242, 199, 92, .16);
    color: var(--admin-primary);
  }

  .pos-image-upload {
    display: grid;
    gap: 1rem;
    padding: 1rem;
    border: 2px dashed var(--admin-border);
    border-radius: 8px;
    background: var(--admin-surface-soft);
  }

  .pos-image-preview {
    width: 100%;
    aspect-ratio: 1.25 / 1;
    border: 1px solid var(--admin-border);
    border-radius: 8px;
    object-fit: cover;
    background: #fff;
  }

  #posProductImage {
    display: none;
  }

  .pos-current-meta {
    display: grid;
    gap: .35rem;
    margin-top: .85rem;
    padding-top: .85rem;
    border-top: 1px solid var(--admin-border);
    color: var(--admin-muted);
    font-size: .78rem;
  }

  .pos-current-meta strong {
    color: var(--admin-text);
    font-size: .84rem;
  }

  .pos-sync-panel {
    display: flex;
    align-items: flex-start;
    gap: .75rem;
    padding: .95rem 1rem;
    border: 1px solid rgba(242, 199, 92, .42);
    border-radius: 8px;
    background: linear-gradient(135deg, rgba(242, 199, 92, .16), rgba(138, 18, 79, .06));
  }

  .pos-sync-panel input {
    width: 18px;
    height: 18px;
    margin-top: .18rem;
    accent-color: var(--admin-primary);
  }

  .pos-sync-panel strong,
  .pos-sync-panel small {
    display: block;
  }

  .pos-sync-panel strong {
    color: var(--admin-text);
  }

  .pos-sync-panel small {
    margin-top: .12rem;
    color: var(--admin-muted);
    line-height: 1.4;
  }

  .pos-form-actions {
    display: flex;
    flex-wrap: wrap;
    gap: .65rem;
  }

  @media (max-width: 991.98px) {
    .pos-product-layout,
    .pos-product-fields {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 767.98px) {
    .barcode-input-action {
      display: grid;
      gap: .6rem;
    }

    .barcode-input-action .btn,
    .pos-form-actions .btn {
      width: 100%;
      justify-content: center;
    }
  }
</style>

<div class="entity-page">
  <section class="entity-hero">
    <div class="entity-hero-copy">
      <p class="eyebrow">Update in-store item</p>
      <h1>{{ $product->name }}</h1>
      <p>Adjust the POS details used by cashiers. Keep website sync checked when the online catalog should receive the same changes.</p>
    </div>
    <div class="entity-chip">
      <i class="fas fa-barcode"></i>
      {{ $product->code }}
    </div>
  </section>

  <form method="POST" action="{{ route('pos.admin.products.update', $product->id) }}" enctype="multipart/form-data" class="pos-product-form">
    @csrf
    @method('PUT')

    <div class="pos-product-layout">
      <div class="pos-product-fields">
        <section class="pos-product-panel">
          <div class="pos-panel-heading">
            <div>
              <strong>Product Name</strong>
              <span>Name shown to cashiers and on receipts.</span>
            </div>
            <span class="pos-panel-icon"><i class="fas fa-tag"></i></span>
          </div>
          <div class="form-group mb-0">
            <label>Product Name</label>
            <input type="text" name="name" value="{{ old('name', $product->name) }}" class="form-control" required>
          </div>
        </section>

        <section class="pos-product-panel">
          <div class="pos-panel-heading">
            <div>
              <strong>SKU</strong>
              <span>Stock code used for faster POS lookup.</span>
            </div>
            <span class="pos-panel-icon"><i class="fas fa-barcode"></i></span>
          </div>
          <div class="form-group mb-0">
            <label>SKU</label>
            <div class="barcode-input-action">
              <input id="productBarcodeInput" type="text" name="code" value="{{ old('code', $product->code) }}" class="form-control" inputmode="numeric" autocomplete="off" placeholder="Scan barcode or enter SKU" required data-hardware-barcode-capture>
              <button type="button" class="btn btn-outline-secondary" data-open-barcode-camera data-barcode-target="#productBarcodeInput">
                <i class="bi bi-camera-video"></i> Open Camera
              </button>
            </div>
            <div class="form-hint">Hardware scanner input is detected automatically and replaces the barcode here.</div>
          </div>
        </section>

        <section class="pos-product-panel">
          <div class="pos-panel-heading">
            <div>
              <strong>Selling Price</strong>
              <span>Price used at the cashier checkout.</span>
            </div>
            <span class="pos-panel-icon"><i class="fas fa-coins"></i></span>
          </div>
          <div class="form-group mb-0">
            <label>Product Price</label>
            <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price) }}" class="form-control" required>
          </div>
        </section>

        <section class="pos-product-panel">
          <div class="pos-panel-heading">
            <div>
              <strong>Cost Price</strong>
              <span>What this item costs the business per unit.</span>
            </div>
            <span class="pos-panel-icon"><i class="fas fa-truck-loading"></i></span>
          </div>
          <div class="form-group mb-0">
            <label>Cost Price</label>
            <input type="number" step="0.01" min="0" name="cost_price" value="{{ old('cost_price', $product->cost_price ?? 0) }}" class="form-control" placeholder="0.00" required>
            <div class="form-hint">Profit per unit is calculated from selling price minus cost price.</div>
          </div>
        </section>

        <section class="pos-product-panel">
          <div class="pos-panel-heading">
            <div>
              <strong>Stock Quantity</strong>
              <span>Available units for in-store selling.</span>
            </div>
            <span class="pos-panel-icon"><i class="fas fa-boxes"></i></span>
          </div>
          <div class="form-group mb-0">
            <label>Stock</label>
            <input type="number" min="0" step="1" name="stock" value="{{ old('stock', $product->stock) }}" class="form-control" required>
          </div>
        </section>

        <section class="pos-product-panel pos-product-panel-full">
          <div class="pos-panel-heading">
            <div>
              <strong>Description</strong>
              <span>Short product notes for staff and website sync.</span>
            </div>
            <span class="pos-panel-icon"><i class="fas fa-align-left"></i></span>
          </div>
          <div class="form-group mb-0">
            <label>Product Description</label>
            <textarea rows="4" name="description" class="form-control">{{ old('description', $product->description) }}</textarea>
          </div>
        </section>
      </div>

      <aside class="pos-product-panel">
        <div class="pos-panel-heading">
          <div>
            <strong>Product Image</strong>
            <span>Upload a new image only when replacing the current one.</span>
          </div>
          <span class="pos-panel-icon"><i class="fas fa-image"></i></span>
        </div>

        <div class="pos-image-upload">
          <img id="posImagePreview" class="pos-image-preview" src="{{ $currentImage }}" alt="Product image preview">
          <div>
            <label for="posProductImage" class="btn btn-light btn-sm mb-2">
              <i class="fas fa-upload"></i> Choose New Image
            </label>
            <input id="posProductImage" type="file" name="image" accept="image/*">
            <div id="posImageFileName" class="form-hint">Current image is unchanged. Maximum image size: 5 MB.</div>
          </div>
        </div>

        <div class="pos-current-meta">
          <strong>Current POS Record</strong>
          <span>SKU: {{ $product->code }}</span>
          <span>Stock: {{ number_format((int) $product->stock) }} units</span>
          <span>Cost: GHS {{ number_format((float) ($product->cost_price ?? 0), 2) }}</span>
          <span>Price: GHS {{ number_format((float) $product->price, 2) }}</span>
          <span>Unit profit: GHS {{ number_format((float) $product->price - (float) ($product->cost_price ?? 0), 2) }}</span>
        </div>
      </aside>
    </div>

    <label class="pos-sync-panel">
      <input type="checkbox" name="sync_to_website" value="1" checked>
      <span>
        <strong>Also update this product on website</strong>
        <small>Auto-checked. Saving updates the linked storefront product using this price, stock, description, and image when changed.</small>
      </span>
    </label>

    <div class="pos-form-actions">
      <button type="submit" class="btn btn-success">
        <i class="fas fa-check-circle"></i> Update Product
      </button>
      <a href="{{ route('pos.admin.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>
@include('admin.products.partials.barcode-scanner')
@endsection

@push('scripts')
<script>
  document.getElementById('posProductImage')?.addEventListener('change', function(event) {
    var file = event.target.files && event.target.files[0] ? event.target.files[0] : null;
    var preview = document.getElementById('posImagePreview');
    var fileName = document.getElementById('posImageFileName');

    if (!file) {
      preview.src = '{{ $currentImage }}';
      fileName.textContent = 'Current image is unchanged. Maximum image size: 5 MB.';
      return;
    }

    fileName.textContent = file.name;
    var reader = new FileReader();
    reader.onload = function(loadEvent) {
      preview.src = loadEvent.target.result;
    };
    reader.readAsDataURL(file);
  });
</script>
@endpush

