@extends('Frontend.layout')

@section('title', 'Cart - ONJECASA')

@push('styles')
    <link href="{{ asset('themes/zabaga/assets/css/module-css/page-header.css') }}" rel="stylesheet">
    <style>
        .onjecasa-template-main { padding: 0; background: #fff; }
        .onjecasa-cart-page {
            position: relative;
            width: 100%;
            max-width: 100%;
            overflow: hidden;
        }
        .onjecasa-cart-page::before {
            content: "";
            position: absolute;
            top: 0;
            left: 50%;
            width: 100vw;
            height: 430px;
            transform: translateX(-50%);
            background:
                linear-gradient(to right,
                    var(--helpest-black) 0 96px,
                    transparent 96px calc(100% - 96px),
                    var(--helpest-black) calc(100% - 96px) 100%);
            pointer-events: none;
            z-index: 0;
        }
        .onjecasa-cart-page .page-header {
            left: 50%;
            transform: translateX(-50%);
            width: 100vw;
            max-width: 100vw;
            margin-left: 0;
            margin-right: 0;
            padding: 150px 0 138px;
            -webkit-mask-size: 100% 100%;
            mask-size: 100% 100%;
            z-index: 1;
        }
        .onjecasa-cart-page .cart-page {
            position: relative;
            z-index: 2;
            background: #fff;
        }
        .onjecasa-cart-page .cart-page { padding: 105px 0 105px; }
        .onjecasa-cart-page .cart-table {
            min-width: 860px;
        }
        .onjecasa-cart-page .cart-table .product-box .img-box {
            width: 118px;
            height: 118px;
            background: #fef3c7;
        }
        .onjecasa-cart-page .cart-table .product-box .img-box img,
        .onjecasa-cart-page .cart-table .product-box .img-box video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            border: 1px solid rgba(var(--helpest-black-rgb), .10);
            border-radius: 10px;
        }
        .onjecasa-cart-page .cart-item-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }
        .onjecasa-cart-page .cart-item-meta span {
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            padding: 5px 10px;
            border-radius: 999px;
            background: var(--helpest-extra);
            color: var(--helpest-gray);
            font-size: 12px;
            line-height: 1.2;
            font-weight: 800;
        }
        .onjecasa-cart-page .cart-update-form {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin: 0;
        }
        .onjecasa-cart-page .cart-update-form .quantity-box input {
            padding-left: 28px;
        }
        .onjecasa-cart-page .cart-update-btn,
        .onjecasa-cart-page .cart-remove-btn {
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 50%;
            background: var(--helpest-extra);
            color: var(--helpest-black);
            transition: all 200ms ease;
        }
        .onjecasa-cart-page .cart-update-btn:hover {
            background: var(--helpest-base);
            color: var(--helpest-white);
        }
        .onjecasa-cart-page .cart-remove-form {
            display: inline-block;
            margin: 0;
        }
        .onjecasa-cart-page .cart-remove-btn:hover {
            background: #ec4899;
            color: #fff;
        }
        .onjecasa-cart-page .cart-page__sidebar {
            position: sticky;
            top: 130px;
        }
        .onjecasa-cart-page .cart-total li {
            justify-content: space-between;
        }
        .onjecasa-cart-page .cart-total li span:first-child {
            width: auto;
            margin-right: 24px;
            text-align: left;
        }
        .onjecasa-cart-page .cart-page__buttons {
            display: grid;
            gap: 12px;
            justify-content: stretch;
        }
        .onjecasa-cart-page .cart-page__buttons .thm-btn {
            justify-content: center;
            width: 100%;
        }
        .onjecasa-cart-page .onjecasa-empty-cart {
            padding: 70px 35px;
            border: 1px solid rgba(var(--helpest-black-rgb), .10);
            border-radius: 15px;
            background: var(--helpest-extra);
            text-align: center;
        }
        .onjecasa-cart-page .onjecasa-empty-cart i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 92px;
            height: 92px;
            margin-bottom: 22px;
            border-radius: 50%;
            background: var(--helpest-base);
            color: var(--helpest-white);
            font-size: 36px;
        }
        .onjecasa-cart-page .onjecasa-empty-cart h3 {
            margin-bottom: 12px;
            color: var(--helpest-black);
            font-size: 34px;
            line-height: 42px;
            font-weight: 900;
        }
        @media (max-width: 991px) {
            .onjecasa-cart-page .cart-page__right {
                margin-left: 0;
                margin-top: 40px;
            }
            .onjecasa-cart-page .cart-page__sidebar {
                position: relative;
                top: auto;
            }
        }
        @media (max-width: 767px) {
            .onjecasa-cart-page::before {
                height: 335px;
            }
            .onjecasa-cart-page .page-header {
                padding: 115px 0 110px;
            }
            .onjecasa-cart-page .cart-page {
                padding: 72px 0 78px;
            }
        }
    </style>
@endpush

@section('full_width_content')
@php
    $subtotal = $cartItems->sum(fn ($item) => \App\Support\MealExtras::lineTotal($item));
    $shipping = $cartItems->count() ? 15 : 0;
    $grandTotal = $subtotal + $shipping;
@endphp

<div class="onjecasa-cart-page">
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
                <h2>Cart</h2>
                <div class="thm-breadcrumb__box">
                    <ul class="thm-breadcrumb list-unstyled">
                        <li><a href="{{ route('home_page') }}">Home</a></li>
                        <li><span class="icon-right-arrow"></span></li>
                        <li>Cart</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="cart-page">
        <div class="container">
            @if($cartItems->isEmpty())
                <div class="onjecasa-empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Your cart is empty.</h3>
                    <p>Add groceries, produce, beverages, household goods, or other products from the store.</p>
                    <a href="{{ route('product_page') }}" class="thm-btn">
                        <span class="thm-btn-text">Shop Products</span>
                        <span class="thm-btn-icon-box"><i class="fas fa-arrow-right"></i></span>
                    </a>
                </div>
            @else
                <div class="row">
                    <div class="col-xl-8 col-lg-12">
                        <div class="table-responsive">
                            <table class="cart-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Subtotal</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cartItems as $item)
                                        @php
                                            $product = $item->product;
                                            $mediaUrl = $product?->thumbnail_media_url ?: $product?->picture_url ?: asset('images/onjecasa-products.svg');
                                            $isVideo = (bool) ($product?->thumbnail_is_video);
                                            $extrasLabel = \App\Support\MealExtras::label($item->color);
                                            $unitPrice = \App\Support\MealExtras::unitPrice($item);
                                            $lineTotal = \App\Support\MealExtras::lineTotal($item);
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="product-box">
                                                    <div class="img-box">
                                                        @if($isVideo)
                                                            <video src="{{ $mediaUrl }}" autoplay muted loop playsinline preload="metadata"></video>
                                                        @else
                                                            <img src="{{ $mediaUrl }}" alt="{{ $product?->name ?: 'ONJECASA product' }}" onerror="this.onerror=null;this.src='{{ asset('images/onjecasa-products.svg') }}';">
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <h3>
                                                            @if($product)
                                                                <a href="{{ route('product_details', $product->id) }}">{{ $product->name }}</a>
                                                            @else
                                                                Removed product
                                                            @endif
                                                        </h3>
                                                        <div class="cart-item-meta">
                                                            @if($item->size)
                                                                <span>{{ $item->size }}</span>
                                                            @endif
                                                            @if($extrasLabel)
                                                                <span>{{ $extrasLabel }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>GHC {{ number_format($unitPrice, 2) }}</td>
                                            <td>
                                                <form action="{{ route('update_cart', $item->id) }}" method="POST" class="cart-update-form">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="quantity-box">
                                                        <button type="button" class="sub"><i class="fa fa-minus"></i></button>
                                                        <input type="number" min="1" name="quantity" value="{{ $item->quantity }}">
                                                        <button type="button" class="add"><i class="fa fa-plus"></i></button>
                                                    </div>
                                                    <button class="cart-update-btn" type="submit" aria-label="Update quantity">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            </td>
                                            <td>GHC {{ number_format($lineTotal, 2) }}</td>
                                            <td>
                                                <form action="{{ route('remove_cart', $item->id) }}" method="POST" class="cart-remove-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="cart-remove-btn" type="submit" aria-label="Remove {{ $product?->name ?: 'product' }}">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-12">
                        <div class="cart-page__right">
                            <div class="cart-page__sidebar">
                                <div class="cart-page__shipping">
                                    <h3 class="cart-page__shipping-title">Order Summary</h3>
                                    <p>Review your product options and extras before checkout. Delivery is estimated and confirmed at checkout.</p>
                                </div>
                                <div class="cart-page__coupon-code">
                                    <h3 class="cart-page__coupon-code-title">Need more food?</h3>
                                    <p class="cart-page__coupon-code-text">Add bags, drinks, household items, produce, or another product before checking out.</p>
                                    <a href="{{ route('product_page') }}" class="thm-btn">
                                        <span class="thm-btn-text">Continue Shopping</span>
                                        <span class="thm-btn-icon-box"><i class="fas fa-arrow-right"></i></span>
                                    </a>
                                </div>
                                <ul class="cart-total list-unstyled">
                                    <li>
                                        <span>Subtotal</span>
                                        <span>GHC {{ number_format($subtotal, 2) }}</span>
                                    </li>
                                    <li>
                                        <span>Estimated Delivery</span>
                                        <span>GHC {{ number_format($shipping, 2) }}</span>
                                    </li>
                                    <li>
                                        <span>Total</span>
                                        <span class="cart-total-amount">GHC {{ number_format($grandTotal, 2) }}</span>
                                    </li>
                                </ul>
                                <div class="cart-page__buttons">
                                    <a href="{{ route('checkout') }}" class="thm-btn">
                                        <span class="thm-btn-text">Proceed to Checkout</span>
                                        <span class="thm-btn-icon-box"><i class="fas fa-arrow-right"></i></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.onjecasa-cart-page video').forEach(function(video) {
        video.muted = true;
        video.defaultMuted = true;
        video.loop = true;
        video.autoplay = true;
        video.playsInline = true;
        video.removeAttribute('controls');
        video.play?.().catch(function() {});
    });
</script>
@endpush



