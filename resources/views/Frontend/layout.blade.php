<!doctype html>
<html lang="en">
@php
    $posSettings = [];
    if (\Illuminate\Support\Facades\Schema::hasTable('pos_settings')) {
        $posSettings = \Illuminate\Support\Facades\DB::table('pos_settings')->pluck('value', 'key')->all();
    }

    $brandName = \App\Support\BrandAssets::systemName();
    $globalLogoUrl = \App\Support\BrandAssets::logoUrl();
    $faviconUrl = \App\Support\BrandAssets::faviconUrl();
    $contactSettings = \Illuminate\Support\Facades\Schema::hasTable('contact_settings')
        ? \App\Models\ContactSetting::first()
        : null;
    $phone = $contactSettings?->business_number ?: ($posSettings['business_phone'] ?? '+233 57 833 9542');
    $email = $contactSettings?->form_email ?: 'orders@onjecasa.test';
    $address = $contactSettings?->office_address ?: ($posSettings['business_address'] ?? 'Nii Okaiman West Main Road, Greater Accra');
    $cartCount = Auth::check() ? App\Models\Cart::where('user_id', Auth::id())->sum('quantity') : 0;
    $currentRoute = request()->route()?->getName();
    $pageBannerSlide = \Illuminate\Support\Facades\Schema::hasTable('home_slides')
        ? \App\Models\HomeSlide::find(12)
        : null;
    $footerBgSlide = \Illuminate\Support\Facades\Schema::hasTable('home_slides')
        ? \App\Models\HomeSlide::find(13)
        : null;
    $pageBannerUrl = $pageBannerSlide?->image_url ?: asset('themes/zabaga/assets/images/backgrounds/page-header-bg.jpg');
    $footerBgUrl = $footerBgSlide?->image_url ?: asset('themes/zabaga/assets/images/backgrounds/site-footer-two-bg.jpg');
@endphp
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', 'ONJECASA - groceries, fresh produce, beverages, household goods, personal care, and bulk deals in Accra')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ONJECASA')</title>
    <link rel="icon" href="{{ $faviconUrl }}">
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900;1000&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@700&display=swap" rel="stylesheet">
    <link href="{{ asset('themes/zabaga/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('themes/zabaga/assets/css/animate.min.css') }}" rel="stylesheet">
    <link href="{{ asset('themes/zabaga/assets/css/font-awesome-all.css') }}" rel="stylesheet">
    <link href="{{ asset('themes/zabaga/assets/css/flaticon.css') }}" rel="stylesheet">
    <link href="{{ asset('themes/zabaga/assets/css/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ asset('themes/zabaga/assets/css/module-css/footer.css') }}" rel="stylesheet">
    <link href="{{ asset('themes/zabaga/assets/css/module-css/contact.css') }}" rel="stylesheet">
    <link href="{{ asset('themes/zabaga/assets/css/module-css/blog.css') }}" rel="stylesheet">
    <link href="{{ asset('themes/zabaga/assets/css/module-css/shop.css') }}" rel="stylesheet">
    <link href="{{ asset('themes/zabaga/assets/css/style.css') }}?v={{ filemtime(public_path('themes/zabaga/assets/css/style.css')) }}" rel="stylesheet">
    <link href="{{ asset('themes/zabaga/assets/css/responsive.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('css/storenav-redesign.css') }}?v={{ filemtime(public_path('css/storenav-redesign.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/onjecasa-navbar.css') }}?v={{ filemtime(public_path('css/onjecasa-navbar.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/onjecasa-zabaga-frontend.css') }}?v={{ filemtime(public_path('css/onjecasa-zabaga-frontend.css')) }}" rel="stylesheet">
    @stack('styles')
    <style>
        .onjecasa-template-page .page-header__bg {
            background-image: var(--onjecasa-page-banner-image) !important;
        }
    </style>
</head>
<body class="custom-cursor storefront-redesign onjecasa-template-page" style="--onjecasa-page-banner-image: url('{{ $pageBannerUrl }}'); --onjecasa-footer-bg-image: url('{{ $footerBgUrl }}');">
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    @include('Frontend.partials.navbar')

    <main class="onjecasa-template-main">
        @hasSection('full_width_content')
            @if(session('message') || session('success') || session('error') || session('status'))
                <div class="container"><div class="flash-message">{{ session('message') ?? session('success') ?? session('error') ?? session('status') }}</div></div>
            @endif
            @yield('full_width_content')
        @else
        <div class="container">
            @if(session('message') || session('success') || session('error') || session('status'))
                <div class="flash-message">{{ session('message') ?? session('success') ?? session('error') ?? session('status') }}</div>
            @endif
            @yield('content')
        </div>
        @endif
    </main>

    <footer class="site-footer-two site-footer-three onjecasa-template-footer">
        <div class="site-footer-two__top">
            <div class="site-footer-two__bg" style="background-image: var(--onjecasa-footer-bg-image);"></div>
            <div class="container">
                <div class="site-footer-two__top-inner">
                    <div class="row">
                        <div class="col-xl-4 col-lg-6 col-md-6">
                            <div class="footer-widget-two__about">
                                <div class="footer-widget-two__about-logo">
                                    <a href="{{ route('home_page') }}"><img src="{{ $globalLogoUrl }}" alt="{{ $brandName }}"></a>
                                </div>
                                <p class="footer-widget-two__about-text">Groceries, fresh produce, household goods, and everyday essentials for pickup, delivery, and bulk orders across Accra.</p>
                                <form class="footer-widget-two__form" method="GET" action="{{ route('product_page') }}">
                                    <div class="footer-widget-two__input"><input type="search" name="search" placeholder="Search products"></div>
                                    <button type="submit" class="footer-widget-two__btn"><i class="icon-right-arrow"></i></button>
                                </form>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-6 col-md-6">
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
                        <div class="col-xl-2 col-lg-6 col-md-6">
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
                        <div class="col-xl-3 col-lg-6 col-md-6">
                            <div class="footer-widget-two__contact">
                                <h3 class="footer-widget-two__title">Contact Info</h3>
                                <ul class="footer-widget-two__contact-list list-unstyled">
                                    <li><div class="icon"><span class="icon-call"></span></div><p><a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a></p></li>
                                    <li><div class="icon"><span class="icon-email"></span></div><p><a href="mailto:{{ $email }}">{{ $email }}</a></p></li>
                                    <li><div class="icon"><span class="icon-pin"></span></div><p>{{ $address }}</p></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="site-footer-two__bottom">
                <div class="container">
                    <div class="site-footer-two__bottom-inner">
                        <div class="site-footer-two__copyright">
                            <p class="site-footer-two__copyright-text">ï¿½&copy; {{ date('Y') }} {{ $brandName }}. All Rights Reserved.</p>
                        </div>
                        <div class="site-footer-two__bottom-menu-box">
                            <ul class="list-unstyled site-footer-two__bottom-menu">
                                <li><a href="{{ route('about_page') }}">Privacy Policy</a></li>
                                <li><a href="{{ route('about_page') }}">Terms of Service</a></li>
                                <li><a href="{{ route('contact_page') }}">Contact</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</div>

<div class="mobile-nav__wrapper">
    <div class="mobile-nav__overlay mobile-nav__toggler"></div>
    <div class="mobile-nav__content">
        <span class="mobile-nav__close mobile-nav__toggler"><i class="fa fa-times"></i></span>
        <div class="logo-box">
            <a href="{{ route('home_page') }}"><img src="{{ $globalLogoUrl }}" width="140" alt="{{ $brandName }}"></a>
        </div>
        <div class="mobile-nav__container"></div>
        <ul class="mobile-nav__contact list-unstyled">
            <li><i class="fa fa-envelope"></i><a href="mailto:{{ $email }}">{{ $email }}</a></li>
            <li><i class="fas fa-phone"></i><a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a></li>
        </ul>
        <div class="mobile-nav__top">
            <div class="mobile-nav__social">
                <a href="#" class="fab fa-twitter"></a>
                <a href="#" class="fab fa-facebook-square"></a>
                <a href="#" class="fab fa-pinterest-p"></a>
                <a href="#" class="fab fa-instagram"></a>
            </div>
        </div>
    </div>
</div>

<div class="search-popup">
    <div class="color-layer"></div>
    <button class="close-search"><span class="far fa-times fa-fw"></span></button>
    <form method="GET" action="{{ route('product_page') }}">
        <div class="form-group">
            <input type="search" name="search" value="" placeholder="Search products" required>
            <button type="submit"><i class="fas fa-search"></i></button>
        </div>
    </form>
</div>

<a href="#" data-target="html" class="scroll-to-target scroll-to-top">
    <span class="scroll-to-top__wrapper"><span class="scroll-to-top__inner"></span></span>
    <span class="scroll-to-top__text"> Go Back Top</span>
</a>

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
                            <a href="{{ route('home_page') }}"><img src="{{ $globalLogoUrl }}" alt="{{ $brandName }}"></a>
                        </div>
                        <div class="content-box">
                            <h4>About Us</h4>
                            <div class="inner-text">
                                <p>ONJECASA serves groceries, fresh produce, household goods, add-ons, delivery orders, and bulk orders from a backend-managed catalog.</p>
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
                                <li><span class="icon-pin"></span> {{ $address }}</li>
                                <li><span class="icon-call"></span> <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a></li>
                                <li><span class="icon-email"></span> <a href="mailto:{{ $email }}">{{ $email }}</a></li>
                            </ul>
                        </div>
                        <div class="thm-social-link1">
                            <ul class="social-box list-unstyled">
                                <li><a href="#"><i class="icon-facebook-app-symbol" aria-hidden="true"></i></a></li>
                                <li><a href="#"><i class="icon-twitter" aria-hidden="true"></i></a></li>
                                <li><a href="#"><i class="icon-instagram" aria-hidden="true"></i></a></li>
                                <li><a href="#"><i class="icon-pinterest" aria-hidden="true"></i></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('themes/zabaga/assets/js/jquery-3.6.0.min.js') }}"></script>
<script src="{{ asset('themes/zabaga/assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('themes/zabaga/assets/js/jquery-ui.js') }}"></script>
<script src="{{ asset('themes/zabaga/assets/js/jquery.magnific-popup.min.js') }}"></script>
<script src="{{ asset('themes/zabaga/assets/js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('themes/zabaga/assets/js/swiper.min.js') }}"></script>
<script src="{{ asset('themes/zabaga/assets/js/jquery-sidebar-content.js') }}"></script>
<script src="{{ asset('themes/zabaga/assets/js/wow.js') }}"></script>
<script src="{{ asset('themes/zabaga/assets/js/script.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const smallScreen = window.matchMedia('(max-width: 767px)');
        const selectors = [
            '.causes-three .row',
            '.team-three .row',
            '.event-one .row',
            '.why-choose-one .row',
            '.orders-summary',
            '.checkout-layout',
            '.cart-layout',
            '.meal-extra-grid'
        ];

        function rails() {
            if (!smallScreen.matches) {
                return [];
            }

            return selectors
                .flatMap(function (selector) {
                    return Array.from(document.querySelectorAll(selector));
                })
                .filter(function (rail) {
                    return rail.scrollWidth > rail.clientWidth + 40;
                });
        }

        rails().forEach(function (rail) {
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
@stack('scripts')
</body>
</html>



