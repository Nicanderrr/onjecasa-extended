@extends('Frontend.layout')

@section('title', 'About - ONJECASA')
@section('meta_description', 'Learn about ONJECASA, serving groceries, fresh produce, household goods, pickup, delivery, and bulk orders in Accra.')

@push('styles')
    <link href="{{ asset('themes/zabaga/assets/css/module-css/page-header.css') }}" rel="stylesheet">
    <link href="{{ asset('themes/zabaga/assets/css/module-css/about.css') }}" rel="stylesheet">
    <style>
        .onjecasa-template-main {
            padding: 0;
            background: #fff;
        }

        .onjecasa-about-page .page-header {
            padding: 166px 0 154px;
            -webkit-mask-size: 100% 100%;
            mask-size: 100% 100%;
        }

        .onjecasa-about-page .page-header__shape-1 img,
        .onjecasa-about-page .page-header__shape-2 img {
            transform: scale(.58);
        }

        .onjecasa-about-page .page-header__shape-1 img {
            transform-origin: right bottom;
        }

        .onjecasa-about-page .page-header__shape-2 img {
            transform-origin: left bottom;
        }

        .onjecasa-about-story {
            position: relative;
            padding: 110px 0 95px;
            overflow: hidden;
            background: #fff;
        }

        .onjecasa-about-story__images {
            position: relative;
            min-height: 620px;
            padding-right: 68px;
        }

        .onjecasa-about-story__main {
            position: relative;
            width: 80%;
            height: 520px;
            overflow: hidden;
            border-radius: 0 72px 0 72px;
            background: #fef3c7;
            box-shadow: 20px 20px 0 var(--helpest-primary);
        }

        .onjecasa-about-story__main img,
        .onjecasa-about-story__small img,
        .onjecasa-about-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .onjecasa-about-story__small {
            position: absolute;
            right: 0;
            bottom: 22px;
            width: 46%;
            height: 280px;
            overflow: hidden;
            border: 12px solid #fff;
            border-radius: 62px 0 62px 0;
            background: #fef3c7;
            box-shadow: 0 18px 55px rgba(var(--helpest-black-rgb), .18);
        }

        .onjecasa-about-story__badge {
            position: absolute;
            left: 32px;
            bottom: 28px;
            width: 132px;
            height: 132px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: var(--helpest-base);
            color: #fff;
            font-weight: 900;
            line-height: 1.15;
            text-align: center;
            text-transform: uppercase;
        }

        .onjecasa-about-story__shape {
            position: absolute;
            left: -24px;
            top: 38px;
            z-index: 1;
            pointer-events: none;
        }

        .onjecasa-about-story__text {
            margin: 0 0 26px;
            color: var(--helpest-gray);
            font-weight: 700;
            line-height: 1.85;
        }

        .onjecasa-about-points {
            display: grid;
            gap: 16px;
            margin: 0 0 34px;
            padding: 0;
            list-style: none;
        }

        .onjecasa-about-points li {
            display: flex;
            gap: 14px;
            align-items: flex-start;
            color: var(--helpest-black);
            font-weight: 900;
        }

        .onjecasa-about-points i {
            width: 30px;
            height: 30px;
            flex: 0 0 30px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: var(--helpest-primary);
            color: var(--helpest-black);
            font-size: 13px;
        }

        .onjecasa-about-actions {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            align-items: center;
        }

        .onjecasa-about-phone {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--helpest-black);
            font-size: 17px;
            font-weight: 900;
        }

        .onjecasa-about-phone span {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: var(--helpest-extra);
            color: var(--helpest-base);
        }

        .onjecasa-about-band {
            padding: 95px 0;
            background: var(--helpest-extra);
        }

        .onjecasa-about-service {
            height: 100%;
            padding: 32px 30px;
            border: 1px solid rgba(var(--helpest-black-rgb), .08);
            border-radius: 12px;
            background: #fff;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .onjecasa-about-service:hover {
            transform: translateY(-3px);
            box-shadow: 0 18px 45px rgba(var(--helpest-black-rgb), .10);
        }

        .onjecasa-about-service__icon {
            width: 64px;
            height: 64px;
            display: grid;
            place-items: center;
            margin-bottom: 20px;
            border-radius: 50%;
            background: var(--helpest-base);
            color: #fff;
            font-size: 26px;
        }

        .onjecasa-about-service h3 {
            margin-bottom: 10px;
            color: var(--helpest-black);
            font-size: 24px;
            font-weight: 900;
        }

        .onjecasa-about-service p {
            margin: 0;
            color: var(--helpest-gray);
            font-weight: 700;
            line-height: 1.7;
        }

        .onjecasa-about-banner {
            position: relative;
            min-height: 440px;
            overflow: hidden;
            background: var(--helpest-black);
        }

        .onjecasa-about-banner img {
            position: absolute;
            inset: 0;
            opacity: .45;
        }

        .onjecasa-about-banner__content {
            position: relative;
            z-index: 1;
            max-width: 780px;
            padding: 102px 0;
            color: #fff;
        }

        .onjecasa-about-banner__content .section-title__tagline {
            color: var(--helpest-primary);
        }

        .onjecasa-about-banner__content h2 {
            margin-bottom: 22px;
            color: #fff;
            font-size: 52px;
            line-height: 1.06;
            font-weight: 900;
        }

        .onjecasa-about-banner__content p {
            max-width: 680px;
            margin: 0;
            color: rgba(255, 255, 255, .84);
            font-size: 18px;
            line-height: 1.8;
            font-weight: 700;
        }

        .onjecasa-about-steps {
            padding: 100px 0;
            background: #fff;
        }

        .onjecasa-step {
            position: relative;
            min-height: 116px;
            padding: 28px 28px 28px 88px;
            border-left: 4px solid var(--helpest-base);
            background: #fff;
            box-shadow: 0 10px 35px rgba(var(--helpest-black-rgb), .07);
        }

        .onjecasa-step + .onjecasa-step {
            margin-top: 20px;
        }

        .onjecasa-step span {
            position: absolute;
            left: 24px;
            top: 28px;
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: var(--helpest-primary);
            color: var(--helpest-black);
            font-weight: 900;
        }

        .onjecasa-step h3 {
            margin-bottom: 6px;
            color: var(--helpest-black);
            font-size: 24px;
            font-weight: 900;
        }

        .onjecasa-step p {
            margin: 0;
            color: var(--helpest-gray);
            font-weight: 700;
            line-height: 1.7;
        }

        @media (max-width: 991px) {
            .onjecasa-about-story__images {
                margin-bottom: 48px;
            }

            .onjecasa-about-banner__content h2 {
                font-size: 40px;
            }
        }

        @media (max-width: 767px) {
            .onjecasa-about-page .page-header {
                padding: 120px 0 116px;
            }

            .onjecasa-about-story,
            .onjecasa-about-band,
            .onjecasa-about-steps {
                padding: 72px 0;
            }

            .onjecasa-about-story__images {
                min-height: auto;
                padding-right: 0;
            }

            .onjecasa-about-story__main {
                width: 100%;
                height: 360px;
                box-shadow: 10px 10px 0 var(--helpest-primary);
            }

            .onjecasa-about-story__small {
                position: relative;
                right: auto;
                bottom: auto;
                width: 76%;
                height: 190px;
                margin: -54px 0 0 auto;
            }

            .onjecasa-about-story__badge {
                left: 18px;
                bottom: 128px;
                width: 104px;
                height: 104px;
                font-size: 13px;
            }

            .onjecasa-about-story__shape {
                display: none;
            }

            .onjecasa-about-banner__content {
                padding: 74px 0;
            }

            .onjecasa-about-banner__content h2 {
                font-size: 34px;
            }
        }
    </style>
@endpush

@section('full_width_content')
@php
    $slides = $homeSlides ?? collect();
    $aboutImageOne = $slides->firstWhere('id', 4);
    $aboutImageTwo = $slides->firstWhere('id', 5);
    $aboutImageOneUrl = $aboutImageOne?->image_url ?: asset('themes/zabaga/assets/images/resources/about-three-img-1.jpg');
    $aboutImageTwoUrl = $aboutImageTwo?->image_url ?: asset('themes/zabaga/assets/images/resources/about-three-img-2.jpg');
@endphp

<div class="onjecasa-about-page">
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
                <h2>About ONJECASA</h2>
                <div class="thm-breadcrumb__box">
                    <ul class="thm-breadcrumb list-unstyled">
                        <li><a href="{{ route('home_page') }}">Home</a></li>
                        <li><span class="icon-right-arrow"></span></li>
                        <li>About</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="onjecasa-about-story">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-xl-6">
                    <div class="onjecasa-about-story__images wow slideInLeft" data-wow-delay="100ms" data-wow-duration="1800ms">
                        <div class="onjecasa-about-story__shape">
                            <img src="{{ asset('themes/zabaga/assets/images/shapes/about-three-shape-1.png') }}" alt="">
                        </div>
                        <div class="onjecasa-about-story__main">
                            <img src="{{ $aboutImageOneUrl }}" alt="ONJECASA">
                        </div>
                        <div class="onjecasa-about-story__small">
                            <img src="{{ $aboutImageTwoUrl }}" alt="ONJECASA supermarket products">
                        </div>
                        <div class="onjecasa-about-story__badge">Shop Well<br>Daily</div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="onjecasa-about-story__content wow fadeInRight" data-wow-delay="200ms">
                        <div class="section-title text-left sec-title-animation animation-style2">
                            <div class="section-title__tagline-box">
                                <span class="section-title__tagline">About Us</span>
                            </div>
                            <h2 class="section-title__title title-animation">Everyday Supermarket With <span>Fresh Stock Energy.</span></h2>
                        </div>
                        <p class="onjecasa-about-story__text">ONJECASA serves everyday essentials, groceries, beverages, fresh produce, household goods, pickup, delivery, and bulk orders from Accra.</p>
                        <ul class="onjecasa-about-points">
                            <li><i class="fa fa-check"></i><span>Groceries, beverages, fresh produce, personal care, household items, and checkout add-ons.</span></li>
                            <li><i class="fa fa-check"></i><span>Online catalog, cart, checkout, branch control, and POS ordering connected to the same backend.</span></li>
                            <li><i class="fa fa-check"></i><span>Built for walk-in sales, quick pickup, delivery orders, office supplies, and bulk orders.</span></li>
                        </ul>
                        <div class="onjecasa-about-actions">
                            <a href="{{ route('product_page') }}" class="thm-btn">
                                <span class="thm-btn-text">Shop Products</span>
                                <span class="thm-btn-icon-box"><i class="fas fa-arrow-right"></i></span>
                            </a>
                            <a href="{{ route('contact_page') }}" class="onjecasa-about-phone">
                                <span class="icon-call"></span>
                                Talk To ONJECASA
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="onjecasa-about-band">
        <div class="container">
            <div class="section-title text-center sec-title-animation animation-style1">
                <div class="section-title__tagline-box">
                    <span class="section-title__tagline">What We Stock</span>
                </div>
                <h2 class="section-title__title title-animation">Daily Essentials For<br> <span>Every Kind Of Shopper.</span></h2>
            </div>
            <div class="row g-4">
                <div class="col-xl-3 col-md-6">
                    <div class="onjecasa-about-service">
                        <div class="onjecasa-about-service__icon"><i class="fa fa-shopping-basket"></i></div>
                        <h3>Groceries</h3>
                        <p>Pantry staples, beverages, packaged goods, and everyday household essentials.</p>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="onjecasa-about-service">
                        <div class="onjecasa-about-service__icon"><i class="fa fa-leaf"></i></div>
                        <h3>Fresh Produce</h3>
                        <p>Fresh fruits, vegetables, chilled items, drinks, and other daily add-ons.</p>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="onjecasa-about-service">
                        <div class="onjecasa-about-service__icon"><i class="fa fa-motorcycle"></i></div>
                        <h3>Order Modes</h3>
                        <p>Pickup, delivery, counter checkout, phone requests, and bulk order handling.</p>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="onjecasa-about-service">
                        <div class="onjecasa-about-service__icon"><i class="fa fa-cash-register"></i></div>
                        <h3>POS Ready</h3>
                        <p>Cashiers can create orders from the same products, add-ons, inventory, and pricing setup.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="onjecasa-about-banner">
        <img src="{{ asset('themes/zabaga/assets/images/resources/about-page-img-1.jpg') }}" alt="ONJECASA supermarket stock">
        <div class="container">
            <div class="onjecasa-about-banner__content">
                <div class="section-title text-left sec-title-animation animation-style2">
                    <div class="section-title__tagline-box">
                        <span class="section-title__tagline">Store Standard</span>
                    </div>
                    <h2 class="section-title__title title-animation">Strong Stock, Fast Service, Clean Ordering.</h2>
                </div>
                <p>The frontend customers see and the backend admins manage now speak the same language: ONJECASA products, controlled images, real pricing, add-ons, inventory, and checkout flow.</p>
            </div>
        </div>
    </section>

    <section class="onjecasa-about-steps">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-xl-5">
                    <div class="section-title text-left sec-title-animation animation-style2">
                        <div class="section-title__tagline-box">
                            <span class="section-title__tagline">How It Works</span>
                        </div>
                        <h2 class="section-title__title title-animation">Choose, Customize,<br> <span>Then Checkout.</span></h2>
                    </div>
                </div>
                <div class="col-xl-7">
                    <div class="onjecasa-step">
                        <span>01</span>
                        <h3>Pick a product</h3>
                        <p>Customers browse the live catalog and open product cards for details, options, and available media.</p>
                    </div>
                    <div class="onjecasa-step">
                        <span>02</span>
                        <h3>Add add-ons</h3>
                        <p>Checkout add-ons carry their own backend prices, images, and descriptions so totals update correctly.</p>
                    </div>
                    <div class="onjecasa-step">
                        <span>03</span>
                        <h3>Checkout or create POS order</h3>
                        <p>Orders can be placed from the website or created by staff through the connected POS workflow.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection


