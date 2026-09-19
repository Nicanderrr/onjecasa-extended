@extends('Frontend.layout')

@section('title', 'Products - ONJECASA')
@section('meta_description', 'Browse groceries, beverages, household goods, personal care, fresh produce, and delivery options.')

@push('styles')
    <link href="{{ asset('themes/zabaga/assets/css/jquery-ui.css') }}" rel="stylesheet">
    <link href="{{ asset('themes/zabaga/assets/css/module-css/page-header.css') }}" rel="stylesheet">
    <style>
        .onjecasa-template-main { padding: 0; background: #fff; }
        .onjecasa-products-page .page-header {
            padding: 166px 0 160px;
            -webkit-mask-size: 100% 100%;
            mask-size: 100% 100%;
        }
        .onjecasa-products-page .page-header__shape-1 img,
        .onjecasa-products-page .page-header__shape-2 img {
            transform: scale(.58);
        }
        .onjecasa-products-page .page-header__shape-1 img {
            transform-origin: right bottom;
        }
        .onjecasa-products-page .page-header__shape-2 img {
            transform-origin: left bottom;
        }
        .onjecasa-products-page .single-product-style1__img,
        .onjecasa-products-page .single-product-style2__img {
            aspect-ratio: 300 / 310;
            background: #fef3c7;
        }
        .onjecasa-products-page .single-product-style1__img img,
        .onjecasa-products-page .single-product-style1__img video,
        .onjecasa-products-page .single-product-style2__img img,
        .onjecasa-products-page .single-product-style2__img video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }
        .onjecasa-products-page .single-product-style1__img video:first-child,
        .onjecasa-products-page .single-product-style2__img video:first-child {
            position: absolute;
            inset: 0;
            z-index: 1;
        }
        .onjecasa-products-page .shop-product-recent-products ul li .img {
            width: 70px;
            height: 70px;
            flex: 0 0 70px;
        }
        .onjecasa-products-page .shop-product-recent-products ul li .img img,
        .onjecasa-products-page .shop-product-recent-products ul li .img video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .onjecasa-products-page .product-filter-inline {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .onjecasa-products-page .price-ranger__values {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-height: 34px;
            color: var(--helpest-gray);
            font-size: 14px;
            font-weight: 700;
        }
        .onjecasa-products-page .single-product-style1__info form,
        .onjecasa-products-page .single-product-style2__info form {
            margin: 0;
        }
        .onjecasa-products-page .single-product-style1__info button {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            border: 0;
            border-radius: 50%;
            background-color: var(--helpest-white);
            box-shadow: 0px 0px 35px 0px rgba(0, 0, 0, .2);
            color: var(--helpest-base);
            font-size: 15px;
            line-height: 0;
            transition: all 200ms linear;
        }
        .onjecasa-products-page .single-product-style1__info button:hover {
            color: var(--helpest-white);
            background-color: var(--helpest-base);
        }
        .onjecasa-products-page .single-product-style2__info button {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            border: 0;
            border-radius: 50%;
            background-color: var(--helpest-white);
            box-shadow: 0px 0px 35px 0px rgba(0, 0, 0, .2);
            color: var(--helpest-base);
            font-size: 15px;
            line-height: 0;
            transition: all 200ms linear;
        }
        .onjecasa-products-page .single-product-style2__info button:hover {
            color: var(--helpest-white);
            background-color: var(--helpest-base);
        }
        .onjecasa-products-page .onjecasa-clickable-product {
            cursor: pointer;
        }
        @media (max-width: 767px) {
            .onjecasa-products-page .page-header {
                padding: 120px 0 120px;
            }
        }
    </style>
@endpush

@section('full_width_content')
@php
    $query = trim((string) request('search'));
    $categoryFilter = trim((string) request('category'));
    $minPrice = request('min_price');
    $maxPrice = request('max_price');
    $priceFloor = (float) \App\Models\ProductPage::query()->min('price');
    $priceCeiling = (float) \App\Models\ProductPage::query()->max('price');
    $priceFloor = (int) floor(max(0, $priceFloor));
    $priceCeiling = (int) ceil(max($priceFloor + 1, $priceCeiling));
    $selectedMinPrice = is_numeric($minPrice) ? max($priceFloor, (int) floor((float) $minPrice)) : $priceFloor;
    $selectedMaxPrice = is_numeric($maxPrice) ? min($priceCeiling, (int) ceil((float) $maxPrice)) : $priceCeiling;
    if ($selectedMinPrice > $selectedMaxPrice) {
        [$selectedMinPrice, $selectedMaxPrice] = [$selectedMaxPrice, $selectedMinPrice];
    }

    $productQuery = \App\Models\ProductPage::query()
        ->when($query !== '', function ($builder) use ($query) {
            $builder->where(function ($searchQuery) use ($query) {
                $searchQuery->where('name', 'like', '%' . $query . '%')
                    ->orWhere('contents', 'like', '%' . $query . '%')
                    ->orWhere('description', 'like', '%' . $query . '%');
            });
        })
        ->when($categoryFilter !== '', fn ($builder) => $builder->where('description', $categoryFilter))
        ->when(is_numeric($minPrice), fn ($builder) => $builder->where('price', '>=', (float) $minPrice))
        ->when(is_numeric($maxPrice), fn ($builder) => $builder->where('price', '<=', (float) $maxPrice));

    $productQuery->latest();

    $products = $productQuery->get();
    $categories = \App\Models\ProductPage::query()->pluck('description')->filter()->unique()->values();
    $recentProducts = \App\Models\ProductPage::latest()->take(4)->get();
    $tags = $categories->take(6)->whenEmpty(fn ($collection) => $collection->push('Groceries', 'Beverages', 'Produce', 'Household'));
    $totalProducts = \App\Models\ProductPage::count();
    $shownCount = $products->count();

    $renderProductMedia = function ($product) {
        if ($product?->thumbnail_is_video && $product?->thumbnail_media_url) {
            return '<video src="' . e($product->thumbnail_media_url) . '" autoplay muted loop playsinline preload="metadata"></video><video src="' . e($product->thumbnail_media_url) . '" autoplay muted loop playsinline preload="metadata"></video>';
        }

        $url = $product?->thumbnail_media_url ?: asset('images/onjecasa-products.svg');
        $alt = e($product?->name ?: 'ONJECASA product');
        return '<img src="' . e($url) . '" alt="' . $alt . '" onerror="this.onerror=null;this.src=\'' . asset('images/onjecasa-products.svg') . '\';"><img src="' . e($url) . '" alt="' . $alt . '">';
    };
@endphp

<div class="onjecasa-products-page">
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
                <h2>Products</h2>
                <div class="thm-breadcrumb__box">
                    <ul class="thm-breadcrumb list-unstyled">
                        <li><a href="{{ route('home_page') }}">Home</a></li>
                        <li><span class="icon-right-arrow"></span></li>
                        <li>Products</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="product">
        <div class="container">
            <div class="row">
                <div class="col-xl-9 col-lg-12">
                    <div class="product__items">
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="product__showing-result">
                                    <div class="product__showing-text-box">
                                        <p class="product__showing-text">Showing {{ $shownCount ? '1-' . $shownCount : '0' }}/{{ $totalProducts }} of {{ $totalProducts }} results</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="product__all">
                            <div class="product__all-tab">
                                <div class="product__all-tab-button">
                                    <ul class="tabs-button-box clearfix">
                                        <li data-tab="#grid" class="tab-btn-item active-btn-item">
                                            <div class="product__all-tab-button-icon one"><i class="fa fa-solid fa-bars"></i></div>
                                        </li>
                                        <li data-tab="#list" class="tab-btn-item">
                                            <div class="product__all-tab-button-icon"><i class="fa fa-solid fa-list-ul"></i></div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="tabs-content-box">
                                    <div class="tab-content-box-item tab-content-box-item-active" id="grid">
                                        <div class="product__all-tab-content-box-item">
                                            <div class="product__all-tab-single">
                                                <div class="row">
                                                    @forelse($products as $product)
                                                        <div class="col-xl-4 col-lg-6 col-md-6">
                                                            <div class="single-product-style1 onjecasa-clickable-product" data-href="{{ route('product_details', $product->id) }}">
                                                                <div class="single-product-style1__img">
                                                                    {!! $renderProductMedia($product) !!}
                                                                    @if($loop->first)
                                                                        <ul class="single-product-style1__overlay"><li><p>New</p></li></ul>
                                                                    @endif
                                                                    <ul class="single-product-style1__info">
                                                                        <li><a href="{{ route('product_details', $product->id) }}" title="Quick View"><i class="fa fa-regular fa-eye"></i></a></li>
                                                                        <li>
                                                                            <form method="POST" action="{{ route('addcart', $product->id) }}">
                                                                                @csrf
                                                                                <input type="hidden" name="quantity" value="1">
                                                                                <button type="submit" title="Add to cart"><i class="fa fa-solid fa-cart-plus"></i></button>
                                                                            </form>
                                                                        </li>
                                                                        <li><a href="{{ route('product_details', $product->id) }}" title="View Details"><i class="fa fa-link"></i></a></li>
                                                                    </ul>
                                                                </div>
                                                                <div class="single-product-style1__content">
                                                                    <div class="single-product-style1__content-left">
                                                                        <h4><a href="{{ route('product_details', $product->id) }}">{{ $product->name }}</a></h4>
                                                                        <p>GHC {{ number_format((float) $product->price, 2) }}</p>
                                                                    </div>
                                                                    <div class="single-product-style1__content-right">
                                                                        <div class="single-product-style1__review">
                                                                            <i class="fa fa-star"></i>
                                                                            <p>{{ number_format(4.7 + (($loop->index % 3) / 10), 1) }}</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <div class="col-xl-12">
                                                            <div class="shop-product-tags product__sidebar-single">
                                                                <h3 class="product__sidebar-title">No products found</h3>
                                                                <p>Try another search, category, or price range.</p>
                                                            </div>
                                                        </div>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-content-box-item" id="list">
                                        <div class="product__all-tab-content-box-item">
                                            <div class="row">
                                                @forelse($products as $product)
                                                    <div class="col-xl-6 col-lg-6">
                                                        <div class="single-product-style2 onjecasa-clickable-product" data-href="{{ route('product_details', $product->id) }}">
                                                            <div class="row">
                                                                <div class="col-xl-6 col-lg-6 col-md-6">
                                                                    <div class="single-product-style2__img">
                                                                        {!! $renderProductMedia($product) !!}
                                                                        @if($loop->first)
                                                                            <ul class="single-product-style1__overlay"><li><p>New</p></li></ul>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div class="col-xl-6 col-lg-6 col-md-6">
                                                                    <div class="single-product-style2__content">
                                                                        <div class="single-product-style2__review">
                                                                            <i class="fa fa-star"></i>
                                                                            <i class="fa fa-star"></i>
                                                                            <i class="fa fa-star"></i>
                                                                            <i class="fa fa-star"></i>
                                                                            <i class="fa fa-star"></i>
                                                                        </div>
                                                                        <div class="single-product-style2__text">
                                                                            <h4><a href="{{ route('product_details', $product->id) }}">{{ $product->name }}</a></h4>
                                                                            <p>GHC {{ number_format((float) $product->price, 2) }}</p>
                                                                        </div>
                                                                        <ul class="single-product-style2__info">
                                                                            <li><a href="{{ route('product_details', $product->id) }}" title="Quick View"><i class="fa fa-regular fa-eye"></i></a></li>
                                                                            <li>
                                                                                <form method="POST" action="{{ route('addcart', $product->id) }}">
                                                                                    @csrf
                                                                                    <input type="hidden" name="quantity" value="1">
                                                                                    <button type="submit" title="Add to cart"><i class="fa fa-solid fa-cart-plus"></i></button>
                                                                                </form>
                                                                            </li>
                                                                            <li><a href="{{ route('product_details', $product->id) }}" title="View Details"><i class="fa fa-link"></i></a></li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="col-xl-12">
                                                        <div class="shop-product-tags product__sidebar-single">
                                                            <h3 class="product__sidebar-title">No products found</h3>
                                                            <p>Try another search, category, or price range.</p>
                                                        </div>
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-12">
                    <div class="product__sidebar">
                        <div class="shop-search product__sidebar-single">
                            <form method="GET" action="{{ route('product_page') }}">
                                <input type="text" name="search" value="{{ $query }}" placeholder="Search">
                                <input type="hidden" name="category" value="{{ $categoryFilter }}">
                                <button type="submit"><i class="fa fa-search"></i></button>
                            </form>
                        </div>
                        <div class="product__price-ranger product__sidebar-single">
                            <h3 class="product__sidebar-title">Price</h3>
                            <form class="price-ranger" method="GET" action="{{ route('product_page') }}">
                                <input type="hidden" name="search" value="{{ $query }}">
                                <input type="hidden" name="category" value="{{ $categoryFilter }}">
                                <div id="slider-range"
                                    data-min="{{ $priceFloor }}"
                                    data-max="{{ $priceCeiling }}"
                                    data-selected-min="{{ $selectedMinPrice }}"
                                    data-selected-max="{{ $selectedMaxPrice }}"></div>
                                <div class="ranger-min-max-block product-filter-inline">
                                    <input type="hidden" class="min" name="min_price" value="{{ $selectedMinPrice }}">
                                    <input type="hidden" class="max" name="max_price" value="{{ $selectedMaxPrice }}">
                                    <span class="price-ranger__values">
                                        GHC <span id="price-range-min">{{ number_format($selectedMinPrice) }}</span>
                                        <span>-</span>
                                        GHC <span id="price-range-max">{{ number_format($selectedMaxPrice) }}</span>
                                    </span>
                                    <input type="submit" value="Filter">
                                </div>
                            </form>
                        </div>

                        <div class="shop-category product__sidebar-single">
                            <h3 class="product__sidebar-title">Categories</h3>
                            <ul class="list-unstyled">
                                <li class="{{ $categoryFilter === '' ? 'active' : '' }}"><a href="{{ route('product_page', request()->except('category')) }}">All Products</a></li>
                                @foreach($categories as $category)
                                    <li class="{{ $categoryFilter === $category ? 'active' : '' }}"><a href="{{ route('product_page', array_merge(request()->except('category'), ['category' => $category])) }}">{{ $category }}</a></li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="shop-product-recent-products product__sidebar-single">
                            <h3 class="product__sidebar-title">Recent Products</h3>
                            <ul class="clearfix list-unstyled">
                                @foreach($recentProducts as $recent)
                                    <li>
                                        <div class="img">
                                            @if($recent->thumbnail_is_video && $recent->thumbnail_media_url)
                                                <video src="{{ $recent->thumbnail_media_url }}" autoplay muted loop playsinline preload="metadata"></video>
                                            @else
                                                <img src="{{ $recent->thumbnail_media_url ?: asset('images/onjecasa-products.svg') }}" alt="{{ $recent->name }}">
                                            @endif
                                            <a href="{{ route('product_details', $recent->id) }}"><i class="fa fa-link" aria-hidden="true"></i></a>
                                        </div>
                                        <div class="content">
                                            <div class="title"><h5><a href="{{ route('product_details', $recent->id) }}">{{ $recent->name }}</a></h5></div>
                                            <div class="price"><p>GHC {{ number_format((float) $recent->price, 2) }}</p></div>
                                            <div class="review"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star color"></i></div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="shop-product-tags product__sidebar-single">
                            <h3 class="product__sidebar-title">Product Tags</h3>
                            <div class="shop-product__tags-list">
                                @foreach($tags as $tag)
                                    <a href="{{ route('product_page', ['search' => $tag]) }}">{{ $tag }}</a>
                                @endforeach
                            </div>
                        </div>

                        <div class="shop-product-tags product__sidebar-single style">
                            <h3 class="product__sidebar-title">Reviews</h3>
                            <div class="sidebar-rating-box sidebar-rating-box--style2">
                                <ul class="list-unstyled">
                                    @foreach([5, 4, 3, 2, 1] as $stars)
                                        <li>
                                            <input type="radio" id="stars{{ $stars }}" name="rating" @checked($stars === 5)>
                                            <label for="stars{{ $stars }}">
                                                <i></i>
                                                @for($i = 1; $i <= 5; $i++)
                                                    <span class="fas fa-star {{ $i > $stars ? 'gray' : '' }}"></span>
                                                @endfor
                                            </label>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    (function ($) {
        const $slider = $('#slider-range');
        if (!$slider.length || typeof $.fn.slider === 'undefined') {
            return;
        }

        const min = Number($slider.data('min')) || 0;
        const max = Number($slider.data('max')) || 1;
        const selectedMin = Number($slider.data('selected-min')) || min;
        const selectedMax = Number($slider.data('selected-max')) || max;
        const $form = $slider.closest('form');
        const $minInput = $form.find('input[name="min_price"]');
        const $maxInput = $form.find('input[name="max_price"]');
        const $minLabel = $('#price-range-min');
        const $maxLabel = $('#price-range-max');
        const formatter = new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 });

        if ($slider.hasClass('ui-slider')) {
            $slider.slider('destroy');
        }

        function updatePriceRange(values) {
            $minInput.val(values[0]);
            $maxInput.val(values[1]);
            $minLabel.text(formatter.format(values[0]));
            $maxLabel.text(formatter.format(values[1]));
        }

        $slider.slider({
            range: true,
            min: min,
            max: max,
            values: [selectedMin, selectedMax],
            slide: function (event, ui) {
                updatePriceRange(ui.values);
            }
        });

        updatePriceRange($slider.slider('values'));
    })(jQuery);
</script>
<script>
    document.querySelectorAll('.onjecasa-clickable-product').forEach(function (card) {
        card.addEventListener('click', function (event) {
            if (event.target.closest('a, button, input, select, textarea, form')) {
                return;
            }

            const href = card.getAttribute('data-href');
            if (href) {
                window.location.href = href;
            }
        });
    });
</script>
@endpush


