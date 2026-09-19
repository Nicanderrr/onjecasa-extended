<header class="main-header-three">
    @php($brandLogoUrl = $brandLogoUrl ?? \App\Support\BrandAssets::logoUrl())
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
