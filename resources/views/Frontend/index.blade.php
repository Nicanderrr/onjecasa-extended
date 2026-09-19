@php
    $posSettings = [];
    if (\Illuminate\Support\Facades\Schema::hasTable('pos_settings')) {
        $posSettings = \Illuminate\Support\Facades\DB::table('pos_settings')->pluck('value', 'key')->all();
    }

    $brandName = \App\Support\BrandAssets::systemName();
    $brandLogoUrl = \App\Support\BrandAssets::logoUrl();
    $faviconUrl = \App\Support\BrandAssets::faviconUrl();
    $contactSettings = \Illuminate\Support\Facades\Schema::hasTable('contact_settings')
        ? \App\Models\ContactSetting::first()
        : null;
    $cartCount = Auth::check() && \Illuminate\Support\Facades\Schema::hasTable('carts')
        ? \App\Models\Cart::where('user_id', Auth::id())->sum('quantity')
        : 0;

    $homepageSlides = \App\Models\HomeSlide::whereIn('id', range(1, 13))->get()->keyBy('id');
    $heroSlide = $homepageSlides->get(1);
    $promoSlideOne = $homepageSlides->get(2);
    $promoSlideTwo = $homepageSlides->get(3);
    $aboutImageOne = $homepageSlides->get(4);
    $aboutImageTwo = $homepageSlides->get(5);
    $orderModeImages = collect(range(6, 10))->mapWithKeys(fn ($id) => [$id => $homepageSlides->get($id)]);
    $menuFavoritesBg = $homepageSlides->get(11);
    $siteFooterBg = $homepageSlides->get(13);
    $footerBgImageUrl = $siteFooterBg?->image_url ?: asset('themes/zabaga/assets/images/backgrounds/site-footer-two-bg.jpg');
    $menuFavoritesOverlayStrength = max(0, min(95, (int) ($posSettings['menu_favorites_overlay_strength'] ?? 90)));
    $menuFavoritesOverlayColor = preg_match('/^#[0-9A-Fa-f]{6}$/', $posSettings['menu_favorites_overlay_color'] ?? '')
        ? $posSettings['menu_favorites_overlay_color']
        : '#111111';
    $menuFavoritesImageOpacity = number_format((100 - $menuFavoritesOverlayStrength) / 100, 2, '.', '');
    $heroMediaSlides = collect([$heroSlide, $promoSlideOne, $promoSlideTwo])
        ->filter()
        ->map(fn ($slide) => [
            'main_header' => $slide->main_header ?: 'Groceries & Fresh Produce',
            'small_header' => $slide->small_header ?: 'Fresh . Tasty . Affordable',
            'image_url' => $slide->image_url,
            'video_url' => $slide->video_url,
        ])
        ->values();
    $heroVideoUrl = $heroMediaSlides->pluck('video_url')->filter()->first();
    $heroImages = collect([$heroSlide, $promoSlideOne, $promoSlideTwo])
        ->filter()
        ->map(fn ($slide) => $slide->image_url)
        ->values();
    if ($heroImages->isEmpty()) {
        $heroImages = collect([
            asset('themes/zabaga/assets/images/resources/banner-one-img-1-1.jpg'),
            asset('themes/zabaga/assets/images/resources/banner-one-img-1-2.jpg'),
            asset('themes/zabaga/assets/images/resources/banner-one-img-1-3.jpg'),
        ]);
    }

    $featuredProducts = \App\Models\ProductPage::latest()->take(3)->get();
    $teamProducts = \App\Models\ProductPage::inRandomOrder()->take(3)->get();
    $productCount = \Illuminate\Support\Facades\Schema::hasTable('product_pages') ? \App\Models\ProductPage::count() : 0;
    $branchCount = \Illuminate\Support\Facades\Schema::hasTable('branches') ? \Illuminate\Support\Facades\DB::table('branches')->where('is_active', true)->count() : 1;
    $orderCount = \Illuminate\Support\Facades\Schema::hasTable('orders') ? \App\Models\Order::count() : 0;
    $phone = $contactSettings?->business_number ?: '+233 57 833 9542';
    $email = $contactSettings?->form_email ?: 'orders@onjecasa.test';
    $address = $contactSettings?->office_address ?: 'Nii Okaiman West Main Road, Greater Accra';
@endphp
<!DOCTYPE html>
<html lang="en">
    
<!-- Mirrored from helpest.laravel.scriptfusions.com/index3 by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 08 Dec 2025 12:49:24 GMT -->
<!-- Added by HTTrack --><meta http-equiv="content-type" content="text/html;charset=UTF-8" /><!-- /Added by HTTrack -->
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $brandName }} Supermarket</title>
    <!-- favicons Icons -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $faviconUrl }}" />
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $faviconUrl }}" />
    <link rel="icon" type="image/png" sizes="16x16" href="{{ $faviconUrl }}" />
    <link rel="manifest" href="/themes/zabaga/assets/images/favicons/site.webmanifest" />
    <meta name="description" content="Everyday groceries, fresh produce, household goods, and essentials from ONJECASA." />

    <!-- fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&amp;display=swap"
        rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Figtree:ital,wght@0,300..900;1,300..900&amp;display=swap"
        rel="stylesheet">


    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400..700&amp;display=swap" rel="stylesheet">


    <link rel="stylesheet" href="/themes/zabaga/assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="/themes/zabaga/assets/css/animate.min.css" />
    <link rel="stylesheet" href="/themes/zabaga/assets/css/custom-animate.css" />
    <link rel="stylesheet" href="/themes/zabaga/assets/css/swiper.min.css" />
    <link rel="stylesheet" href="/themes/zabaga/assets/css/font-awesome-all.css" />
    <link rel="stylesheet" href="/themes/zabaga/assets/css/jarallax.css" />
    <link rel="stylesheet" href="/themes/zabaga/assets/css/jquery.magnific-popup.css" />
    <link rel="stylesheet" href="/themes/zabaga/assets/css/odometer.min.css" />
    <link rel="stylesheet" href="/themes/zabaga/assets/css/flaticon.css">
    <link rel="stylesheet" href="/themes/zabaga/assets/css/owl.carousel.min.css" />
    <link rel="stylesheet" href="/themes/zabaga/assets/css/owl.theme.default.min.css" />
    <link rel="stylesheet" href="/themes/zabaga/assets/css/nice-select.css" />
    <link rel="stylesheet" href="/themes/zabaga/assets/css/jquery-ui.css" />
    <link rel="stylesheet" href="/themes/zabaga/assets/css/aos.css" />
    <link rel="stylesheet" href="/themes/zabaga/assets/css/vegas.min.css" />


    <link rel="stylesheet" href="/themes/zabaga/assets/css/module-css/slider.css" />
    <link rel="stylesheet" href="/themes/zabaga/assets/css/module-css/footer.css" />
    <link rel="stylesheet" href="/themes/zabaga/assets/css/module-css/donate.css" />
    <link rel="stylesheet" href="/themes/zabaga/assets/css/module-css/about.css" />
    <link rel="stylesheet" href="/themes/zabaga/assets/css/module-css/services.css" />
    <link rel="stylesheet" href="/themes/zabaga/assets/css/module-css/become-volenteer.css" />
    <link rel="stylesheet" href="{{ asset('themes/zabaga/assets/css/module-css/causes.css') }}?v={{ filemtime(public_path('themes/zabaga/assets/css/module-css/causes.css')) }}" />
    <link rel="stylesheet" href="/themes/zabaga/assets/css/module-css/counter.css" />
    <link rel="stylesheet" href="/themes/zabaga/assets/css/module-css/video.css" />
    <link rel="stylesheet" href="/themes/zabaga/assets/css/module-css/team.css" />
    <link rel="stylesheet" href="/themes/zabaga/assets/css/module-css/brand.css" />
    <link rel="stylesheet" href="/themes/zabaga/assets/css/module-css/testimonial.css" />
    <link rel="stylesheet" href="/themes/zabaga/assets/css/module-css/donation.css" />
    <link rel="stylesheet" href="/themes/zabaga/assets/css/module-css/faq.css" />
    <link rel="stylesheet" href="/themes/zabaga/assets/css/module-css/blog.css" />
    <link rel="stylesheet" href="/themes/zabaga/assets/css/module-css/gallery.css" />

    
      <link rel="stylesheet" href="/themes/zabaga/assets/css/module-css/process.css">
<link rel="stylesheet" href="/themes/zabaga/assets/css/module-css/sliding-text.css">
<link rel="stylesheet" href="/themes/zabaga/assets/css/module-css/contact.css">
<link rel="stylesheet" href="/themes/zabaga/assets/css/module-css/cta.css">
<link rel="stylesheet" href="/themes/zabaga/assets/css/module-css/feature.css">
<link rel="stylesheet" href="/themes/zabaga/assets/css/module-css/why-choose.css">
<link rel="stylesheet" href="/themes/zabaga/assets/css/module-css/banner.css">
<link rel="stylesheet" href="/themes/zabaga/assets/css/module-css/event.css">
  
    <!-- template styles -->
    <link rel="stylesheet" href="{{ asset('themes/zabaga/assets/css/style.css') }}?v={{ filemtime(public_path('themes/zabaga/assets/css/style.css')) }}" />
    <link rel="stylesheet" href="/themes/zabaga/assets/css/responsive.css" />
    <link rel="stylesheet" href="{{ asset('css/onjecasa-navbar.css') }}?v={{ filemtime(public_path('css/onjecasa-navbar.css')) }}" />
    <link rel="stylesheet" href="{{ asset('css/onjecasa-zabaga-frontend.css') }}?v={{ filemtime(public_path('css/onjecasa-zabaga-frontend.css')) }}" />
    <style>
        .banner-one__slider.has-video {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
            will-change: transform;
        }

        .banner-one__slider-outer {
            overflow: hidden;
        }

        .banner-one__slider.has-video .slider-bg-slide {
            display: none;
        }

        .onjecasa-hero-media-slide {
            position: absolute;
            inset: 0;
            opacity: 0;
            visibility: hidden;
            transition: opacity 900ms ease, visibility 900ms ease;
            z-index: 0;
        }

        .onjecasa-hero-media-slide.is-active {
            opacity: 1;
            visibility: visible;
            z-index: 2;
        }

        .onjecasa-hero-video-bg,
        .onjecasa-hero-image-bg {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center right;
        }

        @media (min-width: 768px) {
            .banner-one__slider.has-video {
                transform: translate3d(0, var(--hero-parallax-y, 0px), 0) scale(1.03);
            }
        }

        .banner-one .container {
            position: relative;
            z-index: 3;
        }

        .banner-one__title {
            max-width: 620px;
            font-size: 76px;
            line-height: .95;
        }

        .causes-three__img .onjecasa-menu-video {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .causes-three__img,
        .team-three__img,
        .about-three__img-1,
        .about-three__img-2 {
            overflow: hidden;
        }

        .causes-three__img {
            height: 278px;
        }

        .causes-three__img img,
        .causes-three__img video,
        .team-three__img img,
        .team-three__img video,
        .about-three__img-1 img,
        .about-three__img-2 img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .team-three__img {
            aspect-ratio: 370 / 420;
        }

        .event-one__img {
            overflow: hidden;
            background: #fef3c7;
            aspect-ratio: 410 / 560;
        }

        .event-one__img img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .event-one__single-2 .event-one__img,
        .event-one__single-3 .event-one__img,
        .event-one__single-4 .event-one__img,
        .event-one__single-5 .event-one__img {
            aspect-ratio: 410 / 265;
        }

        .causes-three__bg-box {
            background-color: {{ $menuFavoritesOverlayColor }};
        }

        .causes-three__bg {
            opacity: {{ $menuFavoritesImageOpacity }};
        }

        .about-three__img-1 {
            aspect-ratio: 520 / 650;
        }

        .about-three__img-2 {
            aspect-ratio: 300 / 330;
        }

        @media (max-width: 1199px) {
            .banner-one__title {
                max-width: 560px;
                font-size: 60px;
                line-height: 1;
            }
        }

        @media (max-width: 767px) {
            .main-menu-three__wrapper-inner {
                min-height: 72px;
                padding-left: 8px !important;
                padding-right: 12px !important;
            }

            .main-header-three .main-menu-three__left {
                flex: 0 0 auto;
            }

            .main-header-three .main-menu-three__logo,
            .stricky-header.main-menu-three .main-menu-three__logo {
                width: 92px !important;
                max-width: 92px !important;
                padding: 6px 0 !important;
                margin-left: -6px;
            }

            .main-header-three .main-menu-three__logo a,
            .stricky-header.main-menu-three .main-menu-three__logo a {
                width: 92px !important;
                height: 48px !important;
            }

            .main-header-three .main-menu-three__logo img,
            .stricky-header.main-menu-three .main-menu-three__logo img,
            .stricky-header .main-menu-three__logo img,
            .stricky-header .sticky-header__content img {
                width: 92px !important;
                max-width: 92px !important;
                height: 48px !important;
                max-height: 48px !important;
            }

            .main-menu-three__right {
                gap: 10px;
            }

            .banner-one {
                min-height: min(620px, calc(100svh - 72px));
                padding: 0 !important;
                overflow: hidden;
                background: #111;
            }

            .banner-one__shape-1,
            .banner-one__shape-2 {
                display: none;
            }

            .banner-one__slider-outer {
                display: block !important;
                position: absolute !important;
                inset: 0 !important;
                right: 0 !important;
                width: 100% !important;
                height: 100% !important;
                z-index: 0;
            }

            .banner-one__slider.has-video {
                display: block;
                width: 100%;
                height: 100%;
                transform: none !important;
            }

            .banner-one__slider.has-video .onjecasa-hero-media-slide {
                position: absolute;
                inset: 0;
                width: 100%;
                height: 100%;
                opacity: 1;
                visibility: visible;
                z-index: auto;
                transform: translate3d(100%, 0, 0);
                transition: transform 760ms cubic-bezier(.22, .8, .24, 1);
            }

            .banner-one__slider.has-video::after {
                content: "";
                position: absolute;
                inset: 0;
                pointer-events: none;
                background:
                    linear-gradient(180deg, rgba(17, 17, 17, .18), rgba(17, 17, 17, .48) 56%, rgba(17, 17, 17, .78)),
                    linear-gradient(90deg, rgba(17, 17, 17, .48), rgba(17, 17, 17, .08));
                z-index: 1;
            }

            .onjecasa-hero-video-bg,
            .onjecasa-hero-image-bg {
                object-position: 58% center;
            }

            .banner-one .container {
                min-height: min(620px, calc(100svh - 72px));
                display: flex;
                align-items: flex-end;
                padding-bottom: 34px;
            }

            .banner-one__inner,
            .banner-one__content {
                width: 100%;
            }

            .banner-one__title {
                max-width: 100%;
                font-size: 32px;
                line-height: 1.04;
                letter-spacing: 0;
                overflow-wrap: anywhere;
                text-shadow: 0 2px 18px rgba(0, 0, 0, .35);
            }

            .banner-one__sub-title-box {
                max-width: 100%;
            }

            .banner-one__btn-box {
                flex-wrap: wrap;
                gap: 10px;
            }

            .banner-one__video-link {
                display: none;
            }

            .feature-one {
                padding: 34px 0 0;
                overflow: hidden;
            }

            .feature-one .row {
                display: flex;
                flex-wrap: nowrap;
                gap: 12px;
                margin-left: -16px;
                margin-right: -16px;
                padding: 2px 16px 14px;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
                -webkit-overflow-scrolling: touch;
            }

            .feature-one .row > [class*="col-"] {
                flex: 0 0 min(68vw, 255px);
                max-width: min(68vw, 255px);
                padding-left: 0;
                padding-right: 0;
                scroll-snap-align: start;
            }

            .feature-one__single {
                min-height: 188px;
                padding: 24px 18px 22px;
                clip-path: polygon(8% 0, 100% 0, 92% 100%, 0 100%);
            }

            .feature-one__single::before {
                inset: 4px;
                clip-path: inherit;
            }

            .feature-one__icon span {
                font-size: 38px;
            }

            .feature-one__title {
                margin: 8px 0 7px;
                font-size: 18px;
                line-height: 1.12;
            }

            .feature-one__text {
                margin-bottom: 10px;
                font-size: 13px;
                line-height: 1.35;
            }

            .feature-one__read-more {
                font-size: 12px;
                line-height: 1;
            }

            .causes-three {
                padding: 54px 0 42px;
            }

            .causes-three .section-title {
                margin-bottom: 22px;
            }

            .causes-three .section-title__tagline {
                font-size: 13px;
            }

            .causes-three .section-title__title {
                font-size: 27px;
                line-height: 1.06;
            }

            .causes-three .row > [class*="col-"] {
                flex-basis: min(72vw, 275px);
                max-width: min(72vw, 275px);
            }

            .causes-three__single {
                margin-bottom: 0;
            }

            .causes-three__img {
                height: 142px;
            }

            .causes-three__content {
                padding: 22px 15px 0;
            }

            .causes-three__donate-btn-box {
                top: -31px;
            }

            .causes-three__donate-btn-box .thm-btn {
                min-height: 34px;
                padding: 6px 16px 5px;
                font-size: 11px;
            }

            .causes-three__title {
                margin-bottom: 14px;
                font-size: 17px;
                line-height: 1.2;
            }

            .causes-three__goals {
                gap: 8px;
            }

            .causes-three__goals p,
            .causes-three__bottom p {
                font-size: 12px;
                line-height: 1.3;
            }

            .causes-three__bottom {
                padding: 13px 15px;
            }

            .causes-three__list {
                gap: 8px;
            }

            .causes-three__list li .icon {
                display: none;
            }

            .event-one {
                display: none !important;
            }

            .site-footer-three .site-footer-two__top {
                margin-top: 0;
            }

            .site-footer-three .site-footer-two__top-inner {
                padding: 20px 0 12px !important;
            }

            .site-footer-two__shape-1,
            .site-footer-two__content-and-social-box {
                display: none;
            }

            .footer-widget-two__about,
            .footer-widget-two__services,
            .footer-widget-two__links,
            .footer-widget-two__contact {
                margin: 0 0 10px !important;
            }

            .footer-widget-two__about-logo img {
                max-width: 76px !important;
                max-height: 40px !important;
            }

            .footer-widget-two__about-text {
                display: none;
            }

            .footer-widget-two__title {
                margin-bottom: 5px;
                font-size: 13px;
                line-height: 1.2;
            }

            .footer-widget-two__services-list,
            .footer-widget-two__contact-list {
                display: flex;
                flex-wrap: wrap;
                gap: 5px 12px;
            }

            .footer-widget-two__services-list li+li,
            .footer-widget-two__contact-list li+li {
                margin-top: 0;
            }

            .footer-widget-two__services-list li a,
            .footer-widget-two__contact-list li p,
            .footer-widget-two__contact-list li p a {
                font-size: 11px;
                line-height: 1.25;
            }

            .footer-widget-two__contact-list li {
                gap: 5px;
            }

            .footer-widget-two__contact-list li .icon span {
                font-size: 11px;
            }

            .footer-widget-two__input input[type="search"],
            .footer-widget-two__input input[type="email"] {
                height: 34px;
                font-size: 12px;
                padding-left: 11px;
                padding-right: 38px;
            }

            .footer-widget-two__btn {
                width: 28px;
                height: 28px;
                right: 3px;
            }

            .site-footer-two__bottom-inner {
                display: grid;
                justify-items: start;
                gap: 5px;
                padding: 8px 0;
            }

            .site-footer-two__copyright-text,
            .site-footer-two__bottom-menu li a {
                font-size: 10px;
                line-height: 1.2;
            }

            .site-footer-two__bottom-menu li+li {
                margin-left: 8px;
            }
        }

        @media (max-width: 480px) {
            .banner-one,
            .banner-one .container {
                min-height: min(560px, calc(100svh - 72px));
            }

            .banner-one .container {
                padding-bottom: 30px;
            }

            .banner-one__title {
                font-size: 28px;
            }
        }
    </style>
    
    </head>

    <body class="custom-cursor">

        <div class="custom-cursor__cursor"></div>
        <div class="custom-cursor__cursor-two"></div>

        <!--Start Preloader-->
    <div class="loader js-preloader">
        <div></div>
        <div></div>
        <div></div>
    </div>
    <!--End Preloader-->
        <div class="page-wrapper">
            <header class="main-header-three">
                <nav class="main-menu main-menu-three">
                    <div class="main-menu-three__wrapper">
                        <div class="main-menu-three__wrapper-inner">
                            <div class="main-menu-three__left">
                                <div class="main-menu-three__logo">
                                    <a href="{{ route('home_page') }}"><img src="{{ $brandLogoUrl }}" alt="{{ $brandName }}"></a>
                                </div>
                            </div>
                            <div class="main-menu-three__main-menu-box">
                                <a href="#" class="mobile-nav__toggler"><i class="fa fa-bars"></i></a>
                                <ul class="main-menu__list onjecasa-clean-nav">
                                    <li class="{{ request()->routeIs('home_page') ? 'current' : '' }}"><a href="{{ route('home_page') }}">Home</a></li>
                                    <li class="{{ request()->routeIs('product_page', 'product_details') ? 'current' : '' }}"><a href="{{ route('product_page') }}">Products</a></li>
                                    <li class="{{ request()->routeIs('about_page') ? 'current' : '' }}"><a href="{{ route('about_page') }}">About</a></li>
                                    <li class="{{ request()->routeIs('contact_page') ? 'current' : '' }}"><a href="{{ route('contact_page') }}">Contact</a></li>
                                    @auth
                                        <li class="{{ request()->routeIs('order.history') ? 'current' : '' }}"><a href="{{ route('order.history') }}">Orders</a></li>
                                        @if(in_array((int) Auth::user()->is_admin, [1, 2], true) || in_array(Auth::user()->role ?? null, ['admin', 'branch_admin', 'superadmin'], true))
                                            <li class="dropdown">
                                                <a href="{{ route('admin.panel') }}">Admin</a>
                                                <ul class="shadow-box">
                                                    <li><a href="{{ route('admin.panel') }}">Website</a></li>
                                                    <li><a href="{{ route('home_slide') }}">Homepage</a></li>
                                                    <li><a href="{{ route('pos.admin.dashboard') }}">POS</a></li>
                                                    @if((int) Auth::user()->is_admin === 2 || (Auth::user()->role ?? null) === 'superadmin')
                                                        <li><a href="{{ route('superadmin.dashboard') }}">Superadmin</a></li>
                                                    @endif
                                                </ul>
                                            </li>
                                        @endif
                                    @endauth
                                </ul>
                            </div>
                            <div class="main-menu-three__right">
                                <div class="main-menu-three__btn-box">
                                    <a href="{{ route('product_page') }}" class="thm-btn">
                                        <span class="thm-btn-text">Order Now</span>
                                        <span class="thm-btn-icon-box"><i class="fas fa-arrow-right"></i></span>
                                    </a>
                                </div>
                                <div class="main-menu-three__cart">
                                    <a href="{{ route('view_cart') }}">
                                        <span class="icon-shopping-cart"></span>
                                        <span class="main-menu-three__cart-count">{{ $cartCount }}</span>
                                    </a>
                                </div>
                                <div class="main-menu-three__search-box">
                                    <span class="main-menu-three__search searcher-toggler-box icon-search"></span>
                                </div>
                                <div class="main-menu-three__login">
                                    @guest
                                        <a href="{{ route('login') }}" aria-label="Login"><span class="fas fa-user"></span></a>
                                    @else
                                        <a href="{{ route('order.history') }}" aria-label="My account"><span class="fas fa-user-check"></span></a>
                                    @endguest
                                </div>
                                @auth
                                    <form method="POST" action="{{ route('logout') }}" class="main-menu-three__logout">
                                        @csrf
                                        <button type="submit" aria-label="Logout"><span class="fas fa-sign-out-alt"></span></button>
                                    </form>
                                @endauth
                            </div>
                        </div>
                    </div>
                </nav>
            </header>
            <div class="stricky-header stricked-menu main-menu main-menu-three">
                <div class="sticky-header__content"></div>
            </div>

                    <!-- Banner One Start -->
        <section class="banner-one">
            <div class="banner-one__shape-1">
                <img src="/themes/zabaga/assets/images/shapes/banner-one-shape-1.png" alt="">
            </div>
            <div class="banner-one__shape-2 img-bounce">
                <img src="/themes/zabaga/assets/images/shapes/banner-one-shape-2.png" alt="">
            </div>
            <div class="banner-one__shape-bg" style="background-image: url(/themes/zabaga/assets/images/shapes/banner-one-shape-bg.jpg);"></div>
            <!--Banner Bottom Start -->
            <div class="banner-one__slider-outer">
                <div class="banner-one__slider {{ $heroMediaSlides->isNotEmpty() ? 'has-video' : '' }}">
                    @if($heroMediaSlides->isNotEmpty())
                        @foreach($heroMediaSlides as $index => $mediaSlide)
                            <div class="onjecasa-hero-media-slide {{ $index === 0 ? 'is-active' : '' }}" data-hero-slide="{{ $index }}">
                                @if(! empty($mediaSlide['video_url']))
                                    <video class="onjecasa-hero-video-bg" muted playsinline preload="metadata" poster="{{ $mediaSlide['image_url'] }}">
                                        <source src="{{ $mediaSlide['video_url'] }}">
                                    </video>
                                @else
                                    <img class="onjecasa-hero-image-bg" src="{{ $mediaSlide['image_url'] }}" alt="{{ $mediaSlide['main_header'] }}">
                                @endif
                            </div>
                        @endforeach
                    @endif
                    <div class="slider-bg-slide" data-options='{ 
                    "delay": 10000, 
                    "transitionDuration": 4000, 
                    "slides": @json($heroImages->map(fn ($url) => ['src' => $url])->values()), 
                        "transition": "fade", 
                        "animation": "random", 
                        "animationDuration": "10000", 
                        "timer": false, 
                        "align": "top" 
                    }'>
                    </div>
                </div>
            </div>
            <!--Banner Bottom End -->
            <div class="container">
                <div class="banner-one__inner">
                    <div class="banner-one__content">
                        <div class="banner-one__sub-title-box" data-aos="fade-right" data-aos-duration="2000" data-aos-delay="0">
                            <div class="banner-one__sub-title-shape"></div>
                            <p class="banner-one__sub-title" data-hero-subtitle>{{ $heroSlide?->small_header ?: 'Fresh . Tasty . Affordable' }}</p>
                        </div>
                        <h2 class="banner-one__title" data-aos="fade-left" data-aos-duration="2000" data-aos-delay="500" data-hero-title>{!! nl2br(e($heroSlide?->main_header ?: 'Groceries & Fresh Produce')) !!}</h2>
                        <div class="banner-one__btn-box" data-aos="fade-right" data-aos-duration="2000" data-aos-delay="800">
                            <div class="banner-one__btn">
                                <a href="{{ route('product_page') }}" class="thm-btn">
                                    <span class="thm-btn-text">Order Now</span>
                                    <span class="thm-btn-icon-box"><i class="fas fa-arrow-right"></i></span>
                                </a>
                            </div>
                            <div class="banner-one__video-link">
                                <a href="{{ $heroVideoUrl ?: 'https://www.youtube.com/watch?v=Get7rqXYrbQ' }}" class="video-popup">
                                    <div class="banner-one__video-icon">
                                        <span class="fa fa-play"></span>
                                        <i class="ripple"></i>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--Banner One End -->

        <!--Feature One Start -->
        <section class="feature-one">
            <div class="container">
                <ul class="row list-unstyled">
                    <!--Feature One Single Start-->
                    <li class="col-xl-4 col-lg-4" data-aos="fade-right" data-aos-duration="1000" data-aos-delay="0">
                        <div class="feature-one__single">
                            <div class="feature-one__single-bg" style="background-image: url(/themes/zabaga/assets/images/backgrounds/feature-one-single-bg-1.jpg);">
                            </div>
                            <div class="feature-one__icon">
                                <span class="icon-fund-raising"></span>
                            </div>
                            <h3 class="feature-one__title"><a href="{{ route('product_page') }}">Groceries</a></h3>
                            <p class="feature-one__text">Pantry staples, beverages, household goods, personal care, and bulk deals.</p>
                            <a href="{{ route('product_page') }}" class="feature-one__read-more">order food<span class="icon-right-arrow"></span></a>
                        </div>
                    </li>
                    <!--Feature One Single End-->
                    <!--Feature One Single Start-->
                    <li class="col-xl-4 col-lg-4" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="100">
                        <div class="feature-one__single">
                            <div class="feature-one__single-bg" style="background-image: url(/themes/zabaga/assets/images/backgrounds/feature-one-single-bg-2.jpg);">
                            </div>
                            <div class="feature-one__icon">
                                <span class="icon-helping-hand"></span>
                            </div>
                            <h3 class="feature-one__title"><a href="{{ route('product_page') }}">Fresh Produce</a></h3>
                            <p class="feature-one__text">Fresh fruits, vegetables, chilled items, and daily essentials.</p>
                            <a href="{{ route('product_page') }}" class="feature-one__read-more">view produce<span class="icon-right-arrow"></span></a>
                        </div>
                    </li>
                    <!--Feature One Single End-->
                    <!--Feature One Single Start-->
                    <li class="col-xl-4 col-lg-4" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="200">
                        <div class="feature-one__single">
                            <div class="feature-one__single-bg" style="background-image: url(/themes/zabaga/assets/images/backgrounds/feature-one-single-bg-3.jpg);">
                            </div>
                            <div class="feature-one__icon">
                                <span class="icon-donation"></span>
                            </div>
                            <h3 class="feature-one__title"><a href="{{ route('contact_page') }}">Group Orders</a></h3>
                            <p class="feature-one__text">Office lunches, family packs, and bulk rice combos from Accra.</p>
                            <a href="{{ route('contact_page') }}" class="feature-one__read-more">book now<span class="icon-right-arrow"></span></a>
                        </div>
                    </li>
                    <!--Feature One Single End-->
                </ul>
            </div>
        </section>
        <!--Feature One End -->

        <!--About Three Start -->
        <section class="about-three">
            <div class="container">
                <div class="row">
                    <div class="col-xl-6">
                        <div class="about-three__left wow slideInLeft" data-wow-delay="100ms" data-wow-duration="2500ms">
                            <div class="about-three__img-box">
                                <div class="about-three__img-1">
                                    <img src="{{ $aboutImageOne?->image_url ?: asset('themes/zabaga/assets/images/resources/about-three-img-1.jpg') }}" alt="ONJECASA">
                                </div>
                                <div class="about-three__img-two-inner">
                                    <div class="about-three__img-2">
                                        <img src="{{ $aboutImageTwo?->image_url ?: asset('themes/zabaga/assets/images/resources/about-three-img-2.jpg') }}" alt="ONJECASA supermarket products">
                                    </div>
                                </div>
                                <div class="about-three__help-text">
                                    <p>Eat Well <span>Daily</span></p>
                                </div>
                                <div class="about-three__shape-1">
                                    <img src="/themes/zabaga/assets/images/shapes/about-three-shape-1.png" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="about-three__right wow fadeInRight" data-wow-delay="300ms">
                            <div class="section-title text-left sec-title-animation animation-style2">
                                <div class="section-title__tagline-box">
                                    <span class="section-title__tagline">About Us</span>
                                </div>
                                <h2 class="section-title__title title-animation">Everyday Supermarket
                                    With
                                    <span>Fresh Stock Energy.</span>
                                </h2>
                            </div>
                            <p class="about-three__text-1">ONJECASA serves fresh produce, groceries, beverages, household essentials, pickup, delivery, and bulk orders from Accra.</p>
                            <ul class="about-three__points list-unstyled">
                                <li>
                                    <div class="icon">
                                        <span class="icon-check"></span>
                                    </div>
                                    <div class="content">
                                        <h4>Everyday stock for home and business</h4>
                                        <p>Groceries, beverages, fresh produce, personal care, household goods,<br>
                                            and extras from the live menu.</p>
                                    </div>
                                </li>
                                <li>
                                    <div class="icon">
                                        <span class="icon-check"></span>
                                    </div>
                                    <div class="content">
                                        <h4>Connected to orders, stock, and POS control</h4>
                                        <p>The frontend menu talks to the same products, cart, checkout,<br>
                                            and admin database used by the backend.</p>
                                    </div>
                                </li>
                            </ul>
                            <div class="about-three__bottom-video-box">
                                <div class="about-three__btn-box">
                                    <a href="{{ route('about_page') }}" class="thm-btn">
                                        <span class="thm-btn-text">About ONJECASA</span>
                                        <span class="thm-btn-icon-box"><i class="fas fa-arrow-right"></i></span>
                                    </a>
                                </div>
                                <div class="about-three__video-link">
                                    <a href="https://www.youtube.com/watch?v=Get7rqXYrbQ" class="video-popup">
                                        <div class="about-three__video-icon">
                                            <span class="fa fa-play"></span>
                                            <i class="ripple"></i>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--About Three End -->

        <!--Causes Three Start-->
        <section class="causes-three">
            <div class="causes-three__bg-box">
                <div class="causes-three__bg" style="background-image: url({{ $menuFavoritesBg?->image_url ?: asset('themes/zabaga/assets/images/backgrounds/causes-three-bg.jpg') }});">
                </div>
            </div>
            <div class="container">
                <div class="section-title text-center sec-title-animation animation-style1">
                    <div class="section-title__tagline-box">
                        <span class="section-title__tagline">ONJECASA Store Favorites</span>
                    </div>
                    <h2 class="section-title__title title-animation">Everyday Products Ready
                        For<br> <span> Pickup & Delivery.</span>
                    </h2>
                </div>
                @php($menuCards = $featuredProducts->values())
                <ul class="row list-unstyled">
                    @foreach([0, 1, 2] as $cardIndex)
                        @php($menuCard = $menuCards->get($cardIndex))
                        <li class="col-xl-4 col-lg-4 wow fadeInUp" data-wow-delay="{{ 100 + ($cardIndex * 100) }}ms">
                            <div class="causes-three__single">
                                <div class="causes-three__img">
                                    @if($menuCard?->thumbnail_is_video && $menuCard?->thumbnail_media_url)
                                        <video class="onjecasa-menu-video" src="{{ $menuCard->thumbnail_media_url }}" autoplay muted loop playsinline preload="metadata"></video>
                                    @else
                                        <img src="{{ $menuCard?->thumbnail_media_url ?: asset('images/onjecasa-products.svg') }}" alt="{{ $menuCard?->name ?: 'ONJECASA product' }}">
                                    @endif
                                </div>
                                <div class="causes-three__content-box">
                                    <div class="causes-three__donate-btn-box">
                                        <a href="{{ $menuCard ? route('product_details', $menuCard->id) : route('product_page') }}" class="thm-btn causes-three__donate-btn">Order Now</a>
                                    </div>
                                    <div class="causes-three__content">
                                        <h3 class="causes-three__title">
                                            <a href="{{ $menuCard ? route('product_details', $menuCard->id) : route('product_page') }}">{{ $menuCard?->name ?: ['Premium Rice 5kg', 'Cooking Oil 1L', 'Bottled Water Pack'][$cardIndex] }}</a>
                                        </h3>
                                        <div class="causes-three__progress">
                                            <div class="bar">
                                                <div class="bar-inner count-bar" data-percent="75%">
                                                    <div class="count-text">75%</div>
                                                </div>
                                            </div>
                                            <div class="causes-three__goals">
                                                <p><span>GHC {{ number_format((float) ($menuCard?->price ?? [45, 45, 50][$cardIndex]), 2) }}</span> Price</p>
                                                <p><span>{{ (int) ($menuCard?->stock ?? 0) }}</span> Stock</p>
                                            </div>
                                        </div>
                                        <div class="causes-three__btn-box">
                                            <a href="{{ $menuCard ? route('product_details', $menuCard->id) : route('product_page') }}" class="causes-three__read-more">View Product <span class="icon-plus-sign"></span></a>
                                        </div>
                                    </div>
                                    <div class="causes-three__bottom">
                                        <ul class="list-unstyled causes-three__list">
                                            <li>
                                                <div class="icon">
                                                    <span class="icon-calendar"></span>
                                                </div>
                                                <div class="text">
                                                    <p>{{ $menuCard?->description ?: 'Groceries' }}</p>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="icon">
                                                    <span class="icon-clock"></span>
                                                </div>
                                                <div class="text">
                                                    <p>Pickup / Delivery</p>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
        <!--Causes Three End-->

        <!--Team Three Start-->
        <section class="team-three">
            <div class="container">
                <div class="section-title text-center sec-title-animation animation-style1">
                    <div class="section-title__tagline-box">
                        <span class="section-title__tagline">Popular Plates</span>
                    </div>
                    <h2 class="section-title__title title-animation">Products Customers Keep <span>Ordering</span>.
                    </h2>
                </div>
                <ul class="row list-unstyled">
                    @foreach([
                        ['class' => 'fadeInLeft', 'delay' => 100, 'fallback' => 'Bel Aqua', 'price' => 4],
                        ['class' => 'fadeInUp', 'delay' => 200, 'fallback' => 'Fanta Lemon', 'price' => 7],
                        ['class' => 'fadeInRight', 'delay' => 300, 'fallback' => 'Vitamilk', 'price' => 15],
                    ] as $teamIndex => $teamCard)
                        @php($teamProduct = $teamProducts->get($teamIndex))
                    <li class="col-xl-4 col-lg-4 wow {{ $teamCard['class'] }}" data-wow-delay="{{ $teamCard['delay'] }}ms">
                        <!--Team One Single-->
                            <div class="team-three__single">
                            <div class="team-three__img">
                                @if($teamProduct?->thumbnail_is_video && $teamProduct?->thumbnail_media_url)
                                    <video src="{{ $teamProduct->thumbnail_media_url }}" autoplay muted loop playsinline preload="metadata"></video>
                                @else
                                    <img src="{{ $teamProduct?->thumbnail_media_url ?: asset('images/onjecasa-products.svg') }}" alt="{{ $teamProduct?->name ?: 'ONJECASA product' }}">
                                @endif
                            </div>
                            <div class="team-three__content">
                                <h4 class="team-three__name"><a href="{{ $teamProduct ? route('product_details', $teamProduct->id) : route('product_page') }}">{{ $teamProduct?->name ?: $teamCard['fallback'] }}</a></h4>
                                <p class="team-three__title">GHC {{ number_format((float) ($teamProduct?->price ?? $teamCard['price']), 2) }}</p>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </section>
        <!--Team Three End-->

        <!--Event One Start -->
        <section class="event-one">
            <div class="container">
                <div class="section-title text-center sec-title-animation animation-style1">
                    <div class="section-title__tagline-box">
                        <span class="section-title__tagline">Order Modes</span>
                    </div>
                    <h2 class="section-title__title title-animation">Choose How You Want <br><span>Your Shopping.</span>
                    </h2>
                </div>
                <div class="row">
                    <div class="col-xl-4 col-lg-4 col-md-12 wow fadeInLeft" data-wow-delay="100ms">
                        <div class="event-one__single">
                            <div class="event-one__img-box">
                                <div class="event-one__img">
                                    <img src="{{ $orderModeImages->get(6)?->image_url ?: asset('themes/zabaga/assets/images/resources/event-1-1.jpg') }}" alt="Pickup Orders">
                                </div>
                                <div class="event-one__content">
                                    <h3 class="event-one__title"><a href="{{ route('product_page') }}">Pickup Orders</a>
                                    </h3>
                                    <ul class="event-one__meta list-unstyled">
                                        <li>
                                            <div class="icon">
                                                <span class="icon-calendar"></span>
                                            </div>
                                            <p>Daily</p>
                                        </li>
                                        <li>
                                            <div class="icon">
                                                <span class="icon-pin"></span>
                                            </div>
                                            <p>{{ $address }}</p>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-6">
                        <div class="event-one__single event-one__single-2 wow fadeInUp" data-wow-delay="200ms">
                            <div class="event-one__img-box">
                                <div class="event-one__img">
                                    <img src="{{ $orderModeImages->get(7)?->image_url ?: asset('themes/zabaga/assets/images/resources/event-1-2.jpg') }}" alt="Delivery Orders">
                                </div>
                                <div class="event-one__content">
                                    <h3 class="event-one__title"><a href="{{ route('product_page') }}">Delivery Orders</a>
                                    </h3>
                                    <ul class="event-one__meta list-unstyled">
                                        <li>
                                            <div class="icon">
                                                <span class="icon-calendar"></span>
                                            </div>
                                            <p>Lunch</p>
                                        </li>
                                        <li>
                                            <div class="icon">
                                                <span class="icon-pin"></span>
                                            </div>
                                            <p>Accra</p>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="event-one__single event-one__single-3 wow fadeInUp" data-wow-delay="300ms">
                            <div class="event-one__img-box">
                                <div class="event-one__img">
                                    <img src="{{ $orderModeImages->get(8)?->image_url ?: asset('themes/zabaga/assets/images/resources/event-1-3.jpg') }}" alt="Office Restock">
                                </div>
                                <div class="event-one__content">
                                    <h3 class="event-one__title"><a href="{{ route('contact_page') }}">Office Restock</a></h3>
                                    <ul class="event-one__meta list-unstyled">
                                        <li>
                                            <div class="icon">
                                                <span class="icon-calendar"></span>
                                            </div>
                                            <p>Weekdays</p>
                                        </li>
                                        <li>
                                            <div class="icon">
                                                <span class="icon-pin"></span>
                                            </div>
                                            <p>Bulk orders</p>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-6">
                        <div class="event-one__single event-one__single-4 wow fadeInUp" data-wow-delay="400ms">
                            <div class="event-one__img-box">
                                <div class="event-one__img">
                                    <img src="{{ $orderModeImages->get(9)?->image_url ?: asset('themes/zabaga/assets/images/resources/event-1-4.jpg') }}" alt="Family Packs">
                                </div>
                                <div class="event-one__content">
                                    <h3 class="event-one__title"><a href="{{ route('product_page') }}">Family Packs</a>
                                    </h3>
                                    <ul class="event-one__meta list-unstyled">
                                        <li>
                                            <div class="icon">
                                                <span class="icon-calendar"></span>
                                            </div>
                                            <p>Evening</p>
                                        </li>
                                        <li>
                                            <div class="icon">
                                                <span class="icon-pin"></span>
                                            </div>
                                            <p>Takeaway</p>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="event-one__single event-one__single-5 wow fadeInUp" data-wow-delay="500ms">
                            <div class="event-one__img-box">
                                <div class="event-one__img">
                                    <img src="{{ $orderModeImages->get(10)?->image_url ?: asset('themes/zabaga/assets/images/resources/event-1-5.jpg') }}" alt="Bulk Supplies">
                                </div>
                                <div class="event-one__content">
                                    <h3 class="event-one__title"><a href="{{ route('contact_page') }}">Bulk Supplies</a>
                                    </h3>
                                    <ul class="event-one__meta list-unstyled">
                                        <li>
                                            <div class="icon">
                                                <span class="icon-calendar"></span>
                                            </div>
                                            <p>Preorder</p>
                                        </li>
                                        <li>
                                            <div class="icon">
                                                <span class="icon-pin"></span>
                                            </div>
                                            <p>Call ONJECASA</p>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--Event One End -->

        <!--Why Choose One Start-->
        <section class="why-choose-one">
            <div class="container">
                <div class="section-title text-center sec-title-animation animation-style1">
                    <div class="section-title__tagline-box">
                        <span class="section-title__tagline">WHY CHOOSE ONJECASA</span>
                    </div>
                    <h2 class="section-title__title title-animation">Built For Supermarket Stock,<br> <span> Fast
                            Ordering</span>
                    </h2>
                </div>
                <ul class="row list-unstyled">
                    <!--Why Choose One Single Start-->
                    <li class="col-xl-6 col-lg-6 wow fadeInLeft" data-wow-delay="100ms">
                        <div class="why-choose-one__single">
                            <div class="why-choose-one__single-bg" style="background-image: url(/themes/zabaga/assets/images/backgrounds/why-choose-one-single-bg-1.jpg);">
                            </div>
                            <div class="why-choose-one__icon-inner">
                                <div class="why-choose-one__icon">
                                    <span class="icon-helping-hand"></span>
                                </div>
                            </div>
                            <div class="why-choose-one__content">
                                <h3 class="why-choose-one__title">Fresh food with a bold menu</h3>
                                <p class="why-choose-one__text">Groceries, fresh produce, household goods, and add-ons are managed from the same database that powers the shop and POS.</p>
                            </div>
                        </div>
                    </li>
                    <!--Why Choose One Single End-->
                    <!--Why Choose One Single Start-->
                    <li class="col-xl-6 col-lg-6 wow fadeInRight" data-wow-delay="200ms">
                        <div class="why-choose-one__single">
                            <div class="why-choose-one__single-bg" style="background-image: url(/themes/zabaga/assets/images/backgrounds/why-choose-one-single-bg-2.jpg);">
                            </div>
                            <div class="why-choose-one__icon-inner">
                                <div class="why-choose-one__icon">
                                    <span class="icon-fund-raising"></span>
                                </div>
                            </div>
                            <div class="why-choose-one__content">
                                <h3 class="why-choose-one__title">Admin-controlled homepage photos</h3>
                                <p class="why-choose-one__text">Superadmin and website admin users can update the homepage hero images, headers, and media from the existing homepage editor.</p>
                            </div>
                        </div>
                    </li>
                    <!--Why Choose One Single End-->
                    <!--Why Choose One Single Start-->
                    <li class="col-xl-6 col-lg-6 wow fadeInLeft" data-wow-delay="300ms">
                        <div class="why-choose-one__single">
                            <div class="why-choose-one__single-bg" style="background-image: url(/themes/zabaga/assets/images/backgrounds/why-choose-one-single-bg-3.jpg);">
                            </div>
                            <div class="why-choose-one__icon-inner">
                                <div class="why-choose-one__icon">
                                    <span class="icon-charity"></span>
                                </div>
                            </div>
                            <div class="why-choose-one__content">
                                <h3 class="why-choose-one__title">Cart, checkout, and order history</h3>
                                <p class="why-choose-one__text">Customers can browse live stock, add products to cart, checkout, and return to track their orders.</p>
                            </div>
                        </div>
                    </li>
                    <!--Why Choose One Single End-->
                    <!--Why Choose One Single Start-->
                    <li class="col-xl-6 col-lg-6 wow fadeInRight" data-wow-delay="400ms">
                        <div class="why-choose-one__single">
                            <div class="why-choose-one__single-bg" style="background-image: url(/themes/zabaga/assets/images/backgrounds/why-choose-one-single-bg-4.jpg);">
                            </div>
                            <div class="why-choose-one__icon-inner">
                                <div class="why-choose-one__icon">
                                    <span class="icon-protection"></span>
                                </div>
                            </div>
                            <div class="why-choose-one__content">
                                <h3 class="why-choose-one__title">Branch and POS connected</h3>
                                <p class="why-choose-one__text">Products, categories, stock, orders, receipts, and branch users stay connected through the backend.</p>
                            </div>
                        </div>
                    </li>
                    <!--Why Choose One Single End-->
                </ul>
            </div>
        </section>
        <!--Why Choose One End-->

        <!--Brand One Start-->
        <section class="brand-one">
            <div class="container">
                <div class="brand-one__carousel owl-theme owl-carousel">
                    <!--Brand One Single Start-->
                    <div class="item">
                        <div class="brand-one__single">
                            <div class="brand-one__img">
                                <img src="/themes/zabaga/assets/images/brand/brand-1-1.png" alt="">
                            </div>
                        </div>
                    </div>
                    <!--Brand One Single End-->
                    <!--Brand One Single Start-->
                    <div class="item">
                        <div class="brand-one__single">
                            <div class="brand-one__img">
                                <img src="/themes/zabaga/assets/images/brand/brand-1-1.png" alt="">
                            </div>
                        </div>
                    </div>
                    <!--Brand One Single End-->
                    <!--Brand One Single Start-->
                    <div class="item">
                        <div class="brand-one__single">
                            <div class="brand-one__img">
                                <img src="/themes/zabaga/assets/images/brand/brand-1-1.png" alt="">
                            </div>
                        </div>
                    </div>
                    <!--Brand One Single End-->
                    <!--Brand One Single Start-->
                    <div class="item">
                        <div class="brand-one__single">
                            <div class="brand-one__img">
                                <img src="/themes/zabaga/assets/images/brand/brand-1-1.png" alt="">
                            </div>
                        </div>
                    </div>
                    <!--Brand One Single End-->
                    <!--Brand One Single Start-->
                    <div class="item">
                        <div class="brand-one__single">
                            <div class="brand-one__img">
                                <img src="/themes/zabaga/assets/images/brand/brand-1-1.png" alt="">
                            </div>
                        </div>
                    </div>
                    <!--Brand One Single End-->
                </div>
            </div>
        </section>
        <!--Brand One End-->
        <!--Sliding Text Start-->
        <section class="sliding-text">
            <div class="sliding-text__inner">
                <div class="sliding-text__bg" style="background-image: url(/themes/zabaga/assets/images/backgrounds/sliding-text-bg.jpg);"></div>
                <ul class="sliding-text__list marquee_mode-1 list-unstyled">
                    <li>
                        <div class="icon">
                            <span class="icon-charity"></span>
                        </div>
                        <p>ONJECASA Supermarket</p>
                    </li>
                    <li>
                        <div class="icon">
                            <span class="icon-education"></span>
                        </div>
                        <p>Groceries, Beverages & Household Essentials</p>
                    </li>
                    <li>
                        <div class="icon">
                            <span class="icon-healthcare"></span>
                        </div>
                        <p>Barcode POS, Cashier Sales & Receipts</p>
                    </li>
                    <li>
                        <div class="icon">
                            <span class="icon-protection"></span>
                        </div>
                        <p>Online Orders, Pickup & Delivery</p>
                    </li>
                </ul>
            </div>
        </section>
        <!--Sliding Text End-->

        <!--Site Footer Two Start-->
        <footer class="site-footer-two site-footer-three">
            <div class="site-footer-two__top">
                <div class="site-footer-two__bg"
                    style="background-image: url({{ $footerBgImageUrl }});"></div>
                <div class="site-footer-two__shape-1 float-bob-y">
                    <img src="/themes/zabaga/assets/images/shapes/site-footer-two-shape-1.png" alt="">
                </div>
                <div class="container">
                    <div class="site-footer-two__top-inner">
                        <div class="row">
                            <div class="col-xl-4 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay="100ms">
                                <div class="footer-widget-two__about">
                                    <div class="footer-widget-two__about-logo">
                                        <a href="{{ route('home_page') }}"><img src="{{ $brandLogoUrl }}" alt="{{ $brandName }}"></a>
                                    </div>
                                    <p class="footer-widget-two__about-text">Order fresh ONJECASA products online, manage your cart, checkout, and return for order history.</p>
                                    <form class="footer-widget-two__form" method="GET" action="{{ route('product_page') }}">
                                        <div class="footer-widget-two__input">
                                            <input type="search" name="search" placeholder="Search products">
                                        </div>
                                        <button type="submit" class="footer-widget-two__btn"><i
                                                class="icon-right-arrow"></i></button>
                                    </form>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay="200ms">
                                <div class="footer-widget-two__services">
                                    <h4 class="footer-widget-two__title">Departments</h4>
                                    <ul class="footer-widget-two__services-list list-unstyled">
                                        <li><a href="{{ route('product_page') }}">Groceries</a></li>
                                        <li><a href="{{ route('product_page') }}">Beverages</a></li>
                                        <li><a href="{{ route('product_page') }}">Household</a></li>
                                        <li><a href="{{ route('product_page') }}">Fresh Produce</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay="300ms">
                                <div class="footer-widget-two__links">
                                    <h4 class="footer-widget-two__title">Links</h4>
                                    <ul class="footer-widget-two__services-list list-unstyled">
                                        <li><a href="{{ route('product_page') }}">All Products</a></li>
                                        <li><a href="{{ route('view_cart') }}">Cart</a></li>
                                        <li><a href="{{ route('checkout') }}">Checkout</a></li>
                                        <li><a href="{{ route('contact_page') }}">Contact</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay="400ms">
                                <div class="footer-widget-two__contact">
                                    <h3 class="footer-widget-two__title">Contact Info</h3>
                                    <ul class="footer-widget-two__contact-list list-unstyled">
                                        <li>
                                            <div class="icon">
                                                <span class="icon-call"></span>
                                            </div>
                                            <p><a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a></p>
                                        </li>
                                        <li>
                                            <div class="icon">
                                                <span class="icon-email"></span>
                                            </div>
                                            <p><a href="mailto:{{ $email }}">{{ $email }}</a></p>
                                        </li>
                                        <li>
                                            <div class="icon">
                                                <span class="icon-pin"></span>
                                            </div>
                                            <p>{{ $address }}</p>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="site-footer-two__bottom">
                    <div class="container">
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="site-footer-two__bottom-inner">
                                    <div class="site-footer-two__copyright">
                                        <p class="site-footer-two__copyright-text">Ã‚&copy; {{ date('Y') }} {{ $brandName }}. All Rights Reserved.</p>
                                    </div>
                                    <div class="site-footer-two__bottom-menu-box">
                                        <ul class="list-unstyled site-footer-two__bottom-menu">
                                            <li><a href="{{ route('about_page') }}">Privacy Policy</a></li>
                                            <li><a href="{{ route('about_page') }}">Terms of Service</a></li>
                                            <li><a href="{{ route('contact_page') }}">Contact Settings</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!--Site Footer Two End-->




    </div><!-- /.page-wrapper -->
        

            <div class="mobile-nav__wrapper">
        <div class="mobile-nav__overlay mobile-nav__toggler"></div>
        <!-- /.mobile-nav__overlay -->
        <div class="mobile-nav__content">
            <span class="mobile-nav__close mobile-nav__toggler"><i class="fa fa-times"></i></span>

            <div class="logo-box">
                <a href="{{ route('home_page') }}" aria-label="ONJECASA logo"><img src="{{ $brandLogoUrl }}" width="140"
                        alt="{{ $brandName }}" /></a>
            </div>
            <!-- /.logo-box -->
            <div class="mobile-nav__container"></div>
            <!-- /.mobile-nav__container -->

            <ul class="mobile-nav__contact list-unstyled">
                <li>
                    <i class="fa fa-envelope"></i>
                    <a href="mailto:{{ $email }}">{{ $email }}</a>
                </li>
                <li>
                    <i class="fas fa-phone"></i>
                    <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a>
                </li>
            </ul><!-- /.mobile-nav__contact -->
            <div class="mobile-nav__top">
                <div class="mobile-nav__social">
                    <a href="#" class="fab fa-twitter"></a>
                    <a href="#" class="fab fa-facebook-square"></a>
                    <a href="#" class="fab fa-pinterest-p"></a>
                    <a href="#" class="fab fa-instagram"></a>
                </div><!-- /.mobile-nav__social -->
            </div><!-- /.mobile-nav__top -->



        </div>
        <!-- /.mobile-nav__content -->
    </div>
    <!-- /.mobile-nav__wrapper -->            <!-- Search Popup -->
    <div class="search-popup">
        <div class="color-layer"></div>
        <button class="close-search"><span class="far fa-times fa-fw"></span></button>
        <form method="GET" action="{{ route('product_page') }}">
            <div class="form-group">
                <input type="search" name="search" value="" placeholder="Search products" required="">
                <button type="submit"><i class="fas fa-search"></i></button>
            </div>
        </form>
    </div>
    <!-- End Search Popup -->            <a href="#" data-target="html" class="scroll-to-target scroll-to-top">
        <span class="scroll-to-top__wrapper"><span class="scroll-to-top__inner"></span></span>
        <span class="scroll-to-top__text"> Go Back Top</span>
    </a>            <!-- Start sidebar widget content -->
    <div class="xs-sidebar-group info-group info-sidebar">
        <div class="xs-overlay xs-bg-black"></div>
        <div class="xs-sidebar-widget">
            <div class="sidebar-widget-container">
                <div class="widget-heading">
                    <a href="#" class="close-side-widget">X</a>
                </div>
                <div class="sidebar-textwidget">
                    <div class="sidebar-info-contents">
                        <div class="content-inner">
                            <div class="logo">
                                <a href="{{ route('home_page') }}"><img src="{{ $brandLogoUrl }}" alt="{{ $brandName }}" /></a>
                            </div>
                            <div class="content-box">
                                <h4>About Us</h4>
                                <div class="inner-text">
                                    <p>ONJECASA serves groceries, fresh produce, household goods, delivery orders, and bulk orders from a backend-managed catalog.
                                    </p>
                                </div>
                            </div>

                            <div class="form-inner">
                                <h4>Send ONJECASA a Message</h4>
                                <form action="{{ route('contact.submit') }}" method="POST" class="contact-form-validated">
                                    @csrf
                                    <div class="form-group">
                                        <input type="text" name="name" placeholder="Name" required>
                                    </div>
                                    <div class="form-group">
                                        <input type="email" name="email" placeholder="Email" required>
                                    </div>
                                    <div class="form-group">
                                        <textarea name="message" placeholder="Message..." required></textarea>
                                    </div>
                                    <div class="form-group message-btn">
                                        <button class="thm-btn" type="submit" data-loading-text="Please wait...">
                                            <span class="thm-btn-text">Submit Now</span>
                                            <span class="thm-btn-icon-box"><i class="fas fa-arrow-right"></i></span>
                                        </button>
                                    </div>
                                    <div class="result mt-2"></div>
                                </form>
                            </div>

                            <div class="sidebar-contact-info">
                                <h4>Contact Info</h4>
                                <ul class="list-unstyled">
                                    <li>
                                        <span class="icon-pin"></span> {{ $address }}
                                    </li>
                                    <li>
                                        <span class="icon-call"></span>
                                        <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a>
                                    </li>
                                    <li>
                                        <span class="icon-email"></span>
                                        <a href="mailto:{{ $email }}">{{ $email }}</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="thm-social-link1">
                                <ul class="social-box list-unstyled">
                                    <li>
                                        <a href="#"><i class="icon-facebook-app-symbol" aria-hidden="true"></i></a>
                                    </li>
                                    <li>
                                        <a href="#"><i class="icon-twitter" aria-hidden="true"></i></a>
                                    </li>
                                    <li>
                                        <a href="#"><i class="icon-instagram" aria-hidden="true"></i></a>
                                    </li>
                                    <li>
                                        <a href="#"><i class="icon-pinterest" aria-hidden="true"></i></a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End sidebar widget content -->            <script src="/themes/zabaga/assets/js/jquery-3.6.0.min.js"></script>
<script src="/themes/zabaga/assets/js/bootstrap.bundle.min.js"></script>
<script src="/themes/zabaga/assets/js/jarallax.min.js"></script>
<script src="/themes/zabaga/assets/js/jquery.ajaxchimp.min.js"></script>
<script src="/themes/zabaga/assets/js/jquery.appear.min.js"></script>
<script src="/themes/zabaga/assets/js/swiper.min.js"></script>
<script src="/themes/zabaga/assets/js/jquery.magnific-popup.min.js"></script>
<script src="/themes/zabaga/assets/js/jquery.validate.min.js"></script>
<script src="/themes/zabaga/assets/js/odometer.min.js"></script>
<script src="/themes/zabaga/assets/js/wNumb.min.js"></script>
<script src="/themes/zabaga/assets/js/wow.js"></script>
<script src="/themes/zabaga/assets/js/isotope.js"></script>
<script src="/themes/zabaga/assets/js/owl.carousel.min.js"></script>
<script src="/themes/zabaga/assets/js/jquery-ui.js"></script>
<script src="/themes/zabaga/assets/js/jquery.nice-select.min.js"></script>
<script src="/themes/zabaga/assets/js/marquee.min.js"></script>
<script src="/themes/zabaga/assets/js/countdown.min.js"></script>
<script src="/themes/zabaga/assets/js/jquery-sidebar-content.js"></script>
<script src="/themes/zabaga/assets/js/aos.js"></script>
<script src="/themes/zabaga/assets/js/vegas.min.js"></script>
<script src="/themes/zabaga/assets/js/gsap/gsap.js"></script>
<script src="/themes/zabaga/assets/js/gsap/ScrollTrigger.js"></script>
<script src="/themes/zabaga/assets/js/gsap/SplitText.js"></script>
<script src="/themes/zabaga/assets/js/script.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const slides = Array.from(document.querySelectorAll('[data-hero-slide]'));
        const heroTrack = document.querySelector('.banner-one__slider.has-video');
        const slideData = @json($heroMediaSlides);
        const title = document.querySelector('[data-hero-title]');
        const subtitle = document.querySelector('[data-hero-subtitle]');
        const popup = document.querySelector('.banner-one__video-link .video-popup');
        let activeIndex = 0;
        const smallScreen = window.matchMedia('(max-width: 767px)');

        if (slides.length < 2) {
            const onlyVideo = slides[0]?.querySelector('video');
            if (onlyVideo) {
                onlyVideo.play().catch(function () {});
            }
            return;
        }

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function activateHeroSlide(nextIndex) {
            activeIndex = (nextIndex + slides.length) % slides.length;

            if (heroTrack && !smallScreen.matches) {
                heroTrack.style.transform = '';
            }

            slides.forEach(function (slide, index) {
                const video = slide.querySelector('video');
                const isActive = index === activeIndex;
                slide.classList.toggle('is-active', isActive);

                if (smallScreen.matches) {
                    let offset = index - activeIndex;
                    if (offset < -1) {
                        offset += slides.length;
                    } else if (offset > 1) {
                        offset -= slides.length;
                    }
                    slide.style.transform = 'translate3d(' + (offset * 100) + '%, 0, 0)';
                } else {
                    slide.style.transform = '';
                }

                if (!video) {
                    return;
                }

                if (isActive) {
                    video.currentTime = 0;
                    video.play().catch(function () {});
                } else {
                    video.pause();
                }
            });

            const activeSlide = slideData[activeIndex] || {};
            if (subtitle && activeSlide.small_header) {
                subtitle.textContent = activeSlide.small_header;
            }
            if (title && activeSlide.main_header) {
                title.innerHTML = escapeHtml(activeSlide.main_header).replace(/\n/g, '<br>');
            }
            if (popup) {
                popup.setAttribute('href', activeSlide.video_url || activeSlide.image_url || '{{ route('product_page') }}');
            }
        }

        activateHeroSlide(0);
        smallScreen.addEventListener('change', function () {
            activateHeroSlide(activeIndex);
        });
        window.setInterval(function () {
            activateHeroSlide(activeIndex + 1);
        }, 9000);
    });

    document.addEventListener('scroll', function () {
        const heroTrack = document.querySelector('.banner-one__slider.has-video');
        if (!heroTrack || window.matchMedia('(max-width: 767px)').matches) {
            return;
        }

        const offset = Math.min(34, window.scrollY * .08);
        heroTrack.style.setProperty('--hero-parallax-y', offset + 'px');
    }, { passive: true });

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.onjecasa-menu-video').forEach(function (video) {
            video.muted = true;
            video.defaultMuted = true;
            video.loop = true;
            video.playsInline = true;
            video.removeAttribute('controls');
            video.play().catch(function () {});
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const smallScreen = window.matchMedia('(max-width: 767px)');
        const selectors = [
            '.feature-one .row',
            '.causes-three .row',
            '.team-three .row',
            '.why-choose-one .row'
        ];

        selectors
            .flatMap(function (selector) {
                return Array.from(document.querySelectorAll(selector));
            })
            .filter(function (rail) {
                return smallScreen.matches && rail.scrollWidth > rail.clientWidth + 40;
            })
            .forEach(function (rail) {
                rail.classList.add('onjecasa-mobile-rail');
                let paused = false;

                ['pointerdown', 'touchstart', 'wheel'].forEach(function (eventName) {
                    rail.addEventListener(eventName, function () {
                        paused = true;
                        window.setTimeout(function () {
                            paused = false;
                        }, 7000);
                    }, { passive: true });
                });

                window.setInterval(function () {
                    if (paused || !smallScreen.matches || rail.matches(':hover')) {
                        return;
                    }

                    const nextLeft = rail.scrollLeft + Math.min(rail.clientWidth * .82, 340);
                    rail.scrollTo({
                        left: nextLeft >= rail.scrollWidth - rail.clientWidth - 8 ? 0 : nextLeft,
                        behavior: 'smooth'
                    });
                }, 5200);
            });
    });
</script>



    </body>


<!-- Mirrored from helpest.laravel.scriptfusions.com/index3 by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 08 Dec 2025 12:49:45 GMT -->
</html>




