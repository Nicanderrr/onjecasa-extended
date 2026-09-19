@extends('Frontend.layout')

@section('title', $product->name . ' - ONJECASA')
@section('meta_description', \Illuminate\Support\Str::limit($product->contents ?: $product->name, 150))

@push('styles')
    <link href="{{ asset('themes/zabaga/assets/css/swiper.min.css') }}" rel="stylesheet">
    <link href="{{ asset('themes/zabaga/assets/css/module-css/page-header.css') }}" rel="stylesheet">
    <style>
        .onjecasa-template-main { padding: 0; background: #fff; }
        .onjecasa-product-details {
            position: relative;
            width: 100%;
            max-width: 100%;
            overflow: hidden;
        }
        .onjecasa-product-details::before {
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
        .onjecasa-product-details .page-header {
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
        .onjecasa-product-details .product-details {
            position: relative;
            z-index: 2;
            background: #fff;
            padding: 105px 0 70px;
        }
        .onjecasa-product-details .product-details__left {
            margin-right: 40px;
        }
        .onjecasa-product-details .product-details__img,
        .onjecasa-product-details .product-details__thumb-img {
            background: #fef3c7;
            overflow: hidden;
        }
        .onjecasa-product-details .product-details__img {
            aspect-ratio: 640 / 620;
            border-radius: 15px;
        }
        .onjecasa-product-details .product-details__img img,
        .onjecasa-product-details .product-details__img video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            border: 1px solid rgba(var(--helpest-black-rgb), .10);
            border-radius: 15px;
        }
        .onjecasa-product-details .product-details__thumb-img {
            height: 118px;
        }
        .onjecasa-product-details .product-details__thumb-img img,
        .onjecasa-product-details .product-details__thumb-img video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            border-radius: 10px;
        }
        .onjecasa-product-details .product-details__title {
            text-transform: none;
        }
        .onjecasa-product-details .product-details__title span {
            white-space: nowrap;
        }
        .onjecasa-product-details .onjecasa-stock-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--helpest-base);
            font-size: 15px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .onjecasa-product-details .product-details__select-size {
            align-items: flex-start;
            gap: 14px;
        }
        .onjecasa-product-details .product-details__select-size h3 {
            flex: 0 0 120px;
            margin: 2px 0 0;
        }
        .onjecasa-product-details .product-details__select-size ul li {
            width: auto;
            min-width: 76px;
            height: 42px;
        }
        .onjecasa-product-details .product-details__select-size ul li label,
        .onjecasa-product-details .product-details__select-size ul li input[type=radio]+label i {
            width: 100%;
            height: 42px;
            min-width: 76px;
        }
        .onjecasa-product-details .product-details__select-size ul li label {
            padding: 0 16px;
            font-size: 13px;
            font-weight: 800;
            white-space: nowrap;
        }
        .onjecasa-product-details .onjecasa-extra-panel {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid rgba(var(--helpest-black-rgb), .10);
        }
        .onjecasa-product-details .onjecasa-extra-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 16px;
        }
        .onjecasa-product-details .onjecasa-extra-card {
            position: relative;
            display: grid;
            grid-template-columns: 52px minmax(0, 1fr) 54px;
            align-items: center;
            gap: 12px;
            min-height: 86px;
            padding: 12px;
            border: 1px solid rgba(var(--helpest-black-rgb), .10);
            border-radius: 10px;
            background: var(--helpest-extra);
            cursor: pointer;
            transition: border-color 200ms ease, background-color 200ms ease, transform 200ms ease;
        }
        .onjecasa-product-details .onjecasa-extra-card:hover,
        .onjecasa-product-details .onjecasa-extra-card.is-selected {
            border-color: var(--helpest-base);
            background: #fff;
            transform: translateY(-1px);
        }
        .onjecasa-product-details .onjecasa-extra-card > input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        .onjecasa-product-details .onjecasa-extra-icon {
            width: 52px;
            height: 52px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: var(--helpest-white);
            color: var(--helpest-base);
            font-size: 24px;
            overflow: hidden;
        }
        .onjecasa-product-details .onjecasa-extra-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .onjecasa-product-details .onjecasa-extra-body {
            min-width: 0;
        }
        .onjecasa-product-details .onjecasa-extra-body strong,
        .onjecasa-product-details .onjecasa-extra-body span {
            display: block;
        }
        .onjecasa-product-details .onjecasa-extra-body strong {
            color: var(--helpest-black);
            font-size: 15px;
            line-height: 19px;
            font-weight: 900;
        }
        .onjecasa-product-details .onjecasa-extra-body span {
            color: var(--helpest-base);
            font-size: 13px;
            line-height: 18px;
            font-weight: 800;
        }
        .onjecasa-product-details .onjecasa-extra-body small {
            display: block;
            color: var(--helpest-gray);
            font-size: 12px;
            line-height: 16px;
        }
        .onjecasa-product-details .onjecasa-extra-qty {
            width: 54px;
            height: 38px;
            border: 1px solid rgba(var(--helpest-black-rgb), .12);
            border-radius: 8px;
            background: #fff;
            color: var(--helpest-black);
            text-align: center;
            font-weight: 900;
        }
        .onjecasa-product-details .onjecasa-total-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-top: 20px;
            padding: 18px 20px;
            border-radius: 12px;
            background: var(--helpest-black);
            color: var(--helpest-white);
        }
        .onjecasa-product-details .onjecasa-total-row span {
            color: rgba(var(--helpest-white-rgb), .72);
            font-weight: 800;
        }
        .onjecasa-product-details .onjecasa-total-row strong {
            font-size: 28px;
            line-height: 1;
            color: var(--helpest-base);
        }
        .onjecasa-product-details .product-description {
            padding-bottom: 95px;
        }
        .onjecasa-product-details .single-product-style1__img {
            aspect-ratio: 300 / 310;
            background: #fef3c7;
        }
        .onjecasa-product-details .single-product-style1__img img,
        .onjecasa-product-details .single-product-style1__img video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        @media (max-width: 991px) {
            .onjecasa-product-details .product-details__left {
                margin-right: 0;
                margin-bottom: 45px;
            }
            .onjecasa-product-details .onjecasa-extra-grid {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 767px) {
            .onjecasa-product-details::before {
                height: 335px;
            }
            .onjecasa-product-details .page-header {
                padding: 115px 0 110px;
            }
            .onjecasa-product-details .product-details {
                padding: 72px 0 50px;
            }
            .onjecasa-product-details .product-details__select-size {
                display: block;
            }
            .onjecasa-product-details .product-details__select-size h3 {
                margin-bottom: 12px;
            }
            .onjecasa-product-details .product-details__buttons-boxes {
                align-items: stretch;
                flex-direction: column;
            }
        }
    </style>
@endpush

@section('full_width_content')
@php
    $features = is_string($product->features) ? json_decode($product->features, true) : $product->features;
    $units = \App\Support\ProductOptions::options($product);
    $initialUnitPrice = (float) ($units[0]['price'] ?? $product->price);

    $selectedExtraNames = is_string($product->colors) ? json_decode($product->colors, true) : $product->colors;
    $mealExtras = collect(\App\Support\MealExtras::options());
    if (is_array($selectedExtraNames) && count($selectedExtraNames)) {
        $mealExtras = $mealExtras->whereIn('name', $selectedExtraNames)->values();
    }
    $mealExtras = $mealExtras->all();
    $features = is_array($features) ? array_values(array_filter($features)) : [];
    $galleryMedia = $product->images()->get();
    $recommendations = \App\Models\ProductPage::where('id', '!=', $product->id)->latest()->take(4)->get();
    $initialMediaUrl = $product->thumbnail_media_url ?: asset('images/onjecasa-products.svg');
    $mediaItems = collect([[
        'type' => $product->thumbnail_is_video ? 'video' : 'image',
        'url' => $initialMediaUrl,
        'label' => $product->name,
    ]]);
    foreach ($galleryMedia as $media) {
        $mediaItems->push([
            'type' => $media->is_video ? 'video' : 'image',
            'url' => $media->url,
            'label' => $product->name,
        ]);
    }
    $mediaItems = $mediaItems->filter(fn ($media) => !empty($media['url']))->unique('url')->values();
    if ($mediaItems->isEmpty()) {
        $mediaItems = collect([['type' => 'image', 'url' => asset('images/onjecasa-products.svg'), 'label' => $product->name]]);
    }
@endphp

<div class="onjecasa-product-details">
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
                <h2>Product Details</h2>
                <div class="thm-breadcrumb__box">
                    <ul class="thm-breadcrumb list-unstyled">
                        <li><a href="{{ route('home_page') }}">Home</a></li>
                        <li><span class="icon-right-arrow"></span></li>
                                <li><a href="{{ route('product_page') }}">Products</a></li>
                        <li><span class="icon-right-arrow"></span></li>
                        <li>{{ \Illuminate\Support\Str::limit($product->name, 28) }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="product-details">
        <div class="container">
            <form action="{{ route('addcart', $product->id) }}" method="POST" id="onjecasaProductForm">
                @csrf
                <div class="row related-products-grid">
                    <div class="col-xl-6 col-lg-6">
                        <div class="product-details__left">
                            <div class="product-details__left-inner">
                                <div class="product-details__content-box">
                                    <div class="swiper-container" id="shop-details-one__carousel">
                                        <div class="swiper-wrapper">
                                            @foreach($mediaItems as $media)
                                                <div class="swiper-slide">
                                                    <div class="product-details__img">
                                                        @if($media['type'] === 'video')
                                                            <video src="{{ $media['url'] }}" autoplay muted loop playsinline preload="metadata"></video>
                                                        @else
                                                            <img src="{{ $media['url'] }}" alt="{{ $media['label'] }}" onerror="this.onerror=null;this.src='{{ asset('images/onjecasa-products.svg') }}';">
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="product-details__nav">
                                            <div class="swiper-button-prev" id="product-details__swiper-button-prev">
                                                <i class="icon-left-arrow"></i>
                                            </div>
                                            <div class="swiper-button-next" id="product-details__swiper-button-next">
                                                <i class="icon-right-arrow"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if($mediaItems->count() > 1)
                                    <div class="product-details__thumb-box">
                                        <div class="swiper-container" id="shop-details-one__thumb">
                                            <div class="swiper-wrapper">
                                                @foreach($mediaItems as $media)
                                                    <div class="swiper-slide">
                                                        <div class="product-details__thumb-img">
                                                            @if($media['type'] === 'video')
                                                                <video src="{{ $media['url'] }}" autoplay muted loop playsinline preload="metadata"></video>
                                                            @else
                                                                <img src="{{ $media['url'] }}" alt="{{ $media['label'] }}" onerror="this.onerror=null;this.src='{{ asset('images/onjecasa-products.svg') }}';">
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 col-lg-6">
                        <div class="product-details__right">
                            <div class="product-details__top">
                                <div class="onjecasa-stock-badge">
                                    <span class="icon-check"></span>
                                    Available for order
                                </div>
                                <h3 class="product-details__title">
                                    {{ $product->name }}
                                    <span>From GHC {{ number_format($initialUnitPrice, 2) }}</span>
                                </h3>
                            </div>
                            <div class="product-details__reveiw">
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <span>{{ $product->description ?: 'ONJECASA supermarket product' }}</span>
                            </div>
                            <div class="product-details__content">
                                <p class="product-details__content-text1">{{ $product->contents ?: 'Available for pickup or delivery.' }}</p>
                                <p class="product-details__content-text2">Choose your option, add add-ons, and the total updates before you add it to cart.</p>
                            </div>

                            <div class="product-details__select">
                                <div class="product-details__select-size">
                                    <h3>Unit</h3>
                                    <ul class="list-unstyled">
                                        @foreach($units as $unit)
                                            @php($optionId = 'option-' . $loop->index)
                                            <li>
                                                <input type="radio" id="{{ $optionId }}" name="size" value="{{ $unit['name'] }}" data-option-price="{{ $unit['price'] }}" @checked($loop->first)>
                                                <label for="{{ $optionId }}">{{ $unit['name'] }} <span>GHC {{ number_format((float) $unit['price'], 2) }}</span><i></i></label>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>

                            <div class="product-details__inner">
                                <div class="product-details__quantity">
                                    <h3 class="product-details__quantity-title">Quantity</h3>
                                    <div class="quantity-box">
                                        <button type="button" class="sub"><i class="fa fa-minus"></i></button>
                                        <input type="number" id="productQuantity" name="quantity" value="1" min="1">
                                        <button type="button" class="add"><i class="fa fa-plus"></i></button>
                                    </div>
                                </div>
                            </div>

                            @if(count($mealExtras))
                                <div class="onjecasa-extra-panel">
                                    <h3 class="product-details__quantity-title">Add-ons & Basket Items</h3>
                                    <div class="onjecasa-extra-grid">
                                        @foreach($mealExtras as $extra)
                                            <label class="onjecasa-extra-card">
                                                <input type="checkbox" name="extras[]" value="{{ $extra['name'] }}" data-extra-price="{{ $extra['price'] }}">
                                                <span class="onjecasa-extra-icon">
                                                    @if(!empty($extra['image_url']))
                                                        <img src="{{ $extra['image_url'] }}" alt="{{ $extra['name'] }}">
                                                    @else
                                                        <i class="bi {{ $extra['icon'] }}"></i>
                                                    @endif
                                                </span>
                                                <span class="onjecasa-extra-body">
                                                    <strong>{{ $extra['name'] }}</strong>
                                                    <span>+ GHC {{ number_format((float) $extra['price'], 2) }}</span>
                                                    @if(!empty($extra['description']))
                                                        <small>{{ \Illuminate\Support\Str::limit($extra['description'], 46) }}</small>
                                                    @endif
                                                </span>
                                                <input type="number" name="extra_quantities[{{ $extra['name'] }}]" value="1" min="1" class="onjecasa-extra-qty" aria-label="{{ $extra['name'] }} quantity">
                                            </label>
                                        @endforeach
                                    </div>
                                    <div class="onjecasa-total-row">
                                        <span>Extras total</span>
                                        <strong>GHC <span id="extrasTotal">0.00</span></strong>
                                    </div>
                                </div>
                            @endif

                            <div class="onjecasa-total-row">
                                <span>Product total</span>
                                <strong>GHC <span id="productTotal">{{ number_format((float) $product->price, 2) }}</span></strong>
                            </div>

                            <div class="product-details__buttons-boxes">
                                <div class="product-details__buttons-1">
                                    <button type="submit" class="thm-btn">
                                        <span class="thm-btn-text">Add to Cart</span>
                                        <span class="thm-btn-icon-box"><i class="fas fa-shopping-cart"></i></span>
                                    </button>
                                </div>
                                <div class="product-details__buttons-2">
                                    <a href="{{ route('product_page') }}" class="thm-btn">
                                        <span class="thm-btn-text">Back to Products</span>
                                        <span class="thm-btn-icon-box"><i class="fas fa-arrow-right"></i></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="product-description">
        <div class="container">
            <div class="product-details__description">
                <div class="product-details__main-tab-box tabs-box">
                    <ul class="tab-buttons clearfix list-unstyled">
                        <li data-tab="#description" class="tab-btn active-btn"><span>Description</span></li>
                        <li data-tab="#information" class="tab-btn"><span>Product Info</span></li>
                    </ul>
                    <div class="tabs-content">
                        <div class="tab active-tab" id="description">
                            <div class="product-details__tab-content-inner">
                                <div class="product-details__description-content">
                                    <p class="product-details__description-text-1">{{ $product->contents ?: 'A freshly prepared ONJECASA product built for pickup, delivery, and bulk orders.' }}</p>
                                    @if(count($features))
                                        <div class="product-description__list">
                                            <ul class="list-unstyled">
                                                @foreach($features as $feature)
                                                    <li><p><span class="icon-check"></span>{{ $feature }}</p></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="tab" id="information">
                            <div class="product-details__tab-content-inner">
                                <div class="product-details__additional-information-content">
                                    <p class="product-details__additional-information-text-1">Category: {{ $product->description ?: 'Product' }}</p>
                                <p class="product-details__additional-information-text-2">Selected unit, extras, and quantities are calculated before checkout.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if($recommendations->isNotEmpty())
        <section class="product pb-5">
            <div class="container">
                <div class="section-title text-center">
                    <div class="section-title__tagline-box">
                        <span class="section-title__tagline">More Plates</span>
                    </div>
                    <h2 class="section-title__title">Customers Also <span>Order.</span></h2>
                </div>
                <div class="row">
                    @foreach($recommendations as $recommended)
                        <div class="col-xl-3 col-lg-6 col-md-6">
                            <div class="single-product-style1">
                                <div class="single-product-style1__img">
                                    @if($recommended->thumbnail_is_video && $recommended->thumbnail_media_url)
                                        <video src="{{ $recommended->thumbnail_media_url }}" autoplay muted loop playsinline preload="metadata"></video>
                                        <video src="{{ $recommended->thumbnail_media_url }}" autoplay muted loop playsinline preload="metadata"></video>
                                    @else
                                        <img src="{{ $recommended->thumbnail_media_url ?: asset('images/onjecasa-products.svg') }}" alt="{{ $recommended->name }}" onerror="this.onerror=null;this.src='{{ asset('images/onjecasa-products.svg') }}';">
                                        <img src="{{ $recommended->thumbnail_media_url ?: asset('images/onjecasa-products.svg') }}" alt="{{ $recommended->name }}">
                                    @endif
                                    <ul class="single-product-style1__info">
                                        <li><a href="{{ route('product_details', $recommended->id) }}" title="View Details"><i class="fa fa-regular fa-eye"></i></a></li>
                                        <li><a href="{{ route('product_details', $recommended->id) }}" title="Open Product"><i class="fa fa-link"></i></a></li>
                                    </ul>
                                </div>
                                <div class="single-product-style1__content">
                                    <div class="single-product-style1__content-left">
                                        <h4><a href="{{ route('product_details', $recommended->id) }}">{{ $recommended->name }}</a></h4>
                                        <p>GHC {{ number_format((float) $recommended->price, 2) }}</p>
                                    </div>
                                    <div class="single-product-style1__content-right">
                                        <div class="single-product-style1__review">
                                            <i class="fa fa-star"></i>
                                            <p>4.8</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
@endsection

@push('scripts')
<script>
    const baseProductPrice = {{ json_encode($initialUnitPrice) }};
    const productTotal = document.getElementById('productTotal');
    const extrasTotal = document.getElementById('extrasTotal');
    const productQuantity = document.getElementById('productQuantity');

    function updateProductTotal() {
        const quantity = Math.max(1, Number(productQuantity?.value || 1));
        let extras = 0;

        document.querySelectorAll('.onjecasa-extra-card').forEach(function(card) {
            const checkbox = card.querySelector('input[type="checkbox"]');
            const qtyInput = card.querySelector('.onjecasa-extra-qty');
            const qty = Math.max(1, Number(qtyInput?.value || 1));

            if (checkbox?.checked) {
                extras += Number(checkbox.dataset.extraPrice || 0) * qty;
            }

            if (qtyInput) {
                qtyInput.disabled = !checkbox?.checked;
            }
            card.classList.toggle('is-selected', Boolean(checkbox?.checked));
        });

        const selectedUnit = document.querySelector('input[name="size"]:checked');
        const unitPrice = Number(selectedUnit?.dataset.optionPrice || baseProductPrice);

        if (extrasTotal) extrasTotal.textContent = extras.toFixed(2);
        if (productTotal) productTotal.textContent = ((unitPrice + extras) * quantity).toFixed(2);
    }

    document.querySelectorAll('input[name="size"]').forEach(function(input) {
        input.addEventListener('change', updateProductTotal);
    });
    document.querySelectorAll('.onjecasa-extra-card input').forEach(function(input) {
        input.addEventListener('change', updateProductTotal);
        input.addEventListener('input', updateProductTotal);
    });
    productQuantity?.addEventListener('input', updateProductTotal);
    document.addEventListener('DOMContentLoaded', updateProductTotal);

    document.querySelectorAll('.quantity-box .add, .quantity-box .sub').forEach(function(button) {
        button.addEventListener('click', function() {
            window.setTimeout(updateProductTotal, 0);
        });
    });

    function prepareAutoplayVideo(video) {
        video.removeAttribute('controls');
        video.muted = true;
        video.defaultMuted = true;
        video.loop = true;
        video.autoplay = true;
        video.playsInline = true;
        video.setAttribute('muted', '');
        video.setAttribute('autoplay', '');
        video.setAttribute('loop', '');
        video.setAttribute('playsinline', '');
    }

    function startProductVideos() {
        document.querySelectorAll('.onjecasa-product-details video').forEach(function(video) {
            prepareAutoplayVideo(video);
            const playPromise = video.play?.();
            if (playPromise && typeof playPromise.catch === 'function') {
                playPromise.catch(function() {});
            }
        });
    }

    document.addEventListener('DOMContentLoaded', startProductVideos);
    window.addEventListener('load', startProductVideos);
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) startProductVideos();
    });
</script>
@endpush


