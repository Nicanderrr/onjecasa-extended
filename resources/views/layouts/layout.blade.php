
<!doctype html>
<html lang="en">
@php
    $globalLogoUrl = \App\Support\BrandAssets::logoUrl();
    $systemName = \App\Support\BrandAssets::systemName();
@endphp
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'ONJECASA')</title>

    <!-- CSS FILES -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;300;400;700;900&display=swap" rel="stylesheet">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-icons.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/slick.css') }}"/>
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link rel="icon" href="{{ $globalLogoUrl }}" />

</head>
<body>

    <section class="preloader">
        <div class="spinner">
            <span class="sk-inner-circle"></span>
        </div>
    </section>

    <main>
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <a class="navbar-brand" href="{{ route('home_page') }}">
                    <img src="{{ $globalLogoUrl }}" alt="{{ $systemName }}" style="width:34px;height:34px;object-fit:cover;border-radius:6px;display:inline-block;margin-right:8px;">
                    <strong><span>{{ $systemName }}</span></strong>
                </a>

                <div class="d-lg-none">
                    @php
                    $cartCount = Auth::check() ? App\Models\Cart::where('user_id', Auth::id())->count() : 0;
                    @endphp

                    <a href="{{ route('view_cart') }}" class="bi-bag custom-icon position-relative">
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ $cartCount }}
                        <span class="visually-hidden">cart items</span>
                    </span>
                    </a>
                </div>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav mx-auto">
                        <li class="nav-item">
                            <a class="nav-link " href="{{route('home_page')}}">Home</a>
                        </li>

                        {{-- <li class="nav-item">
                            <a class="nav-link" href="{{route('about_page')}}">About</a>
                        </li> --}}

                        <li class="nav-item">
                            <a class="nav-link active" href="{{route('product_page')}}">Shop Products</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{route('faqs_page')}}">FAQs</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{route('contact_page')}}">Contact</a>
                        </li>
                    </ul>

                    <div class="d-none d-lg-block">
                            @php
                            $cartCount = Auth::check() ? App\Models\Cart::where('user_id', Auth::id())->count() : 0;
                            @endphp
    
                            <a href="{{ route('view_cart') }}" class="bi-bag custom-icon position-relative">
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ $cartCount }}
                                <span class="visually-hidden">cart items</span>
                            </span>
                            </a>
                    </div>
                </div>
            </div>
        </nav>
        <br><br><hr>

        
        @yield('content')
    </main>

    {{-- @include('frontend.partials.footer') <!-- Include Footer --> --}}

    <!-- CART MODAL -->
    {{-- @include('frontend.partials.cart_modal') <!-- Include Cart Modal --> --}}

    <!-- JAVASCRIPT FILES -->
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/Headroom.js') }}"></script>
    <script src="{{ asset('js/jQuery.headroom.js') }}"></script>
    <script src="{{ asset('js/slick.min.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>

    @stack('scripts') <!-- Additional Scripts -->
</body>
</html>



