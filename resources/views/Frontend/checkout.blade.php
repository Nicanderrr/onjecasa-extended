@extends('Frontend.layout')

@section('title', 'Checkout - ONJECASA')

@section('full_width_content')
@php
    $subtotal = $cartItems->sum(fn ($item) => \App\Support\MealExtras::lineTotal($item));
    $shipping = 0;
    $grandTotal = $subtotal;
    $fulfillmentMethod = old('fulfillment_method', 'delivery');
    $branchOptions = $branches->map(fn ($branch) => [
        'id' => $branch->id,
        'name' => $branch->name,
        'address' => $branch->address,
        'latitude' => $branch->latitude !== null ? (float) $branch->latitude : null,
        'longitude' => $branch->longitude !== null ? (float) $branch->longitude : null,
        'base_delivery_fee' => (float) ($branch->base_delivery_fee ?? 0),
        'delivery_fee_per_km' => (float) ($branch->delivery_fee_per_km ?? 0),
        'max_delivery_distance_km' => $branch->max_delivery_distance_km !== null ? (float) $branch->max_delivery_distance_km : null,
    ])->values();
@endphp

<div class="onjecasa-checkout-page">
    <section class="page-header">
        <div class="page-header__bg" style="background-image: url({{ asset('themes/zabaga/assets/images/backgrounds/page-header-bg.jpg') }});"></div>
        <div class="page-header__shape-bg" style="background-image: url({{ asset('themes/zabaga/assets/images/shapes/page-header-shape-bg.png') }});"></div>
        <div class="page-header__shape-1 float-bob-y">
            <img src="{{ asset('themes/zabaga/assets/images/shapes/page-header-shape-1.png') }}" alt="">
        </div>
        <div class="page-header__shape-2 float-bob-x">
            <img src="{{ asset('themes/zabaga/assets/images/shapes/page-header-shape-2.png') }}" alt="">
        </div>
        <div class="container">
            <div class="page-header__inner">
                <h2>Checkout</h2>
                <div class="thm-breadcrumb__box">
                    <ul class="thm-breadcrumb list-unstyled">
                        <li><a href="{{ route('home_page') }}">Home</a></li>
                        <li><span class="icon-right-arrow"></span></li>
                        <li><a href="{{ route('view_cart') }}">Cart</a></li>
                        <li><span class="icon-right-arrow"></span></li>
                        <li>Checkout</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="onjecasa-checkout">
        <div class="container">
            <div class="section-title text-center">
                <div class="section-title__tagline-box">
                    <span class="section-title__tagline">Secure Checkout</span>
                </div>
                <h2 class="section-title__title">Complete Your <span>ONJECASA Order.</span></h2>
            </div>

            @if($cartItems->count() > 0)
                <form action="{{ route('checkout.confirm') }}" method="POST" id="checkoutForm">
                    @csrf
                    <input type="hidden" name="delivery_latitude" id="deliveryLatitude" value="{{ old('delivery_latitude') }}">
                    <input type="hidden" name="delivery_longitude" id="deliveryLongitude" value="{{ old('delivery_longitude') }}">
                    <div class="onjecasa-checkout-grid">
                        <section class="onjecasa-checkout-card onjecasa-checkout-card--main">
                            <div class="checkout-card-heading">
                                <span>Fulfillment</span>
                                <h3>Customer Details</h3>
                            </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="field-label">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="shop-input" placeholder="+233..." required>
                    </div>
                    <div class="col-md-6">
                        <label class="field-label">Payment Method</label>
                        <input type="hidden" name="payment_method" value="paystack">
                        <select name="payment_channel" class="shop-select" required>
                            <option value="card" {{ old('payment_channel', 'card') === 'card' ? 'selected' : '' }}>Card via Paystack</option>
                            <option value="mobile_money" {{ old('payment_channel') === 'mobile_money' ? 'selected' : '' }}>Mobile Money via Paystack</option>
                            <option value="bank_transfer" {{ old('payment_channel') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer via Paystack</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="field-label">Ordering Branch</label>
                        <select name="branch_id" id="branchSelect" class="shop-select" required>
                            <option value="">Choose the store handling this order</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @selected((int) old('branch_id') === (int) $branch->id)>
                                    {{ $branch->name }}{{ $branch->address ? ' - '.$branch->address : '' }}
                                </option>
                            @endforeach
                        </select>
                        <small id="branchHint" class="muted-copy">Your order will be sent to this store for pickup or fulfillment.</small>
                    </div>
                    <div class="col-12">
                        <label class="field-label">How do you want to receive the order?</label>
                        <div class="fulfillment-options">
                            <label class="fulfillment-option">
                                <input type="radio" name="fulfillment_method" value="delivery" @checked($fulfillmentMethod === 'delivery')>
                                <span>
                                    <strong>Delivery</strong>
                                    <small>Enter a delivery address below.</small>
                                </span>
                            </label>
                            <label class="fulfillment-option">
                                <input type="radio" name="fulfillment_method" value="pickup" @checked($fulfillmentMethod === 'pickup')>
                                <span>
                                    <strong>Pickup</strong>
                                    <small>I will collect it from the store.</small>
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="field-label" id="addressLabel">Delivery Address</label>
                        <textarea name="address" id="checkoutAddress" class="shop-textarea" rows="5" placeholder="House number, street, area, city, and nearest landmark">{{ old('address') }}</textarea>
                        <small id="deliveryHint" class="muted-copy">This address is saved with the order for delivery.</small>
                        <small id="pickupHint" class="muted-copy d-none">No delivery address is needed for pickup orders.</small>
                    </div>
                    <div class="col-12" id="deliveryMapSection">
                        <label class="field-label">Pin your delivery location</label>
                        <div class="checkout-map-toolbar">
                            <button type="button" class="shop-btn shop-btn-outline" id="useMyLocationBtn">Use My Location</button>
                            <span id="nearestBranchHint" class="muted-copy">Pick a point on the map to calculate delivery.</span>
                        </div>
                        <div id="checkoutMap" class="checkout-map"></div>
                        <small id="mapStatus" class="muted-copy d-block mt-2">Tap the map or use your current location.</small>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="shop-btn shop-btn-primary w-100">Confirm Order</button>
                    </div>
                </div>
                        </section>

                        <aside class="onjecasa-checkout-card onjecasa-checkout-card--summary">
                            <div class="checkout-card-heading">
                                <span>Summary</span>
                                <h3>Order Summary</h3>
                            </div>
                <div class="line-list">
                    @foreach($cartItems as $item)
                        <div class="line-item" style="grid-template-columns:64px 1fr;">
                            <img src="{{ $item->product->picture_url ?? asset('images/onjecasa-products.svg') }}" alt="{{ $item->product->name }}" class="line-thumb" style="width:64px;height:64px;" onerror="this.onerror=null;this.src='{{ asset('images/onjecasa-products.svg') }}';">
                            <div>
                                <div class="fw-bold text-dark">{{ $item->product->name }}</div>
                                <div class="small text-muted">{{ $item->quantity }} x GHC {{ number_format(\App\Support\MealExtras::unitPrice($item), 2) }}</div>
                                <div class="chip-row mt-1">
                                    @if($item->size)<span class="shop-chip">{{ $item->size }}</span>@endif
                                    @if(\App\Support\MealExtras::label($item->color))<span class="shop-chip">{{ \App\Support\MealExtras::label($item->color) }}</span>@endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <hr>
                <div class="summary-row"><span class="muted-copy">Subtotal</span><strong>GHC {{ number_format($subtotal, 2) }}</strong></div>
                <div class="summary-row"><span class="muted-copy">Delivery</span><strong id="shippingTotal">GHC {{ number_format($shipping, 2) }}</strong></div>
                <div class="summary-row"><span class="muted-copy">Distance</span><strong id="distanceTotal">Select location</strong></div>
                <div class="summary-row fs-4"><span>Total</span><strong class="price-text" id="grandTotal">GHC {{ number_format($grandTotal, 2) }}</strong></div>
                        </aside>
                    </div>
                </form>
            @else
                <div class="onjecasa-checkout-card onjecasa-empty-checkout text-center">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Your cart is empty.</h3>
                    <p>Add products before checkout.</p>
                    <a href="{{ route('product_page') }}" class="thm-btn">
                        <span class="thm-btn-text">Shop Products</span>
                        <span class="thm-btn-icon-box"><i class="fas fa-arrow-right"></i></span>
                    </a>
                </div>
            @endif
        </div>
    </section>
</div>

@endsection

@push('styles')
<link href="{{ asset('themes/zabaga/assets/css/module-css/page-header.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
    .onjecasa-template-main { padding: 0; background: #fff; }
    .onjecasa-checkout-page {
        position: relative;
        width: 100%;
        max-width: 100%;
        overflow: hidden;
    }
    .onjecasa-checkout-page .page-header {
        padding: 150px 0 138px;
        -webkit-mask-size: 100% 100%;
        mask-size: 100% 100%;
    }
    .onjecasa-checkout {
        padding: 105px 0;
        background: #fff;
    }
    .onjecasa-checkout-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 430px;
        gap: 30px;
        align-items: start;
    }
    .onjecasa-checkout-card {
        position: relative;
        padding: 34px;
        border: 1px solid rgba(var(--helpest-black-rgb), .12);
        border-radius: 14px;
        background: var(--helpest-white);
        box-shadow: 0 22px 60px rgba(var(--helpest-black-rgb), .10);
    }
    .onjecasa-checkout-card--summary {
        position: sticky;
        top: 120px;
        background: var(--helpest-extra);
    }
    .checkout-card-heading {
        margin-bottom: 26px;
    }
    .checkout-card-heading span {
        display: inline-block;
        margin-bottom: 8px;
        color: var(--helpest-base);
        font-size: 13px;
        line-height: 1;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .08em;
    }
    .checkout-card-heading h3 {
        color: var(--helpest-black);
        font-size: 32px;
        line-height: 1.08;
        font-weight: 900;
    }
    .onjecasa-checkout-page .field-label {
        color: var(--helpest-black);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .08em;
    }
    .onjecasa-checkout-page .shop-input,
    .onjecasa-checkout-page .shop-select,
    .onjecasa-checkout-page .shop-textarea {
        border: 1px solid rgba(var(--helpest-black-rgb), .14);
        border-radius: 8px;
        background: #fff;
        color: var(--helpest-black);
        font-weight: 800;
    }
    .checkout-email-verify {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        gap: 12px;
        align-items: center;
        padding: 12px;
        border: 1px solid rgba(var(--helpest-black-rgb), .12);
        border-radius: 10px;
        background: var(--helpest-extra);
    }
    .checkout-email-value {
        min-width: 0;
        color: var(--helpest-black);
        font-size: 15px;
        font-weight: 900;
        overflow-wrap: anywhere;
    }
    .onjecasa-checkout-page .shop-chip {
        border: 0;
        border-radius: 999px;
        background: var(--helpest-black);
        color: #fff;
        font-weight: 900;
    }
    .onjecasa-checkout-page .shop-chip.is-strong {
        background: var(--helpest-base);
        color: #fff;
    }
    .onjecasa-checkout-page .shop-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 48px;
        border-radius: 999px !important;
        padding: 12px 22px;
        font-weight: 900;
        text-transform: uppercase;
    }
    .onjecasa-checkout-page .shop-btn-primary {
        border: 0;
        background: var(--helpest-base);
        color: #fff;
    }
    .onjecasa-checkout-page .shop-btn-outline {
        border: 1px solid rgba(var(--helpest-black-rgb), .18);
        background: #fff;
        color: var(--helpest-black);
    }
    .onjecasa-checkout-page .fulfillment-option {
        border-color: rgba(var(--helpest-black-rgb), .12);
        border-radius: 10px;
        background: #fff;
    }
    .onjecasa-checkout-page .fulfillment-option:has(input:checked) {
        border-color: var(--helpest-base);
        background: #eef4ff;
    }
    .checkout-map {
        width: 100%;
        height: 320px;
        border: 1px solid rgba(var(--helpest-black-rgb), .14);
        border-radius: 12px;
        overflow: hidden;
        background: #f8f4ec;
    }
    .checkout-map-toolbar {
        display: flex;
        gap: .75rem;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        margin-bottom: .75rem;
    }
    .delivery-alert {
        color: #b91c1c;
        font-weight: 800;
    }
    .onjecasa-checkout-page .line-list {
        gap: 0;
    }
    .onjecasa-checkout-page .line-item {
        grid-template-columns: 70px minmax(0, 1fr) !important;
        padding: 16px 0;
        border-color: rgba(var(--helpest-black-rgb), .10);
    }
    .onjecasa-checkout-page .line-thumb {
        width: 70px !important;
        height: 70px !important;
        border-radius: 10px;
        object-fit: cover;
        background: #fef3c7;
    }
    .onjecasa-checkout-page .summary-row {
        margin-bottom: 14px;
        padding-bottom: 14px;
        border-bottom: 1px solid rgba(var(--helpest-black-rgb), .10);
    }
    .onjecasa-checkout-page .summary-row:last-child {
        margin-bottom: 0;
        padding: 18px 20px;
        border: 0;
        border-radius: 12px;
        background: var(--helpest-black);
        color: #fff;
    }
    .onjecasa-checkout-page .summary-row:last-child .price-text {
        color: var(--helpest-base);
    }
    .onjecasa-empty-checkout i {
        display: inline-grid;
        place-items: center;
        width: 90px;
        height: 90px;
        margin-bottom: 20px;
        border-radius: 50%;
        background: var(--helpest-base);
        color: #fff;
        font-size: 36px;
    }
    .onjecasa-empty-checkout h3 {
        margin-bottom: 10px;
        font-size: 34px;
        font-weight: 900;
    }
    .onjecasa-empty-checkout p {
        margin-bottom: 22px;
    }
    .onjecasa-checkout-page .modal-content {
        border-radius: 14px !important;
    }
    @media (max-width: 575.98px) {
        .checkout-map { height: 260px; }
        .checkout-map-toolbar .shop-btn { width: 100%; }
    }
    @media (max-width: 991px) {
        .onjecasa-checkout-grid {
            grid-template-columns: 1fr;
        }
        .onjecasa-checkout-card--summary {
            position: relative;
            top: auto;
        }
    }
    @media (max-width: 767px) {
        .onjecasa-checkout-page .page-header {
            padding: 115px 0 110px;
        }
        .onjecasa-checkout {
            padding: 72px 0;
        }
        .onjecasa-checkout-card {
            padding: 22px;
        }
        .checkout-email-verify {
            grid-template-columns: 1fr;
        }
        .checkout-card-heading h3 {
            font-size: 27px;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const subtotal = {{ json_encode((float) $subtotal) }};
    const branches = @json($branchOptions);
    const branchSelect = document.getElementById('branchSelect');
    const branchHint = document.getElementById('branchHint');
    const mapSection = document.getElementById('deliveryMapSection');
    const mapStatus = document.getElementById('mapStatus');
    const nearestBranchHint = document.getElementById('nearestBranchHint');
    const distanceTotal = document.getElementById('distanceTotal');
    const deliveryLatitude = document.getElementById('deliveryLatitude');
    const deliveryLongitude = document.getElementById('deliveryLongitude');
    const useMyLocationBtn = document.getElementById('useMyLocationBtn');
    const checkoutForm = document.getElementById('checkoutForm');
    let map = null;
    let customerMarker = null;
    let branchMarkers = [];
    let selectedLocation = null;
    let currentDeliveryFee = 0;
    let currentDistance = null;

    function formatMoney(amount) {
        return 'GHC ' + Number(amount).toFixed(2);
    }

    function selectedBranch() {
        const id = Number(branchSelect?.value || 0);
        return branches.find(branch => Number(branch.id) === id) || null;
    }

    function distanceKm(fromLat, fromLng, toLat, toLng) {
        const earthRadius = 6371;
        const toRad = value => value * Math.PI / 180;
        const dLat = toRad(toLat - fromLat);
        const dLng = toRad(toLng - fromLng);
        const a = Math.sin(dLat / 2) ** 2 + Math.cos(toRad(fromLat)) * Math.cos(toRad(toLat)) * Math.sin(dLng / 2) ** 2;
        return earthRadius * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)));
    }

    function branchHasCoords(branch) {
        return branch && branch.latitude !== null && branch.longitude !== null;
    }

    function nearestBranch(location) {
        return branches
            .filter(branchHasCoords)
            .map(branch => ({branch, distance: distanceKm(branch.latitude, branch.longitude, location.lat, location.lng)}))
            .sort((a, b) => a.distance - b.distance)[0] || null;
    }

    function updateTotals() {
        const selected = document.querySelector('input[name="fulfillment_method"]:checked')?.value || 'delivery';
        const isPickup = selected === 'pickup';
        const branch = selectedBranch();
        const shippingTotal = document.getElementById('shippingTotal');
        const grandTotal = document.getElementById('grandTotal');

        currentDeliveryFee = 0;
        currentDistance = null;

        if (!isPickup && branchHasCoords(branch) && selectedLocation) {
            currentDistance = distanceKm(branch.latitude, branch.longitude, selectedLocation.lat, selectedLocation.lng);
            currentDeliveryFee = Number(branch.base_delivery_fee || 0) + (currentDistance * Number(branch.delivery_fee_per_km || 0));
            const maxDistance = branch.max_delivery_distance_km === null ? null : Number(branch.max_delivery_distance_km);
            if (maxDistance !== null && currentDistance > maxDistance) {
                currentDeliveryFee = 0;
                mapStatus.textContent = `This location is ${currentDistance.toFixed(2)} km away and outside ${branch.name}'s delivery range.`;
                mapStatus.classList.add('delivery-alert');
            } else {
                mapStatus.textContent = `${branch.name} is ${currentDistance.toFixed(2)} km away. Delivery fee calculated.`;
                mapStatus.classList.remove('delivery-alert');
            }
        }

        if (shippingTotal) shippingTotal.textContent = formatMoney(isPickup ? 0 : currentDeliveryFee);
        if (distanceTotal) distanceTotal.textContent = isPickup ? 'Pickup' : (currentDistance === null ? 'Select location' : `${currentDistance.toFixed(2)} km`);
        if (grandTotal) grandTotal.textContent = formatMoney(subtotal + (isPickup ? 0 : currentDeliveryFee));
    }

    function updateBranchHint() {
        const branch = selectedBranch();
        if (!branchHint) return;

        if (!branch) {
            branchHint.textContent = 'Your order will be sent to this store for pickup or fulfillment.';
            return;
        }

        const feeText = `Base ${formatMoney(branch.base_delivery_fee || 0)} + ${formatMoney(branch.delivery_fee_per_km || 0)} per km`;
        branchHint.textContent = branch.address ? `${branch.address}. ${feeText}.` : feeText;
    }

    function setCustomerLocation(lat, lng, shouldPan = true) {
        selectedLocation = {lat, lng};
        if (deliveryLatitude) deliveryLatitude.value = lat.toFixed(7);
        if (deliveryLongitude) deliveryLongitude.value = lng.toFixed(7);

        if (map) {
            if (!customerMarker) {
                customerMarker = L.marker([lat, lng], {draggable: true}).addTo(map).bindPopup('Delivery location');
                customerMarker.on('dragend', function () {
                    const point = customerMarker.getLatLng();
                    setCustomerLocation(point.lat, point.lng, false);
                });
            } else {
                customerMarker.setLatLng([lat, lng]);
            }
            if (shouldPan) map.setView([lat, lng], Math.max(map.getZoom(), 14));
        }

        const nearest = nearestBranch(selectedLocation);
        if (nearest && nearestBranchHint) {
            nearestBranchHint.textContent = `Nearest branch: ${nearest.branch.name} (${nearest.distance.toFixed(2)} km)`;
        }
        if (nearest && branchSelect && !branchSelect.value) {
            branchSelect.value = nearest.branch.id;
            updateBranchHint();
        }

        updateTotals();
    }

    function initMap() {
        if (!document.getElementById('checkoutMap') || !window.L) return;
        const firstBranch = branches.find(branchHasCoords);
        const start = firstBranch ? [firstBranch.latitude, firstBranch.longitude] : [5.6037, -0.1870];
        map = L.map('checkoutMap', {scrollWheelZoom: false}).setView(start, firstBranch ? 12 : 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        branchMarkers = branches.filter(branchHasCoords).map(function (branch) {
            return L.marker([branch.latitude, branch.longitude]).addTo(map).bindPopup(`<strong>${branch.name}</strong><br>${branch.address || 'Branch location'}`);
        });

        map.on('click', function (event) {
            setCustomerLocation(event.latlng.lat, event.latlng.lng);
        });

        const oldLat = Number(deliveryLatitude?.value || 0);
        const oldLng = Number(deliveryLongitude?.value || 0);
        if (oldLat && oldLng) setCustomerLocation(oldLat, oldLng);
    }

    function updateFulfillmentState() {
        const selected = document.querySelector('input[name="fulfillment_method"]:checked')?.value || 'delivery';
        const isPickup = selected === 'pickup';
        const address = document.getElementById('checkoutAddress');
        const addressLabel = document.getElementById('addressLabel');
        const deliveryHint = document.getElementById('deliveryHint');
        const pickupHint = document.getElementById('pickupHint');

        if (address) {
            address.required = !isPickup;
            address.disabled = isPickup;
            address.placeholder = isPickup ? 'Pickup at store' : 'House number, street, area, city, and nearest landmark';
        }
        if (addressLabel) addressLabel.textContent = isPickup ? 'Pickup' : 'Delivery Address';
        if (deliveryHint) deliveryHint.classList.toggle('d-none', isPickup);
        if (pickupHint) pickupHint.classList.toggle('d-none', !isPickup);
        if (mapSection) mapSection.classList.toggle('d-none', isPickup);
        updateTotals();
        setTimeout(function () {
            if (map && !isPickup) map.invalidateSize();
        }, 50);
    }

    document.querySelectorAll('input[name="fulfillment_method"]').forEach(function(input) {
        input.addEventListener('change', updateFulfillmentState);
    });
    branchSelect?.addEventListener('change', function () {
        updateBranchHint();
        updateTotals();
    });

    useMyLocationBtn?.addEventListener('click', function () {
        if (!navigator.geolocation) {
            mapStatus.textContent = 'Your browser does not support location detection. Tap the map instead.';
            mapStatus.classList.add('delivery-alert');
            return;
        }

        useMyLocationBtn.disabled = true;
        useMyLocationBtn.textContent = 'Finding...';
        navigator.geolocation.getCurrentPosition(function (position) {
            setCustomerLocation(position.coords.latitude, position.coords.longitude);
            useMyLocationBtn.disabled = false;
            useMyLocationBtn.textContent = 'Use My Location';
        }, function () {
            mapStatus.textContent = 'Could not get your location. Tap the map to choose your delivery point.';
            mapStatus.classList.add('delivery-alert');
            useMyLocationBtn.disabled = false;
            useMyLocationBtn.textContent = 'Use My Location';
        }, {enableHighAccuracy: true, timeout: 12000});
    });

    checkoutForm?.addEventListener('submit', function (event) {
        const selected = document.querySelector('input[name="fulfillment_method"]:checked')?.value || 'delivery';
        const branch = selectedBranch();
        if (selected === 'delivery') {
            if (!branchHasCoords(branch)) {
                event.preventDefault();
                mapStatus.textContent = 'Select a branch with map coordinates before delivery checkout.';
                mapStatus.classList.add('delivery-alert');
                return;
            }
            if (!selectedLocation) {
                event.preventDefault();
                mapStatus.textContent = 'Select your delivery location on the map before checkout.';
                mapStatus.classList.add('delivery-alert');
                return;
            }
            const maxDistance = branch.max_delivery_distance_km === null ? null : Number(branch.max_delivery_distance_km);
            if (maxDistance !== null && currentDistance !== null && currentDistance > maxDistance) {
                event.preventDefault();
                mapStatus.textContent = 'This delivery location is outside the selected branch delivery range.';
                mapStatus.classList.add('delivery-alert');
            }
        }
    });

    initMap();
    updateBranchHint();
    updateFulfillmentState();

});
</script>
@endpush


