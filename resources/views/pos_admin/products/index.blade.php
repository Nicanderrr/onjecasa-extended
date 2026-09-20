@extends('layouts.pos-admin')

@section('title', 'Products - ONJECASA POS')
@section('page-eyebrow', 'Inventory')
@section('page-title', 'Products')
@section('page-description', 'Track stock levels, pricing, and catalog updates from one compact view.')
@section('page-actions')
  <a href="{{ route('pos.admin.products.import') }}" class="btn btn-outline-primary btn-sm">
    <i class="bi bi-upload"></i> Import Products
  </a>
  <a href="{{ route('pos.admin.products.create') }}" class="btn btn-primary btn-sm">
    <i class="fas fa-utensils"></i> Add Product
  </a>
@endsection

@section('content')
<div class="entity-page">
  <section class="row g-2 dashboard-metrics entity-metrics" aria-label="Product metrics">
    <div class="col-12 col-md-3">
      <article class="metric-card metric-primary">
        <div class="metric-top">
          <span class="metric-label">Total Products</span>
          <span class="metric-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span>
        </div>
        <div class="metric-value">{{ number_format($totalProducts) }}</div>
        <div class="metric-meta"><span class="text-primary">Catalog</span><span>items listed</span></div>
      </article>
    </div>
    <div class="col-12 col-md-3">
      <article class="metric-card metric-danger">
        <div class="metric-top">
          <span class="metric-label">Low Stock</span>
          <span class="metric-icon"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></span>
        </div>
        <div class="metric-value">{{ $lowStock }}</div>
        <div class="metric-meta"><span class="text-danger">Attention</span><span>10 units or fewer</span></div>
      </article>
    </div>
    <div class="col-12 col-md-3">
      <article class="metric-card metric-success">
        <div class="metric-top">
          <span class="metric-label">Inventory Value</span>
          <span class="metric-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span>
        </div>
        <div class="metric-value">{{ number_format($inventoryValue, 2) }}</div>
        <div class="metric-meta"><span class="text-success">Stock</span><span>estimated value</span></div>
      </article>
    </div>
    <div class="col-12 col-md-3">
      <article class="metric-card metric-warning">
        <div class="metric-top">
          <span class="metric-label">Potential Profit</span>
          <span class="metric-icon"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i></span>
        </div>
        <div class="metric-value">{{ number_format($potentialProfit, 2) }}</div>
        <div class="metric-meta"><span class="text-warning">Current stock</span><span>estimated margin</span></div>
      </article>
    </div>
  </section>

  <div class="card shadow entity-card">
    <div class="card-header border-0 entity-toolbar products-toolbar">
      <div class="products-table-heading">
        <span class="products-table-icon"><i class="bi bi-boxes"></i></span>
        <div>
          <strong>Product inventory</strong>
          <span>{{ number_format($totalProducts) }} {{ \Illuminate\Support\Str::plural('item', $totalProducts) }} in the catalog</span>
        </div>
      </div>
      <div class="entity-filter-wrap">
        <i class="bi bi-search" aria-hidden="true"></i>
        <form method="GET" action="{{ route('pos.admin.products.index') }}">
          <input
            type="search"
            name="q"
            value="{{ request('q') }}"
            class="form-control form-control-sm entity-filter"
            placeholder="Search products or SKU"
            aria-label="Search products"
          >
        </form>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table align-items-center table-flush products-table" id="products-table">
        <thead class="thead-light">
          <tr>
            <th>Product</th>
            <th>SKU</th>
            <th>Stock</th>
            <th>Pricing</th>
            <th>Updated</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($products as $prod)
            @php
              $stock = (int) ($prod->stock ?? 0);
              $stockState = $stock === 0 ? 'out' : ($stock <= 10 ? 'low' : 'ok');
              $stockLabel = $stock === 0 ? 'Out of stock' : ($stock <= 10 ? 'Low stock' : 'In stock');
            @endphp
            <tr data-filter-row>
              <td class="product-cell">
                <div class="product-identity">
                  <div class="product-thumb-wrap">
                    @if($prod->image)
                      <img class="thumb-preview" src="{{ asset('assets/admin/img/products/'.$prod->image) }}" alt="{{ $prod->name }}">
                    @else
                      <img class="thumb-preview" src="{{ asset('assets/admin/img/products/place.png') }}" alt="{{ $prod->name }}">
                    @endif
                  </div>
                  <div class="product-copy">
                    <strong>{{ $prod->name }}</strong>
                    <span>{{ \Illuminate\Support\Str::limit($prod->description ?: 'No description added', 58) }}</span>
                  </div>
                </div>
              </td>
              <td><span class="product-code">{{ $prod->code }}</span></td>
              <td>
                <div class="product-stock">
                  <span class="stock-pill {{ $stockState }}"><span class="stock-dot"></span>{{ $stockLabel }}</span>
                  <small>{{ $stock }} {{ \Illuminate\Support\Str::plural('unit', $stock) }}</small>
                </div>
              </td>
              <td>
                <div class="product-price">Sell: {{ number_format($prod->price, 2) }}</div>
                <small class="text-muted d-block">Cost: {{ number_format((float) ($prod->cost_price ?? 0), 2) }}</small>
                <small class="text-success d-block">Profit: {{ number_format((float) $prod->price - (float) ($prod->cost_price ?? 0), 2) }}</small>
              </td>
              <td>
                <span class="product-date">
                  {{ $prod->updated_at ? \Illuminate\Support\Carbon::parse($prod->updated_at)->format('d M Y') : '—' }}
                </span>
              </td>
              <td class="text-right">
                <div class="action-group product-actions justify-content-end">
                  <a href="{{ route('pos.admin.products.edit', $prod->id) }}" class="btn btn-sm btn-outline-primary" title="Edit {{ $prod->name }}" aria-label="Edit {{ $prod->name }}">
                    <i class="bi bi-pencil-square"></i>
                  </a>
                  <form method="POST" action="{{ route('pos.admin.products.destroy', $prod->id) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" title="Delete {{ $prod->name }}" aria-label="Delete {{ $prod->name }}">
                      <i class="bi bi-trash3"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr data-filter-empty>
              <td colspan="6" class="text-center py-5 text-muted">No products yet. Add the first item to start the catalog.</td>
            </tr>
          @endforelse
          @if($products->count())
            <tr data-filter-empty style="display:none;">
              <td colspan="6" class="text-center py-5 text-muted">No matching products found.</td>
            </tr>
          @endif
        </tbody>
      </table>
    </div>
    @if($products->hasPages())
      <div class="card-footer border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <small class="text-muted">Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} products</small>
        {{ $products->links() }}
      </div>
    @endif
  </div>
</div>
@endsection

