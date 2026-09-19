<nav class="navbar navbar-expand-lg userpanel-navbar fixed-top">
    <div class="container userpanel-navbar-inner">
        <a class="navbar-brand userpanel-brand" href="{{ route('home_page') }}">
            <img src="{{ \App\Support\BrandAssets::logoUrl() }}" alt="{{ \App\Support\BrandAssets::systemName() }}" style="width:36px;height:36px;border-radius:8px;margin-right:8px;display:inline-block;vertical-align:middle;">
            <strong><span>{{ \App\Support\BrandAssets::systemName() }}</span></strong>
        </a>

        @php
            $cartCount = Auth::check() ? \App\Models\Cart::where('user_id', Auth::id())->count() : 0;
        @endphp

        <div class="userpanel-nav-track">
            <ul class="navbar-nav flex-row flex-nowrap userpanel-nav-list">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home_page') ? 'active' : '' }}" href="{{ route('home_page') }}">Home</a>
                </li>
                <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('product_page') ? 'active' : '' }}" href="{{ route('product_page') }}">Shop Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('faqs_page') ? 'active' : '' }}" href="{{ route('faqs_page') }}">FAQs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('contact_page') ? 'active' : '' }}" href="{{ route('contact_page') }}">Contact</a>
                </li>

                @if(Auth::check())
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('order.history') ? 'active' : '' }}" href="{{ route('order.history') }}">My Orders</a>
                    </li>
                @endif
            </ul>
        </div>

        <div class="userpanel-actions">
            @if(Auth::check() && in_array((int) Auth::user()->is_admin, [1, 2], true))
                <a href="{{ route('admin.panel') }}" class="userpanel-chip">Admin Panel</a>
            @endif

            <a href="{{ route('view_cart') }}" class="bi-bag userpanel-icon position-relative">
                @if($cartCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ $cartCount }}
                        <span class="visually-hidden">cart items</span>
                    </span>
                @endif
            </a>

            @if(Auth::check())
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="userpanel-logout">Logout</button>
                </form>
            @endif
        </div>
    </div>
</nav>




