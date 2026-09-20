@extends('layouts.pos-admin')

@section('title', 'Create POS Order - ONJECASA POS')
@section('page-eyebrow', 'In-Store Checkout')
@section('page-title', 'Create POS Order')
@section('page-description', 'Build a walk-in order, confirm product photos, and complete payment from one screen.')
@section('page-actions')
  <a href="{{ route('pos.admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> Back to Orders
  </a>
@endsection

@section('content')
<style>
  .pos-order-shell {
    display: grid;
    gap: 1rem;
  }

  .pos-order-hero {
    display: flex;
    align-items: stretch;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem;
    border: 1px solid var(--admin-border);
    border-radius: 8px;
    background: linear-gradient(135deg, rgba(138, 18, 79, .08), rgba(242, 199, 92, .16));
    box-shadow: var(--admin-shadow-sm);
  }

  .pos-order-hero h2 {
    margin: 0;
    color: var(--admin-text);
    font-size: 1.25rem;
    line-height: 1.2;
  }

  .pos-order-hero p {
    margin: .35rem 0 0;
    color: var(--admin-muted);
    max-width: 52rem;
    line-height: 1.45;
  }

  .pos-order-code {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    min-width: max-content;
    padding: .75rem .9rem;
    border: 1px solid rgba(246, 205, 112, .42);
    border-radius: 8px;
    background: var(--admin-surface);
    color: var(--admin-primary);
    font-weight: 800;
  }

  .pos-scan-panel {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto auto;
    gap: .75rem;
    align-items: end;
    padding: 1rem;
    border: 1px solid var(--admin-border);
    border-radius: 8px;
    background: var(--admin-surface);
    box-shadow: var(--admin-shadow-sm);
  }

  .pos-scan-field label {
    display: block;
    margin-bottom: .35rem;
    color: var(--admin-muted);
    font-size: .72rem;
    font-weight: 800;
    letter-spacing: .06em;
    text-transform: uppercase;
  }

  .pos-scan-input-wrap {
    position: relative;
  }

  .pos-scan-input-wrap i {
    position: absolute;
    left: .9rem;
    top: 50%;
    color: var(--admin-muted);
    transform: translateY(-50%);
    pointer-events: none;
  }

  .pos-scan-input-wrap .form-control {
    min-height: 3rem;
    padding-left: 2.65rem;
    font-weight: 800;
  }

  .pos-scan-status {
    min-height: 1.25rem;
    margin-top: .4rem;
    color: var(--admin-muted);
    font-size: .82rem;
    font-weight: 700;
  }

  .pos-scan-status.is-success {
    color: #166534;
  }

  .pos-scan-status.is-error {
    color: #b42318;
  }

  .pos-order-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(300px, .42fr);
    gap: 1rem;
    align-items: start;
  }

  .pos-order-panel {
    min-width: 0;
    border: 1px solid var(--admin-border);
    border-radius: 8px;
    background: var(--admin-surface);
    box-shadow: var(--admin-shadow-sm);
    overflow: hidden;
  }

  .pos-order-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: 1rem;
    border-bottom: 1px solid var(--admin-border);
    background: var(--admin-surface-soft);
  }

  .pos-order-panel-title {
    display: flex;
    align-items: center;
    gap: .75rem;
    min-width: 0;
  }

  .pos-order-panel-title span {
    width: 38px;
    height: 38px;
    display: inline-grid;
    place-items: center;
    flex: 0 0 auto;
    border-radius: 8px;
    background: rgba(242, 199, 92, .16);
    color: var(--admin-primary);
  }

  .pos-order-panel-title strong,
  .pos-order-panel-title small {
    display: block;
  }

  .pos-order-panel-title strong {
    color: var(--admin-text);
    line-height: 1.2;
  }

  .pos-order-panel-title small {
    margin-top: .15rem;
    color: var(--admin-muted);
    line-height: 1.35;
  }

  .pos-order-panel-body {
    min-width: 0;
    padding: 1rem;
  }

  .pos-order-items {
    display: grid;
    gap: .85rem;
    min-width: 0;
  }

  .pos-order-item {
    display: grid;
    grid-template-columns: 72px minmax(0, 1fr) minmax(88px, .28fr) minmax(76px, .24fr) 38px;
    gap: .75rem;
    align-items: end;
    min-width: 0;
    padding: .85rem;
    border: 1px solid var(--admin-border);
    border-radius: 8px;
    background: var(--admin-surface);
  }

  .pos-order-thumb {
    width: 72px;
    aspect-ratio: 1;
    align-self: stretch;
    border: 1px solid var(--admin-border);
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
  }

  .pos-order-thumb img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
  }

  .pos-order-item .form-control,
  .pos-order-item .pos-field {
    min-width: 0;
    max-width: 100%;
  }

  .pos-order-item .pos-line-total {
    grid-column: 2 / 4;
  }

  .pos-order-item .pos-extras-field {
    grid-column: 2 / -1;
  }

  .pos-extras-toggle {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    width: fit-content;
    margin-bottom: .55rem;
    padding: .45rem .75rem;
    border: 1px solid var(--admin-border);
    border-radius: 999px;
    background: var(--admin-surface-soft);
    color: var(--admin-text);
    font-size: .78rem;
    font-weight: 900;
    cursor: pointer;
  }

  .pos-extras-toggle input {
    width: 16px;
    height: 16px;
    accent-color: var(--admin-primary);
  }

  .pos-extras-grid {
    display: none;
    grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
    gap: .5rem;
  }

  .pos-order-item.extras-enabled .pos-extras-grid {
    display: grid;
  }

  .pos-extra-option {
    display: grid;
    grid-template-columns: 18px minmax(0, 1fr) 58px;
    gap: .45rem;
    align-items: center;
    min-width: 0;
    margin: 0;
    padding: .55rem;
    border: 1px solid var(--admin-border);
    border-radius: 8px;
    background: var(--admin-surface-soft);
    cursor: pointer;
  }

  .pos-extra-option input[type="checkbox"] {
    width: 16px;
    height: 16px;
    accent-color: var(--admin-primary);
  }

  .pos-extra-option strong,
  .pos-extra-option small {
    display: block;
    min-width: 0;
    line-height: 1.2;
  }

  .pos-extra-option strong {
    color: var(--admin-text);
    font-size: .78rem;
  }

  .pos-extra-option small {
    margin-top: .15rem;
    color: var(--admin-primary);
    font-size: .72rem;
    font-weight: 800;
  }

  .pos-extra-qty {
    height: 34px;
    padding: .25rem;
    text-align: center;
  }

  .pos-extra-qty:disabled {
    opacity: .45;
  }

  .pos-order-item .pos-product-meta {
    grid-column: 4 / 5;
    align-self: center;
    margin-top: 0;
  }

  .pos-field label {
    margin-bottom: .35rem;
    color: var(--admin-muted);
    font-size: .72rem;
    font-weight: 800;
    letter-spacing: .06em;
    text-transform: uppercase;
  }

  .pos-product-meta {
    margin-top: .35rem;
    color: var(--admin-muted);
    font-size: .78rem;
    line-height: 1.3;
  }

  .pos-remove-cell {
    align-self: center;
    justify-self: end;
  }

  .pos-checkout-panel {
    position: sticky;
    top: 18px;
  }

  .pos-payment-options {
    display: grid;
    gap: .65rem;
  }

  .pos-summary {
    display: grid;
    gap: .65rem;
    margin-top: 1rem;
    padding: 1rem;
    border: 1px dashed var(--admin-border);
    border-radius: 8px;
    background: var(--admin-surface-soft);
  }

  .pos-summary-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    color: var(--admin-muted);
  }

  .pos-summary-total {
    padding-top: .75rem;
    border-top: 1px solid var(--admin-border);
    color: var(--admin-text);
    font-size: 1.2rem;
    font-weight: 900;
  }

  .pos-order-empty {
    padding: .9rem 1rem;
    border: 1px solid rgba(242, 199, 92, .36);
    border-radius: 8px;
    background: rgba(242, 199, 92, .12);
    color: var(--admin-muted);
    font-size: .9rem;
  }

  @media (max-width: 1399.98px) {
    .pos-order-item {
      grid-template-columns: 72px minmax(0, 1fr) minmax(86px, .32fr) minmax(76px, .28fr) 38px;
    }
  }

  @media (max-width: 991.98px) {
    .pos-order-grid {
      grid-template-columns: 1fr;
    }

    .pos-checkout-panel {
      position: static;
    }

    .pos-order-hero {
      flex-direction: column;
    }

    .pos-order-code {
      width: fit-content;
    }

    .pos-scan-panel {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 767.98px) {
    .pos-scan-panel {
      padding: .85rem;
    }

    .pos-scan-panel .btn {
      width: 100%;
      justify-content: center;
    }

    .pos-order-item {
      grid-template-columns: 70px minmax(0, 1fr) 38px;
      align-items: start;
    }

    .pos-order-item .pos-field {
      grid-column: 2 / 3;
    }

    .pos-order-item .pos-price-field,
    .pos-order-item .pos-qty-field,
    .pos-order-item .pos-extras-field,
    .pos-order-item .pos-line-total {
      grid-column: 1 / -1;
    }

    .pos-order-item .pos-product-meta {
      grid-column: 1 / -1;
    }

    .pos-remove-cell {
      grid-column: 3 / 4;
      grid-row: 1;
      align-self: start;
    }
  }
</style>

<form method="POST" action="{{ route('pos.admin.orders.store') }}" id="order-form" class="pos-order-shell">
  @csrf

  <section class="pos-order-hero">
    <div>
      <h2>Store Checkout</h2>
      <p>Select each product, confirm the product photo, add quantities, and complete the order. Mobile Money still opens Paystack before the receipt is generated.</p>
    </div>
    <div class="pos-order-code">
      <i class="bi bi-receipt-cutoff"></i>
      ORD-{{ now()->format('YmdHis') }}
    </div>
  </section>

  <section class="pos-scan-panel">
    <div class="pos-scan-field">
      <label for="barcode_scan">Barcode Scan</label>
      <div class="pos-scan-input-wrap">
        <i class="bi bi-upc-scan" aria-hidden="true"></i>
        <input id="barcode_scan" type="text" class="form-control" inputmode="numeric" autocomplete="off" placeholder="Scan barcode or type SKU and press Enter" data-hardware-barcode-capture data-barcode-camera-continuous>
      </div>
      <div class="pos-scan-status" id="barcode_scan_status">Ready for continuous barcode scans.</div>
    </div>
    <button type="button" class="btn btn-outline-secondary" data-open-barcode-camera data-barcode-target="#barcode_scan">
      <i class="bi bi-camera-video"></i> Open Camera Scanner
    </button>
    <button type="button" class="btn btn-outline-secondary" id="focus_barcode_scan">
      <i class="bi bi-crosshair"></i> Focus Scanner
    </button>
  </section>

  <div class="pos-order-grid">
    <section class="pos-order-panel">
      <div class="pos-order-panel-header">
        <div class="pos-order-panel-title">
          <span><i class="bi bi-basket2"></i></span>
          <div>
            <strong>Basket Items</strong>
            <small>Selected products show their photo, stock, price, and line total.</small>
          </div>
        </div>
        <button type="button" class="btn btn-outline-success btn-sm" id="btn-add-row">
          <i class="fas fa-plus"></i> Add Item
        </button>
      </div>

      <div class="pos-order-panel-body">
        <div class="pos-order-items" id="product_tbody">
          <div class="pos-order-item" data-order-row>
            <div class="pos-order-thumb">
              <img class="item-image" src="{{ asset('upload/no_image.jpg') }}" alt="Selected product image">
            </div>

            <div class="pos-field">
              <label>Product</label>
              <select class="form-control item-product" name="items[0][product_id]" required>
                <option value="" data-price="" data-stock="" data-image="{{ asset('upload/no_image.jpg') }}">Select product</option>
                @foreach($products as $product)
                  @php
                    $imageUrl = $product->image
                        ? asset('assets/admin/img/products/' . $product->image)
                        : asset('upload/no_image.jpg');
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

            <div class="pos-field pos-price-field">
              <label>Price</label>
              <input type="text" class="form-control item-price" readonly>
            </div>

            <div class="pos-field pos-qty-field">
              <label>Qty</label>
              <input type="number" min="1" name="items[0][qty]" class="form-control item-qty" placeholder="1" required>
            </div>

            <div class="pos-field pos-extras-field">
              <label>Add-ons</label>
              <label class="pos-extras-toggle">
                <input type="checkbox" class="item-extras-toggle">
                <span>Add add-ons</span>
              </label>
              <div class="pos-extras-grid">
                @foreach($mealExtras as $extra)
                  <label class="pos-extra-option">
                    <input type="checkbox" class="item-extra" name="items[0][extras][]" value="{{ $extra['name'] }}" data-extra-price="{{ $extra['price'] }}" data-extra-name="{{ $extra['name'] }}">
                    <span>
                      <strong>{{ $extra['name'] }}</strong>
                      <small>+ GHC {{ number_format((float) $extra['price'], 2) }}</small>
                    </span>
                    <input type="number" min="1" value="1" class="form-control pos-extra-qty" name="items[0][extra_quantities][{{ $extra['name'] }}]" data-extra-name="{{ $extra['name'] }}" aria-label="{{ $extra['name'] }} quantity" disabled>
                  </label>
                @endforeach
              </div>
            </div>

            <div class="pos-field pos-line-total">
              <label>Total</label>
              <input type="text" class="form-control item-total" readonly>
            </div>

            <div class="pos-product-meta item-meta">Choose a product to show its photo and available stock.</div>

            <div class="pos-remove-cell">
              <button type="button" class="btn btn-sm btn-outline-danger btn-row-remove" aria-label="Remove order item">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
        </div>

        <div class="pos-order-empty mt-3" id="order-helper">
          Start by choosing a product. Add more rows when the customer wants multiple items.
        </div>
      </div>
    </section>

    <aside class="pos-order-panel pos-checkout-panel">
      <div class="pos-order-panel-header">
        <div class="pos-order-panel-title">
          <span><i class="bi bi-credit-card"></i></span>
          <div>
            <strong>Checkout</strong>
            <small>Walk-in customer and payment summary.</small>
          </div>
        </div>
      </div>

      <div class="pos-order-panel-body">
        <input type="hidden" name="customer_name" value="Walk-in">

        <div class="form-group mb-3">
          <label class="mb-2">Customer Phone <span class="text-muted">(optional)</span></label>
          <input class="form-control" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="024 123 4567 or +233241234567" inputmode="tel">
          <div class="form-hint">A private receipt link will be sent by SMS when Zeckta is configured.</div>
        </div>

        <div class="form-group mb-3">
          <label class="mb-2">Payment Method</label>
          <select class="form-control" name="payment_method" id="payment_method" required>
            <option>Cash</option>
            <option>Mobile Money</option>
          </select>
          <input type="hidden" name="paystack_reference" id="paystack_reference">
          <div class="form-hint">Mobile Money uses Paystack before the receipt opens.</div>
        </div>

        <div class="pos-summary">
          <div class="pos-summary-row">
            <span>Items</span>
            <strong id="items_count_text">0</strong>
          </div>
          <div class="pos-summary-row">
            <span>Subtotal</span>
            <strong id="subtotal_text">0.00</strong>
          </div>
          <div class="pos-summary-row">
            <span>Tax</span>
            <strong>0.00</strong>
          </div>
          <div class="pos-summary-row pos-summary-total">
            <span>Total</span>
            <strong id="total_text">0.00</strong>
          </div>
        </div>

        <input type="hidden" id="grand_total" value="0">

        <button type="submit" class="btn btn-success btn-block mt-3" id="submit_order_btn">
          <i class="fas fa-check-circle"></i> Complete Payment
        </button>
      </div>
    </aside>
  </div>
</form>

@include('admin.products.partials.barcode-scanner')
<script>
(() => {
  const paystackPublicKey = @json(config('services.paystack.public_key'));
  const defaultImage = @json(asset('upload/no_image.jpg'));
  const tbody = document.getElementById('product_tbody');
  const addBtn = document.getElementById('btn-add-row');
  const paymentMethod = document.getElementById('payment_method');
  const orderForm = document.getElementById('order-form');
  const paystackRefInput = document.getElementById('paystack_reference');
  const grandTotalInput = document.getElementById('grand_total');
  const subtotalText = document.getElementById('subtotal_text');
  const totalText = document.getElementById('total_text');
  const itemsCountText = document.getElementById('items_count_text');
  const helper = document.getElementById('order-helper');
  const barcodeInput = document.getElementById('barcode_scan');
  const barcodeStatus = document.getElementById('barcode_scan_status');
  const focusBarcodeButton = document.getElementById('focus_barcode_scan');
  let receiptWindow = null;
  let rowIndex = 1;

  function selectedOption(row) {
    const select = row.querySelector('.item-product');
    return select.options[select.selectedIndex];
  }

  function refreshRowProduct(row) {
    const selected = selectedOption(row);
    const image = row.querySelector('.item-image');
    const meta = row.querySelector('.item-meta');
    const price = Number(selected ? selected.dataset.price || 0 : 0);
    const stock = selected ? selected.dataset.stock : '';
    const productName = selected ? selected.textContent.trim() : '';

    image.src = selected && selected.dataset.image ? selected.dataset.image : defaultImage;
    image.alt = productName && selected.value ? productName : 'Selected product image';

    if (selected && selected.value) {
      meta.textContent = `Stock available: ${stock || 0} units`;
    } else {
      meta.textContent = 'Choose a product to show its photo and available stock.';
    }

    row.querySelector('.item-price').value = price ? price.toFixed(2) : '';
  }

  function recalcRow(row) {
    const selected = selectedOption(row);
    const qtyInput = row.querySelector('.item-qty');
    const totalInput = row.querySelector('.item-total');
    const price = selected ? Number(selected.dataset.price || 0) : 0;
    const stock = selected ? Number(selected.dataset.stock || 0) : 0;
    let qty = Number(qtyInput.value || 0);
    const extras = rowExtrasTotal(row);

    if (selected && selected.value && qty > stock) {
      qty = stock;
      qtyInput.value = stock;
    }

    const total = (price + extras) * qty;

    refreshRowProduct(row);
    totalInput.value = total ? total.toFixed(2) : '';
    recalcGrandTotal();
  }

  function recalcGrandTotal() {
    let total = 0;
    let items = 0;

    tbody.querySelectorAll('[data-order-row]').forEach((row) => {
      total += Number(row.querySelector('.item-total').value || 0);
      items += Number(row.querySelector('.item-qty').value || 0);
    });

    grandTotalInput.value = total.toFixed(2);
    subtotalText.textContent = total.toFixed(2);
    totalText.textContent = total.toFixed(2);
    itemsCountText.textContent = items;
    helper.style.display = total > 0 ? 'none' : '';
  }

  function refreshNames() {
    tbody.querySelectorAll('[data-order-row]').forEach((row, index) => {
      row.querySelector('.item-product').name = `items[${index}][product_id]`;
      row.querySelector('.item-qty').name = `items[${index}][qty]`;
      row.querySelectorAll('.item-extra').forEach((input) => {
        input.name = `items[${index}][extras][]`;
      });
      row.querySelectorAll('.pos-extra-qty').forEach((input) => {
        input.name = `items[${index}][extra_quantities][${input.dataset.extraName || input.getAttribute('aria-label').replace(/ quantity$/, '')}]`;
      });
    });
    rowIndex = tbody.querySelectorAll('[data-order-row]').length;
  }

  function setScanStatus(message, state = '') {
    barcodeStatus.textContent = message;
    barcodeStatus.classList.toggle('is-success', state === 'success');
    barcodeStatus.classList.toggle('is-error', state === 'error');
  }

  function rowExtrasTotal(row) {
    const enabled = row.querySelector('.item-extras-toggle')?.checked;
    let total = 0;
    row.classList.toggle('extras-enabled', Boolean(enabled));
    row.querySelectorAll('.pos-extra-option').forEach((option) => {
      const checkbox = option.querySelector('.item-extra');
      const qtyInput = option.querySelector('.pos-extra-qty');
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
    row.querySelector('.item-product').addEventListener('change', () => recalcRow(row));
    row.querySelector('.item-qty').addEventListener('input', () => recalcRow(row));
    row.querySelector('.item-extras-toggle')?.addEventListener('change', () => recalcRow(row));
    row.querySelectorAll('.item-extra, .pos-extra-qty').forEach((input) => {
      input.addEventListener('change', () => recalcRow(row));
      input.addEventListener('input', () => recalcRow(row));
    });
    row.querySelector('.btn-row-remove').addEventListener('click', () => {
      if (tbody.querySelectorAll('[data-order-row]').length === 1) {
        row.querySelector('.item-product').selectedIndex = 0;
        row.querySelector('.item-qty').value = '';
        row.querySelector('.item-total').value = '';
        row.querySelector('.item-extras-toggle').checked = false;
        row.classList.remove('extras-enabled');
        row.querySelectorAll('.item-extra').forEach((input) => input.checked = false);
        row.querySelectorAll('.pos-extra-qty').forEach((input) => {
          input.value = '1';
          input.disabled = true;
        });
        refreshRowProduct(row);
        recalcGrandTotal();
        return;
      }

      row.remove();
      refreshNames();
      recalcGrandTotal();
    });
    refreshRowProduct(row);
  }

  function addEmptyRow() {
    const template = tbody.querySelector('[data-order-row]');
    const clone = template.cloneNode(true);

    clone.querySelector('.item-product').name = `items[${rowIndex}][product_id]`;
    clone.querySelector('.item-product').selectedIndex = 0;
    clone.querySelector('.item-price').value = '';
    clone.querySelector('.item-qty').name = `items[${rowIndex}][qty]`;
    clone.querySelector('.item-qty').value = '';
    clone.querySelector('.item-extras-toggle').checked = false;
    clone.classList.remove('extras-enabled');
    clone.querySelectorAll('.item-extra').forEach((input) => {
      input.name = `items[${rowIndex}][extras][]`;
      input.checked = false;
    });
    clone.querySelectorAll('.pos-extra-qty').forEach((input) => {
      const extraName = input.dataset.extraName || input.getAttribute('aria-label').replace(/ quantity$/, '');
      input.name = `items[${rowIndex}][extra_quantities][${extraName}]`;
      input.value = '1';
      input.disabled = true;
    });
    clone.querySelector('.item-total').value = '';
    clone.querySelector('.item-image').src = defaultImage;
    clone.querySelector('.item-meta').textContent = 'Choose a product to show its photo and available stock.';
    tbody.insertBefore(clone, tbody.firstElementChild);
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

    return [...tbody.querySelector('[data-order-row]').querySelectorAll('.item-product option')]
      .find((option) => String(option.dataset.code || '').trim().toLowerCase() === normalized) || null;
  }

  function rowForProduct(productId) {
    return [...tbody.querySelectorAll('[data-order-row]')]
      .find((row) => row.querySelector('.item-product').value === String(productId)) || null;
  }

  function firstEmptyRow() {
    return [...tbody.querySelectorAll('[data-order-row]')]
      .find((row) => !row.querySelector('.item-product').value) || null;
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
      const qtyInput = row.querySelector('.item-qty');
      const stock = Number(selectedOption(row).dataset.stock || 0);
      const nextQty = Math.min(stock, Number(qtyInput.value || 0) + 1);
      qtyInput.value = nextQty;
      recalcRow(row);
      setScanStatus(`${option.textContent.trim()} quantity is now ${nextQty}.`, 'success');
      return;
    }

    row = firstEmptyRow() || addEmptyRow();
    row.querySelector('.item-product').value = option.value;
    row.querySelector('.item-qty').value = 1;
    recalcRow(row);
    setScanStatus(`${option.textContent.trim()} added to the order.`, 'success');
  }

  addBtn.addEventListener('click', () => {
    addEmptyRow();
  });

  barcodeInput?.addEventListener('keydown', (event) => {
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

  barcodeInput?.addEventListener('barcode-scanned', (event) => {
    const code = String(event.detail?.code || barcodeInput.value || '').trim();
    if (!code) {
      return;
    }

    addBarcodeProduct(code);
    barcodeInput.value = '';
    barcodeInput.focus();
  });

  focusBarcodeButton?.addEventListener('click', () => {
    barcodeInput?.focus();
  });

  bindRow(tbody.querySelector('[data-order-row]'));
  recalcGrandTotal();
  barcodeInput?.focus();

  orderForm.addEventListener('submit', function (e) {
    if (paymentMethod.value !== 'Mobile Money') {
      orderForm.target = '_self';
      return;
    }
    e.preventDefault();
    const amount = Math.round(Number(grandTotalInput.value || 0) * 100);
    if (!amount || amount < 100) { orderForm.target = '_self'; alert('Please add at least one valid order item.'); return; }
    if (!paystackPublicKey) { orderForm.target = '_self'; alert('Paystack public key is not configured.'); return; }

    receiptWindow = window.open('', 'ONJECASAPOSReceiptWindow');
    if (!receiptWindow) {
      orderForm.target = '_self';
      alert('Allow popups so the receipt can open after payment.');
      return;
    }

    receiptWindow.document.write(`<!doctype html>
      <html lang="en">
        <head>
          <meta charset="utf-8">
          <title>Preparing receipt...</title>
          <style>
            body {
              margin: 0;
              min-height: 100vh;
              display: grid;
              place-items: center;
              font-family: "Segoe UI", Arial, sans-serif;
              background: #eef3ef;
              color: #14532d;
            }
            .box {
              padding: 18px 22px;
              border-radius: 14px;
              background: #ffffff;
              border: 1px solid #d8e4db;
              box-shadow: 0 10px 26px rgba(15, 23, 42, 0.08);
              font-weight: 700;
            }
          </style>
        </head>
        <body><div class="box">Preparing receipt...</div></body>
      </html>`);
    receiptWindow.document.close();
    orderForm.target = 'ONJECASAPOSReceiptWindow';

    const handler = PaystackPop.setup({
      key: paystackPublicKey,
      email: @json(auth()->user()->email ?? 'admin@example.com'),
      amount, currency: 'GHS',
      callback: function(response) {
        paystackRefInput.value = response.reference;
        orderForm.submit();
      },
      onClose: function() {
        if (receiptWindow && !receiptWindow.closed) {
          receiptWindow.close();
        }
        orderForm.target = '_self';
        alert('Payment window closed.');
      }
    });
    handler.openIframe();
  });
})();
</script>
<script src="https://js.paystack.co/v1/inline.js"></script>
@endsection


