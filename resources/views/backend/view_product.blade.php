@extends('backend.admin')

@section('title', 'Website Products - ONJECASA')
@section('page-icon', 'bi bi-box-seam')
@section('page-eyebrow', 'Storefront Catalog')
@section('page-title', 'Website Products')
@section('page-description', 'Manage the products shown on the public website and keep them synced with POS when needed.')
@section('page-actions')
  <a href="{{ route('add_product') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-circle"></i> Add Website Product
  </a>
@endsection

@section('admin')
@php
  $fallbackImage = asset('upload/no_image.jpg');
@endphp

<style>
  .website-products-table .product-thumb-wrap {
    cursor: zoom-in;
  }

  .website-product-sync {
    display: grid;
    gap: .25rem;
  }

  .drink-preview-image {
    width: 100%;
    max-height: 70vh;
    object-fit: contain;
    border-radius: 8px;
    background: #f8fafc;
  }
</style>

<div class="entity-page">
  <section class="row g-2 dashboard-metrics entity-metrics" aria-label="Website product metrics">
    <div class="col-12 col-md-4">
      <article class="metric-card metric-primary">
        <div class="metric-top">
          <span class="metric-label">Website Products</span>
          <span class="metric-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span>
        </div>
        <div class="metric-value">{{ number_format($totalProducts) }}</div>
        <div class="metric-meta"><span class="text-primary">Storefront</span><span>items listed</span></div>
      </article>
    </div>
    <div class="col-12 col-md-4">
      <article class="metric-card metric-success">
        <div class="metric-top">
          <span class="metric-label">Synced to POS</span>
          <span class="metric-icon"><i class="bi bi-arrow-repeat" aria-hidden="true"></i></span>
        </div>
        <div class="metric-value">{{ number_format($syncedProducts) }}</div>
        <div class="metric-meta"><span class="text-success">Linked</span><span>available in-store</span></div>
      </article>
    </div>
    <div class="col-12 col-md-4">
      <article class="metric-card metric-warning">
        <div class="metric-top">
          <span class="metric-label">Catalog Value</span>
          <span class="metric-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span>
        </div>
        <div class="metric-value">{{ number_format($totalInventoryValue, 2) }}</div>
        <div class="metric-meta"><span class="text-warning">{{ number_format($categories) }}</span><span>categories, price x stock</span></div>
      </article>
    </div>
  </section>

  <div class="card shadow entity-card">
    <div class="card-header border-0 entity-toolbar products-toolbar">
      <div class="products-table-heading">
        <span class="products-table-icon"><i class="bi bi-globe2"></i></span>
        <div>
          <strong>Website catalog</strong>
          <span>{{ number_format($totalProducts) }} {{ \Illuminate\Support\Str::plural('item', $totalProducts) }} shown on the storefront</span>
        </div>
      </div>
      <div class="entity-filter-wrap">
        <i class="bi bi-search" aria-hidden="true"></i>
        <form method="GET" action="{{ route('view_product') }}">
          <input
            type="search"
            name="q"
            value="{{ request('q') }}"
            class="form-control form-control-sm entity-filter"
            placeholder="Search website products"
            aria-label="Search website products"
          >
        </form>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table align-items-center table-flush products-table website-products-table" id="website-products-table">
        <thead class="thead-light">
          <tr>
            <th>Product</th>
            <th>Category</th>
            <th>Units / Variants</th>
            <th>POS Sync</th>
            <th>Unit Price</th>
            <th>Updated</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($alldata as $product)
            @php
              $units = \App\Support\ProductOptions::options($product);
              $variants = is_string($product->colors) ? json_decode($product->colors, true) : $product->colors;
              $variants = is_array($variants) ? $variants : [];
              $unitLabels = collect($units)
                ->map(fn ($unit) => ($unit['name'] ?? 'Single Item') . ' - GHC ' . number_format((float) ($unit['price'] ?? $product->price ?? 0), 2))
                ->all();
              $isSynced = ! empty($product->pos_product_id);
              $thumbnailUrl = $product->thumbnail_media_url;
              $thumbnailType = $product->thumbnail_media_type;
            @endphp
            <tr data-filter-row>
              <td class="product-cell">
                <div class="product-identity">
                  <button type="button"
                          class="product-thumb-wrap border-0 p-0"
                          data-bs-toggle="modal"
                          data-bs-target="#drinkImagePreviewModal"
                          data-media="{{ $thumbnailUrl }}"
                          data-type="{{ $thumbnailType }}"
                          data-title="{{ $product->name }}">
                    @if($thumbnailType === 'video')
                      <video class="thumb-preview" src="{{ $thumbnailUrl }}" autoplay muted loop playsinline></video>
                    @else
                      <img class="thumb-preview"
                           src="{{ $thumbnailUrl }}"
                           alt="{{ $product->name }} photo"
                           onerror="this.onerror=null;this.src='{{ $fallbackImage }}';">
                    @endif
                  </button>
                  <div class="product-copy">
                    <strong>{{ $product->name }}</strong>
                    <span>{{ \Illuminate\Support\Str::limit($product->contents ?: 'No description added', 58) }}</span>
                  </div>
                </div>
              </td>
              <td><span class="product-code">{{ $product->description ?: 'Uncategorized' }}</span></td>
              <td>
                <div class="product-stock">
                  <span class="stock-pill ok"><span class="stock-dot"></span>{{ $unitLabels ? implode(', ', array_slice($unitLabels, 0, 2)) : 'Single Item' }}</span>
                  <small>{{ $variants ? implode(', ', array_slice($variants, 0, 3)) : 'Standard' }}</small>
                  <small>Stock: {{ number_format((int) ($product->stock ?? 0)) }}</small>
                </div>
              </td>
              <td>
                <div class="website-product-sync">
                  <span class="stock-pill {{ $isSynced ? 'ok' : 'low' }}">
                    <span class="stock-dot"></span>{{ $isSynced ? 'Synced' : 'Website only' }}
                  </span>
                  <small>{{ $isSynced ? 'POS #' . $product->pos_product_id : 'Not linked to POS' }}</small>
                </div>
              </td>
              <td><span class="product-price">{{ number_format((float) $product->price, 2) }}</span></td>
              <td>
                <span class="product-date">
                  {{ $product->updated_at ? \Illuminate\Support\Carbon::parse($product->updated_at)->format('d M Y') : 'No date' }}
                </span>
              </td>
              <td class="text-right">
                <div class="action-group product-actions justify-content-end">
                  <a href="{{ route('editproduct', $product->id) }}" class="btn btn-sm btn-outline-primary" title="Edit {{ $product->name }}" aria-label="Edit {{ $product->name }}">
                    <i class="bi bi-pencil-square"></i>
                  </a>
                  <a href="{{ route('deleteproduct', $product->id) }}" data-confirm-delete class="btn btn-sm btn-outline-danger" title="Delete {{ $product->name }}" aria-label="Delete {{ $product->name }}">
                    <i class="bi bi-trash3"></i>
                  </a>
                </div>
              </td>
            </tr>
          @empty
            <tr data-filter-empty>
              <td colspan="7" class="text-center py-5 text-muted">No website products yet. Add the first product to start the storefront catalog.</td>
            </tr>
          @endforelse
          @if($alldata->count())
            <tr data-filter-empty style="display:none;">
              <td colspan="7" class="text-center py-5 text-muted">No matching website products found.</td>
            </tr>
          @endif
        </tbody>
      </table>
    </div>
    @if($alldata->hasPages())
      <div class="card-footer border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <small class="text-muted">Showing {{ $alldata->firstItem() }} to {{ $alldata->lastItem() }} of {{ $alldata->total() }} products</small>
        {{ $alldata->links() }}
      </div>
    @endif
  </div>
</div>

<div class="modal fade" id="drinkImagePreviewModal" tabindex="-1" aria-labelledby="drinkImagePreviewTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="drinkImagePreviewTitle">Website product photo</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <img src="{{ $fallbackImage }}" alt="Website product photo preview" class="drink-preview-image" id="drinkImagePreviewImg">
        <video class="drink-preview-image" id="drinkImagePreviewVideo" controls playsinline style="display:none;"></video>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.getElementById('drinkImagePreviewModal')?.addEventListener('show.bs.modal', function(event) {
    var trigger = event.relatedTarget;
    var media = trigger?.getAttribute('data-media') || '{{ $fallbackImage }}';
    var type = trigger?.getAttribute('data-type') || 'image';
    var title = trigger?.getAttribute('data-title') || 'Website product photo';
    var image = this.querySelector('#drinkImagePreviewImg');
    var video = this.querySelector('#drinkImagePreviewVideo');

    this.querySelector('#drinkImagePreviewTitle').textContent = title;
    if (type === 'video') {
      image.style.display = 'none';
      video.style.display = 'block';
      video.src = media;
      video.load();
    } else {
      video.pause();
      video.removeAttribute('src');
      video.load();
      video.style.display = 'none';
      image.style.display = 'block';
      image.src = media;
    }
  });
</script>
@endpush


