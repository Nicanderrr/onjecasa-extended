@extends('layouts.cashier')

@section('title', 'New Sale - Cashier')

@section('content')
@php
  $defaultImage = asset('upload/no_image.jpg');
@endphp

<style>
  .cashier-sale-shell {
    display: grid;
    gap: 1rem;
    min-width: 0;
  }

  .cashier-sale-hero {
    display: flex;
    align-items: stretch;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem;
    border: 1px solid rgba(148, 163, 184, .22);
    border-radius: 8px;
    background: linear-gradient(135deg, rgba(22, 101, 52, .08), rgba(245, 158, 11, .16));
    box-shadow: 0 14px 30px rgba(15, 23, 42, .06);
  }

  .cashier-sale-hero h2 {
    margin: 0;
    color: var(--admin-text, #111827);
    font-size: 1.25rem;
    line-height: 1.2;
  }

  .cashier-sale-hero p {
    margin: .35rem 0 0;
    color: var(--admin-muted, #64748b);
    max-width: 58rem;
    line-height: 1.45;
  }

  .cashier-sale-code {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    min-width: max-content;
    padding: .75rem .9rem;
    border: 1px solid rgba(22, 163, 74, .22);
    border-radius: 8px;
    background: var(--admin-surface, #fff);
    color: #166534;
    font-weight: 900;
  }

  .cashier-scan-panel {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto auto;
    gap: .75rem;
    align-items: end;
    padding: 1rem;
    border: 1px solid rgba(148, 163, 184, .22);
    border-radius: 8px;
    background: var(--admin-surface, #fff);
    box-shadow: 0 14px 30px rgba(15, 23, 42, .06);
  }

  .cashier-scan-field label {
    display: block;
    margin-bottom: .35rem;
    color: var(--admin-muted, #64748b);
    font-size: .72rem;
    font-weight: 900;
    letter-spacing: .06em;
    text-transform: uppercase;
  }

  .cashier-scan-input-wrap {
    position: relative;
  }

  .cashier-scan-input-wrap i {
    position: absolute;
    left: .9rem;
    top: 50%;
    color: var(--admin-muted, #64748b);
    transform: translateY(-50%);
    pointer-events: none;
  }

  .cashier-scan-input-wrap .form-control {
    min-height: 3rem;
    padding-left: 2.65rem;
    font-weight: 900;
  }

  .cashier-scan-status {
    min-height: 1.25rem;
    margin-top: .4rem;
    color: var(--admin-muted, #64748b);
    font-size: .82rem;
    font-weight: 800;
  }

  .cashier-scan-status.is-success {
    color: #166534;
  }

  .cashier-scan-status.is-error {
    color: #b42318;
  }

  .cashier-sale-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(300px, .38fr);
    gap: 1rem;
    align-items: start;
  }

  .cashier-sale-panel {
    min-width: 0;
    border: 1px solid rgba(148, 163, 184, .22);
    border-radius: 8px;
    background: var(--admin-surface, #fff);
    box-shadow: 0 14px 30px rgba(15, 23, 42, .06);
    overflow: hidden;
  }

  .cashier-sale-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: 1rem;
    border-bottom: 1px solid rgba(148, 163, 184, .18);
    background: rgba(248, 250, 252, .72);
  }

  .cashier-sale-panel-title {
    display: flex;
    align-items: center;
    gap: .75rem;
    min-width: 0;
  }

  .cashier-sale-panel-title span {
    width: 38px;
    height: 38px;
    display: inline-grid;
    place-items: center;
    flex: 0 0 auto;
    border-radius: 8px;
    background: rgba(22, 163, 74, .12);
    color: #166534;
  }

  .cashier-sale-panel-title strong,
  .cashier-sale-panel-title small {
    display: block;
  }

  .cashier-sale-panel-title strong {
    color: var(--admin-text, #111827);
    line-height: 1.2;
  }

  .cashier-sale-panel-title small {
    margin-top: .15rem;
    color: var(--admin-muted, #64748b);
    line-height: 1.35;
  }

  .cashier-sale-panel-body {
    padding: 1rem;
    min-width: 0;
  }

  .cashier-sale-items {
    display: grid;
    gap: .85rem;
    min-width: 0;
  }

  .cashier-sale-item {
    display: grid;
    grid-template-columns: 76px minmax(0, 1fr) minmax(88px, .22fr) minmax(82px, .2fr) 38px;
    gap: .75rem;
    align-items: end;
    min-width: 0;
    padding: .85rem;
    border: 1px solid rgba(148, 163, 184, .2);
    border-radius: 8px;
    background: var(--admin-surface, #fff);
  }

  .cashier-sale-thumb {
    width: 76px;
    aspect-ratio: 1;
    align-self: stretch;
    border: 1px solid rgba(148, 163, 184, .2);
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
  }

  .cashier-sale-thumb img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
  }

  .cashier-field {
    min-width: 0;
  }

  .cashier-field label {
    display: block;
    margin-bottom: .35rem;
    color: var(--admin-muted, #64748b);
    font-size: .72rem;
    font-weight: 900;
    letter-spacing: .06em;
    text-transform: uppercase;
  }

  .cashier-field .form-control {
    min-width: 0;
    max-width: 100%;
  }

  .cashier-line-total {
    grid-column: 2 / 4;
  }

  .cashier-extras-field {
    grid-column: 2 / -1;
  }

  .cashier-extras-toggle {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    width: fit-content;
    margin-bottom: .55rem;
    padding: .45rem .75rem;
    border: 1px solid rgba(148, 163, 184, .22);
    border-radius: 999px;
    background: rgba(248, 250, 252, .72);
    color: var(--admin-text, #111827);
    font-size: .78rem;
    font-weight: 900;
    cursor: pointer;
  }

  .cashier-extras-toggle input {
    width: 16px;
    height: 16px;
    accent-color: #16a34a;
  }

  .cashier-extras-grid {
    display: none;
    grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
    gap: .5rem;
  }

  .cashier-sale-item.extras-enabled .cashier-extras-grid {
    display: grid;
  }

  .cashier-extra-option {
    display: grid;
    grid-template-columns: 18px minmax(0, 1fr) 58px;
    gap: .45rem;
    align-items: center;
    min-width: 0;
    margin: 0;
    padding: .55rem;
    border: 1px solid rgba(148, 163, 184, .22);
    border-radius: 8px;
    background: rgba(248, 250, 252, .72);
    cursor: pointer;
  }

  .cashier-extra-option input[type="checkbox"] {
    width: 16px;
    height: 16px;
    accent-color: #16a34a;
  }

  .cashier-extra-option strong,
  .cashier-extra-option small {
    display: block;
    min-width: 0;
    line-height: 1.2;
  }

  .cashier-extra-option strong {
    color: var(--admin-text, #111827);
    font-size: .78rem;
  }

  .cashier-extra-option small {
    margin-top: .15rem;
    color: #166534;
    font-size: .72rem;
    font-weight: 900;
  }

  .cashier-extra-qty {
    height: 34px;
    padding: .25rem;
    text-align: center;
  }

  .cashier-extra-qty:disabled {
    opacity: .45;
  }

  .cashier-product-meta {
    grid-column: 4 / 5;
    align-self: center;
    color: var(--admin-muted, #64748b);
    font-size: .78rem;
    font-weight: 700;
    line-height: 1.35;
  }

  .cashier-remove-cell {
    align-self: center;
    justify-self: end;
  }

  .cashier-checkout-panel {
    position: sticky;
    top: 18px;
  }

  .cashier-summary {
    display: grid;
    gap: .7rem;
    margin-top: 1rem;
    padding: 1rem;
    border: 1px dashed rgba(148, 163, 184, .38);
    border-radius: 8px;
    background: rgba(248, 250, 252, .7);
  }

  .cashier-summary-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    color: var(--admin-muted, #64748b);
  }

  .cashier-summary-row strong {
    color: var(--admin-text, #111827);
  }

  .cashier-summary-total {
    padding-top: .75rem;
    border-top: 1px solid rgba(148, 163, 184, .22);
    color: var(--admin-text, #111827);
    font-size: 1.2rem;
    font-weight: 900;
  }

  .cashier-sale-empty {
    padding: .9rem 1rem;
    border: 1px solid rgba(245, 158, 11, .28);
    border-radius: 8px;
    background: rgba(245, 158, 11, .1);
    color: var(--admin-muted, #64748b);
    font-size: .9rem;
  }

  @media (max-width: 1199.98px) {
    .cashier-sale-grid {
      grid-template-columns: 1fr;
    }

    .cashier-checkout-panel {
      position: static;
    }
  }

  @media (max-width: 767.98px) {
    .cashier-sale-hero {
      flex-direction: column;
    }

    .cashier-sale-code {
      width: fit-content;
    }

    .cashier-scan-panel {
      grid-template-columns: 1fr;
      padding: .85rem;
    }

    .cashier-scan-panel .btn {
      width: 100%;
      justify-content: center;
    }

    .cashier-sale-item {
      grid-template-columns: 72px minmax(0, 1fr) 38px;
      align-items: start;
    }

    .cashier-sale-item .cashier-field {
      grid-column: 2 / 3;
    }

    .cashier-sale-item .cashier-price-field,
    .cashier-sale-item .cashier-qty-field,
    .cashier-extras-field,
    .cashier-sale-item .cashier-line-total,
    .cashier-product-meta {
      grid-column: 1 / -1;
    }

    .cashier-remove-cell {
      grid-column: 3 / 4;
      grid-row: 1;
      align-self: start;
    }
  }
</style>

<form method="POST" action="{{ route('cashier.sales.store') }}" id="cashier-sale-form" class="cashier-sale-shell">
  @csrf

  <section class="cashier-sale-hero">
    <div>
      <h2>Checkout Sale</h2>
      <p>Select products, confirm the product image and available stock, then complete the customer payment from one screen.</p>
    </div>
    <div class="cashier-sale-code">
      <i class="bi bi-receipt-cutoff"></i>
      {{ now()->format('d M Y') }}
    </div>
  </section>

  @if($products->isNotEmpty())
    <section class="cashier-scan-panel">
      <div class="cashier-scan-field">
        <label for="barcode_scan">Barcode Scan</label>
        <div class="cashier-scan-input-wrap">
          <i class="bi bi-upc-scan" aria-hidden="true"></i>
          <input id="barcode_scan" type="text" class="form-control" inputmode="numeric" autocomplete="off" placeholder="Scan barcode or type SKU and press Enter" data-hardware-barcode-capture data-barcode-camera-continuous>
        </div>
        <div class="cashier-scan-status" id="barcode_scan_status">Ready for continuous barcode scans.</div>
      </div>
      <button type="button" class="btn btn-outline-secondary" data-open-barcode-camera data-barcode-target="#barcode_scan">
        <i class="bi bi-camera-video"></i> Open Camera Scanner
      </button>
      <button type="button" class="btn btn-outline-secondary" id="focus_barcode_scan">
        <i class="bi bi-crosshair"></i> Focus Scanner
      </button>
    </section>
  @endif

  <div class="cashier-sale-grid">
    <section class="cashier-sale-panel">
      <div class="cashier-sale-panel-header">
        <div class="cashier-sale-panel-title">
          <span><i class="bi bi-basket2"></i></span>
          <div>
            <strong>Basket Items</strong>
            <small>Each selected product shows photo, price, stock, and line total.</small>
          </div>
        </div>
        <button type="button" class="btn btn-outline-success btn-sm" id="add-sale-row">
          <i class="bi bi-plus-circle"></i> Add Item
        </button>
      </div>

      <div class="cashier-sale-panel-body">
        @if($products->isEmpty())
          <div class="alert alert-warning mb-0">No products are available for this branch.</div>
        @else
          <div class="cashier-sale-items" id="saleRows">
            <div class="cashier-sale-item" data-sale-row>
              <div class="cashier-sale-thumb">
                <img class="sale-item-image" src="{{ $defaultImage }}" alt="Selected product image">
              </div>

              <div class="cashier-field">
                <label>Product</label>
                <select class="form-control sale-product" name="items[0][product_id]" required>
                  <option value="" data-price="" data-stock="" data-image="{{ $defaultImage }}">Select product</option>
                  @foreach($products as $product)
                    @php
                      $imageUrl = $product->image
                          ? asset('assets/admin/img/products/' . $product->image)
                          : $defaultImage;
                    @endphp
                    <option
                      value="{{ $product->id }}"
                      data-price="{{ $product->price }}"
                      data-stock="{{ $product->stock }}"
                      data-code="{{ $product->code }}"
                      data-image="{{ $imageUrl }}"
                      @disabled((int) $product->stock < 1)
                    >
                      {{ $product->name }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="cashier-field cashier-price-field">
                <label>Price</label>
                <input type="text" class="form-control sale-price" readonly>
              </div>

              <div class="cashier-field cashier-qty-field">
                <label>Qty</label>
                <input type="number" min="1" name="items[0][qty]" class="form-control sale-qty" placeholder="1" required>
              </div>

              <div class="cashier-field cashier-extras-field">
                <label>Add-ons</label>
                <label class="cashier-extras-toggle">
                  <input type="checkbox" class="sale-extras-toggle">
                  <span>Add add-ons</span>
                </label>
                <div class="cashier-extras-grid">
                  @foreach($productExtras as $extra)
                    <label class="cashier-extra-option">
                      <input type="checkbox" class="sale-extra" name="items[0][extras][]" value="{{ $extra['name'] }}" data-extra-price="{{ $extra['price'] }}" data-extra-name="{{ $extra['name'] }}">
                      <span>
                        <strong>{{ $extra['name'] }}</strong>
                        <small>+ GHC {{ number_format((float) $extra['price'], 2) }}</small>
                      </span>
                      <input type="number" min="1" value="1" class="form-control cashier-extra-qty" name="items[0][extra_quantities][{{ $extra['name'] }}]" data-extra-name="{{ $extra['name'] }}" aria-label="{{ $extra['name'] }} quantity" disabled>
                    </label>
                  @endforeach
                </div>
              </div>

              <div class="cashier-field cashier-line-total">
                <label>Total</label>
                <input type="text" class="form-control sale-total" readonly>
              </div>

              <div class="cashier-product-meta sale-meta">Choose a product to show its image and stock.</div>

              <div class="cashier-remove-cell">
                <button type="button" class="btn btn-sm btn-outline-danger remove-sale-row" aria-label="Remove item">
                  <i class="bi bi-x-lg"></i>
                </button>
              </div>
            </div>
          </div>

          <div class="cashier-sale-empty mt-3" id="saleHelper">
            Start with one product. Add more rows for mixed customer orders.
          </div>
        @endif
      </div>
    </section>

    <aside class="cashier-sale-panel cashier-checkout-panel">
      <div class="cashier-sale-panel-header">
        <div class="cashier-sale-panel-title">
          <span><i class="bi bi-credit-card"></i></span>
          <div>
            <strong>Checkout</strong>
            <small>Customer and payment details.</small>
          </div>
        </div>
      </div>

      <div class="cashier-sale-panel-body">
        <div class="form-group mb-3">
          <label class="mb-2 fw-bold">Customer Name</label>
          <input class="form-control" name="customer_name" value="{{ old('customer_name', 'Walk-in') }}" required>
        </div>

        <div class="form-group mb-3">
          <label class="mb-2 fw-bold">Customer Phone <span class="text-muted fw-normal">(optional)</span></label>
          <input class="form-control" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="024 123 4567 or +233241234567" inputmode="tel">
          <div class="form-hint">A private receipt link will be sent by SMS when Zeckta is configured.</div>
        </div>

        <div class="form-group mb-3">
          <label class="mb-2 fw-bold">Payment Method</label>
          <select class="form-control" name="payment_method" required>
            <option @selected(old('payment_method') === 'Cash')>Cash</option>
            <option @selected(old('payment_method') === 'Mobile Money')>Mobile Money</option>
            <option @selected(old('payment_method') === 'Credit Card')>Credit Card</option>
          </select>
        </div>

        <div class="cashier-summary">
          <div class="cashier-summary-row">
            <span>Items</span>
            <strong id="saleItemsCount">0</strong>
          </div>
          <div class="cashier-summary-row">
            <span>Subtotal</span>
            <strong id="saleSubtotal">GHC 0.00</strong>
          </div>
          <div class="cashier-summary-row">
            <span>Tax</span>
            <strong>GHC 0.00</strong>
          </div>
          <div class="cashier-summary-row cashier-summary-total">
            <span>Total</span>
            <strong id="saleGrandTotal">GHC 0.00</strong>
          </div>
        </div>

        <button class="btn btn-success btn-block mt-3" @disabled($products->isEmpty())>
          <i class="bi bi-check2-circle"></i> Complete Sale
        </button>
      </div>
    </aside>
  </div>
</form>

@if($products->isNotEmpty())
@include('admin.products.partials.barcode-scanner')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const rows = document.getElementById('saleRows');
  const addButton = document.getElementById('add-sale-row');
  const defaultImage = @json($defaultImage);
  const subtotalText = document.getElementById('saleSubtotal');
  const grandTotalText = document.getElementById('saleGrandTotal');
  const itemsCountText = document.getElementById('saleItemsCount');
  const helper = document.getElementById('saleHelper');
  const barcodeInput = document.getElementById('barcode_scan');
  const barcodeStatus = document.getElementById('barcode_scan_status');
  const focusBarcodeButton = document.getElementById('focus_barcode_scan');
  let rowIndex = 1;

  function money(value) {
    return 'GHC ' + Number(value || 0).toFixed(2);
  }

  function selectedOption(row) {
    const select = row.querySelector('.sale-product');
    return select.options[select.selectedIndex];
  }

  function updateRow(row) {
    const selected = selectedOption(row);
    const price = Number(selected?.dataset.price || 0);
    const stock = Number(selected?.dataset.stock || 0);
    const qtyInput = row.querySelector('.sale-qty');
    let qty = Number(qtyInput.value || 0);
    const image = row.querySelector('.sale-item-image');
    const meta = row.querySelector('.sale-meta');
    const extras = rowExtrasTotal(row);

    if (selected?.value && qty > stock) {
      qty = stock;
      qtyInput.value = stock;
    }

    row.querySelector('.sale-price').value = price ? price.toFixed(2) : '';
    row.querySelector('.sale-total').value = price && qty ? ((price + extras) * qty).toFixed(2) : '';
    image.src = selected?.dataset.image || defaultImage;

    if (selected?.value) {
      meta.textContent = `Stock available: ${stock} units`;
      meta.classList.toggle('text-danger', qty > stock && stock >= 0);
    } else {
      meta.textContent = 'Choose a product to show its image and stock.';
      meta.classList.remove('text-danger');
    }

    updateSummary();
  }

  function updateSummary() {
    let total = 0;
    let items = 0;

    rows.querySelectorAll('[data-sale-row]').forEach(function (row) {
      total += Number(row.querySelector('.sale-total').value || 0);
      items += Number(row.querySelector('.sale-qty').value || 0);
    });

    subtotalText.textContent = money(total);
    grandTotalText.textContent = money(total);
    itemsCountText.textContent = items;
    helper.style.display = total > 0 ? 'none' : '';
  }

  function refreshNames() {
    rows.querySelectorAll('[data-sale-row]').forEach(function (row, index) {
      row.querySelector('.sale-product').name = `items[${index}][product_id]`;
      row.querySelector('.sale-qty').name = `items[${index}][qty]`;
      row.querySelectorAll('.sale-extra').forEach(function (input) {
        input.name = `items[${index}][extras][]`;
      });
      row.querySelectorAll('.cashier-extra-qty').forEach(function (input) {
        input.name = `items[${index}][extra_quantities][${input.dataset.extraName || input.getAttribute('aria-label').replace(/ quantity$/, '')}]`;
      });
    });
    rowIndex = rows.querySelectorAll('[data-sale-row]').length;
  }

  function setScanStatus(message, state = '') {
    if (!barcodeStatus) {
      return;
    }

    barcodeStatus.textContent = message;
    barcodeStatus.classList.toggle('is-success', state === 'success');
    barcodeStatus.classList.toggle('is-error', state === 'error');
  }

  function rowExtrasTotal(row) {
    const enabled = row.querySelector('.sale-extras-toggle')?.checked;
    let total = 0;
    row.classList.toggle('extras-enabled', Boolean(enabled));
    row.querySelectorAll('.cashier-extra-option').forEach(function (option) {
      const checkbox = option.querySelector('.sale-extra');
      const qtyInput = option.querySelector('.cashier-extra-qty');
      if (!enabled) {
        checkbox.checked = false;
      }
      if (qtyInput) {
        qtyInput.disabled = !enabled || !checkbox.checked;
      }
      option.classList.toggle('is-selected', checkbox.checked);
      if (enabled && checkbox.checked) {
        total += Number(checkbox.dataset.extraPrice || 0) * Math.max(1, Number(qtyInput.value || 1));
      }
    });
    return total;
  }

  function bindRow(row) {
    row.querySelector('.sale-product').addEventListener('change', function () {
      updateRow(row);
    });
    row.querySelector('.sale-qty').addEventListener('input', function () {
      updateRow(row);
    });
    row.querySelector('.sale-extras-toggle')?.addEventListener('change', function () {
      updateRow(row);
    });
    row.querySelectorAll('.sale-extra, .cashier-extra-qty').forEach(function (input) {
      input.addEventListener('change', function () {
        updateRow(row);
      });
      input.addEventListener('input', function () {
        updateRow(row);
      });
    });
    row.querySelector('.remove-sale-row').addEventListener('click', function () {
      if (rows.querySelectorAll('[data-sale-row]').length === 1) {
        row.querySelector('.sale-product').selectedIndex = 0;
        row.querySelector('.sale-qty').value = '';
        row.querySelector('.sale-price').value = '';
        row.querySelector('.sale-total').value = '';
        row.querySelector('.sale-extras-toggle').checked = false;
        row.classList.remove('extras-enabled');
        row.querySelectorAll('.sale-extra').forEach(function (input) {
          input.checked = false;
        });
        row.querySelectorAll('.cashier-extra-qty').forEach(function (input) {
          input.value = '1';
          input.disabled = true;
        });
        row.querySelector('.sale-item-image').src = defaultImage;
        row.querySelector('.sale-meta').textContent = 'Choose a product to show its image and stock.';
        updateSummary();
        return;
      }

      row.remove();
      refreshNames();
      updateSummary();
    });
  }

  function addEmptyRow() {
    const source = rows.querySelector('[data-sale-row]');
    const clone = source.cloneNode(true);

    clone.querySelector('.sale-product').selectedIndex = 0;
    clone.querySelector('.sale-product').name = `items[${rowIndex}][product_id]`;
    clone.querySelector('.sale-price').value = '';
    clone.querySelector('.sale-qty').name = `items[${rowIndex}][qty]`;
    clone.querySelector('.sale-qty').value = '';
    clone.querySelector('.sale-extras-toggle').checked = false;
    clone.classList.remove('extras-enabled');
    clone.querySelectorAll('.sale-extra').forEach(function (input) {
      input.name = `items[${rowIndex}][extras][]`;
      input.checked = false;
    });
    clone.querySelectorAll('.cashier-extra-qty').forEach(function (input) {
      const extraName = input.dataset.extraName || input.getAttribute('aria-label').replace(/ quantity$/, '');
      input.name = `items[${rowIndex}][extra_quantities][${extraName}]`;
      input.value = '1';
      input.disabled = true;
    });
    clone.querySelector('.sale-total').value = '';
    clone.querySelector('.sale-item-image').src = defaultImage;
    clone.querySelector('.sale-meta').textContent = 'Choose a product to show its image and stock.';
    rows.insertBefore(clone, rows.firstElementChild);
    bindRow(clone);
    refreshNames();
    rowIndex++;
    return clone;
  }

  function productOptionByBarcode(code) {
    const normalized = String(code || '').trim().toLowerCase();
    if (!normalized) {
      return null;
    }

    return [...rows.querySelector('[data-sale-row]').querySelectorAll('.sale-product option')]
      .find((option) => String(option.dataset.code || '').trim().toLowerCase() === normalized) || null;
  }

  function rowForProduct(productId) {
    return [...rows.querySelectorAll('[data-sale-row]')]
      .find((row) => row.querySelector('.sale-product').value === String(productId)) || null;
  }

  function firstEmptyRow() {
    return [...rows.querySelectorAll('[data-sale-row]')]
      .find((row) => !row.querySelector('.sale-product').value) || null;
  }

  function addBarcodeProduct(code) {
    const option = productOptionByBarcode(code);

    if (!option) {
      setScanStatus(`No product found for barcode ${code}.`, 'error');
      return;
    }

    if (option.disabled) {
      setScanStatus(`${option.textContent.trim()} is out of stock.`, 'error');
      return;
    }

    let row = rowForProduct(option.value);

    if (row) {
      const qtyInput = row.querySelector('.sale-qty');
      const stock = Number(selectedOption(row).dataset.stock || 0);
      const nextQty = Math.min(stock, Number(qtyInput.value || 0) + 1);
      qtyInput.value = nextQty;
      updateRow(row);
      setScanStatus(`${option.textContent.trim()} quantity is now ${nextQty}.`, 'success');
      return;
    }

    row = firstEmptyRow() || addEmptyRow();
    row.querySelector('.sale-product').value = option.value;
    row.querySelector('.sale-qty').value = 1;
    updateRow(row);
    setScanStatus(`${option.textContent.trim()} added to the order.`, 'success');
  }

  addButton.addEventListener('click', function () {
    addEmptyRow();
  });

  barcodeInput?.addEventListener('keydown', function (event) {
    if (event.key !== 'Enter') {
      return;
    }

    event.preventDefault();
    const code = barcodeInput.value.trim();
    if (!code) {
      setScanStatus('Scan a barcode or type a SKU first.', 'error');
      return;
    }

    addBarcodeProduct(code);
    barcodeInput.value = '';
    barcodeInput.focus();
  });

  barcodeInput?.addEventListener('barcode-scanned', function (event) {
    const code = String(event.detail?.code || barcodeInput.value || '').trim();
    if (!code) {
      return;
    }

    addBarcodeProduct(code);
    barcodeInput.value = '';
    barcodeInput.focus();
  });

  focusBarcodeButton?.addEventListener('click', function () {
    barcodeInput?.focus();
  });

  bindRow(rows.querySelector('[data-sale-row]'));
  updateSummary();
  barcodeInput?.focus();
});
</script>
@endif
@endsection
