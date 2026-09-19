@extends('Frontend.layout')

@section('title', 'Contact - ONJECASA')
@section('meta_description', 'Contact ONJECASA for pickup, delivery, bulk shopping, group products, office supplies, and fresh supermarket orders in Accra.')

@push('styles')
    <link href="{{ asset('themes/zabaga/assets/css/module-css/page-header.css') }}" rel="stylesheet">
    <link href="{{ asset('themes/zabaga/assets/css/module-css/contact.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        .onjecasa-template-main {
            padding: 0;
            background: #fff;
        }

        .onjecasa-contact-page .page-header {
            padding: 166px 0 154px;
            -webkit-mask-size: 100% 100%;
            mask-size: 100% 100%;
        }

        .onjecasa-contact-page .page-header__shape-1 img,
        .onjecasa-contact-page .page-header__shape-2 img {
            transform: scale(.58);
        }

        .onjecasa-contact-page .page-header__shape-1 img {
            transform-origin: right bottom;
        }

        .onjecasa-contact-page .page-header__shape-2 img {
            transform-origin: left bottom;
        }

        .onjecasa-contact-intro {
            position: relative;
            padding: 108px 0 0;
            background: #fff;
            overflow: hidden;
        }

        .onjecasa-contact-intro__shape-one,
        .onjecasa-contact-intro__shape-two {
            position: absolute;
            pointer-events: none;
            opacity: .9;
        }

        .onjecasa-contact-intro__shape-one {
            left: 34px;
            top: 82px;
        }

        .onjecasa-contact-intro__shape-two {
            right: 44px;
            bottom: 44px;
        }

        .onjecasa-contact-grid {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 24px;
            margin-top: 38px;
        }

        .onjecasa-contact-card {
            min-height: 186px;
            padding: 30px;
            border: 1px solid rgba(var(--helpest-black-rgb), .08);
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 18px 45px rgba(var(--helpest-black-rgb), .07);
        }

        .onjecasa-contact-card__icon {
            width: 60px;
            height: 60px;
            display: grid;
            place-items: center;
            margin-bottom: 19px;
            border-radius: 50%;
            background: var(--helpest-base);
            color: #fff;
            font-size: 22px;
        }

        .onjecasa-contact-card h3 {
            margin-bottom: 8px;
            color: var(--helpest-black);
            font-size: 23px;
            font-weight: 900;
        }

        .onjecasa-contact-card p,
        .onjecasa-contact-card a {
            margin: 0;
            color: var(--helpest-gray);
            font-weight: 700;
            line-height: 1.7;
        }

        .onjecasa-contact-card a:hover {
            color: var(--helpest-base);
        }

        .onjecasa-contact-form-wrap {
            position: relative;
            padding: 108px 0 110px;
            overflow: hidden;
        }

        .onjecasa-contact-form-wrap__bg {
            position: absolute;
            inset: 0;
            background-position: center;
            background-size: cover;
            opacity: .12;
            mix-blend-mode: luminosity;
        }

        .onjecasa-contact-form-wrap::before {
            content: "";
            position: absolute;
            inset: 0;
            background: var(--helpest-black);
        }

        .onjecasa-contact-content {
            position: relative;
            z-index: 1;
        }

        .onjecasa-contact-content .section-title__tagline {
            color: var(--helpest-primary);
        }

        .onjecasa-contact-content .section-title__title,
        .onjecasa-contact-content p {
            color: #fff;
        }

        .onjecasa-contact-content p {
            max-width: 520px;
            margin-bottom: 30px;
            color: rgba(255, 255, 255, .78);
            font-size: 17px;
            font-weight: 700;
            line-height: 1.8;
        }

        .onjecasa-contact-list {
            display: grid;
            gap: 18px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .onjecasa-contact-list li {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .onjecasa-contact-list__icon {
            width: 54px;
            height: 54px;
            flex: 0 0 54px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: var(--helpest-primary);
            color: var(--helpest-black);
            font-size: 18px;
        }

        .onjecasa-contact-list small {
            display: block;
            color: rgba(255, 255, 255, .68);
            font-weight: 800;
            line-height: 1.2;
        }

        .onjecasa-contact-list a,
        .onjecasa-contact-list span {
            color: #fff;
            font-size: 18px;
            font-weight: 900;
            line-height: 1.45;
        }

        .onjecasa-contact-list a:hover {
            color: var(--helpest-primary);
        }

        .onjecasa-contact-form {
            position: relative;
            z-index: 1;
            padding: 42px;
            border-radius: 14px;
            background: var(--helpest-extra);
        }

        .onjecasa-contact-form__title {
            margin-bottom: 24px;
            color: var(--helpest-black);
            font-size: 30px;
            font-weight: 900;
        }

        .onjecasa-contact-input {
            position: relative;
            margin-bottom: 20px;
        }

        .onjecasa-contact-input input,
        .onjecasa-contact-input textarea {
            width: 100%;
            border: 0;
            outline: 0;
            border-radius: 8px;
            background: #fff;
            color: var(--helpest-gray);
            font-size: 16px;
            font-weight: 700;
        }

        .onjecasa-contact-input input {
            height: 64px;
            padding: 0 24px;
        }

        .onjecasa-contact-input textarea {
            height: 152px;
            padding: 18px 24px;
            resize: vertical;
        }

        .onjecasa-contact-error {
            display: block;
            margin-top: 7px;
            color: #b42318;
            font-size: 13px;
            font-weight: 800;
        }

        .onjecasa-contact-map {
            position: relative;
            padding: 0 0 110px;
            background: #fff;
        }

        .onjecasa-contact-map__frame {
            height: 430px;
            overflow: hidden;
            border-radius: 16px;
            background: var(--helpest-extra);
        }

        #onjecasaContactMap {
            width: 100%;
            height: 100%;
            min-height: 430px;
        }

        .onjecasa-contact-map__frame iframe {
            width: 100%;
            height: 100%;
            border: 0;
            display: block;
        }

        .onjecasa-contact-map__fallback {
            height: 100%;
            display: grid;
            place-items: center;
            padding: 36px;
            text-align: center;
            color: var(--helpest-black);
            font-size: 24px;
            font-weight: 900;
        }

        .onjecasa-contact-map__heading {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1.5rem;
            margin-bottom: 28px;
        }

        .onjecasa-contact-map__heading .section-title {
            margin-bottom: 0;
        }

        .onjecasa-contact-map__count {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-height: 46px;
            padding: 10px 18px;
            border-radius: 999px;
            background: var(--helpest-extra);
            color: var(--helpest-black);
            font-weight: 900;
            white-space: nowrap;
        }

        .onjecasa-contact-map__count i {
            color: var(--helpest-base);
        }

        .onjecasa-contact-branch-list {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin-top: 24px;
        }

        .onjecasa-contact-branch {
            padding: 20px;
            border: 1px solid rgba(var(--helpest-black-rgb), .08);
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 12px 34px rgba(var(--helpest-black-rgb), .06);
        }

        .onjecasa-contact-branch h3 {
            margin-bottom: 6px;
            color: var(--helpest-black);
            font-size: 20px;
            font-weight: 900;
        }

        .onjecasa-contact-branch p,
        .onjecasa-contact-branch a {
            margin: 0;
            color: var(--helpest-gray);
            font-weight: 700;
            line-height: 1.55;
        }

        .onjecasa-contact-marker {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border: 4px solid #fff;
            border-radius: 50% 50% 50% 0;
            background: var(--helpest-base);
            color: #fff;
            box-shadow: 0 10px 24px rgba(0,0,0,.28);
            transform: rotate(-45deg);
        }

        .onjecasa-contact-marker i {
            transform: rotate(45deg);
            font-size: 16px;
        }

        .onjecasa-contact-popup strong {
            display: block;
            margin-bottom: 4px;
            color: #111;
            font-size: 15px;
        }

        .onjecasa-contact-popup span,
        .onjecasa-contact-popup a {
            color: #4b5563;
            font-size: 13px;
            line-height: 1.45;
        }

        @media (max-width: 991px) {
            .onjecasa-contact-grid {
                grid-template-columns: 1fr;
            }

            .onjecasa-contact-content {
                margin-bottom: 44px;
            }

            .onjecasa-contact-map__heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .onjecasa-contact-branch-list {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767px) {
            .onjecasa-contact-page .page-header {
                padding: 120px 0 116px;
            }

            .onjecasa-contact-intro,
            .onjecasa-contact-form-wrap {
                padding-top: 72px;
            }

            .onjecasa-contact-form-wrap {
                padding-bottom: 72px;
            }

            .onjecasa-contact-map {
                padding-bottom: 72px;
            }

            .onjecasa-contact-form {
                padding: 28px 20px;
            }

            .onjecasa-contact-map__frame {
                height: 340px;
            }

            #onjecasaContactMap {
                min-height: 340px;
            }

            .onjecasa-contact-intro__shape-one,
            .onjecasa-contact-intro__shape-two {
                display: none;
            }
        }
    </style>
@endpush

@section('full_width_content')
@php
    $settings = $settings ?? \App\Models\ContactSetting::getSettings();
    $branches = $branches ?? collect();
    $phone = $settings->business_number ?: '+233 57 833 9542';
    $whatsapp = $settings->whatsapp_number ?: $phone;
    $whatsappLink = $settings->whatsapp_link ?: 'https://wa.me/' . preg_replace('/\D+/', '', $whatsapp);
    $email = $settings->form_email ?: 'orders@onjecasa.test';
    $address = $settings->office_address ?: 'Nii Okaiman West Main Road, Greater Accra';
    $headerImage = !empty($settings->header_image)
        ? asset('storage/' . $settings->header_image)
        : asset('themes/zabaga/assets/images/resources/contact-one-img-1.png');
    $branchMapLocations = $branches->map(fn ($branch) => [
        'id' => $branch->id,
        'name' => $branch->name,
        'code' => $branch->code,
        'phone' => $branch->phone,
        'address' => $branch->address,
        'latitude' => (float) $branch->latitude,
        'longitude' => (float) $branch->longitude,
    ])->values();
@endphp

<div class="onjecasa-contact-page">
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
                <h2>Contact ONJECASA</h2>
                <div class="thm-breadcrumb__box">
                    <ul class="thm-breadcrumb list-unstyled">
                        <li><a href="{{ route('home_page') }}">Home</a></li>
                        <li><span class="icon-right-arrow"></span></li>
                        <li>Contact</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="onjecasa-contact-intro">
        <div class="onjecasa-contact-intro__shape-one">
            <img src="{{ asset('themes/zabaga/assets/images/shapes/contact-page-shape-1.png') }}" alt="">
        </div>
        <div class="onjecasa-contact-intro__shape-two">
            <img src="{{ asset('themes/zabaga/assets/images/shapes/contact-page-shape-2.png') }}" alt="">
        </div>
        <div class="container">
            <div class="section-title text-center sec-title-animation animation-style1">
                <div class="section-title__tagline-box">
                    <span class="section-title__tagline">Talk To ONJECASA</span>
                </div>
                <h2 class="section-title__title title-animation">Pickup, Delivery,<br> <span>Group Orders.</span></h2>
            </div>
            <div class="onjecasa-contact-grid">
                <div class="onjecasa-contact-card">
                    <div class="onjecasa-contact-card__icon"><i class="fa fa-phone"></i></div>
                    <h3>Call Us</h3>
                    <p><a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a></p>
                </div>
                <div class="onjecasa-contact-card">
                    <div class="onjecasa-contact-card__icon"><i class="fab fa-whatsapp"></i></div>
                    <h3>WhatsApp</h3>
                    <p><a href="{{ $whatsappLink }}" target="_blank" rel="noopener">{{ $whatsapp }}</a></p>
                </div>
                <div class="onjecasa-contact-card">
                    <div class="onjecasa-contact-card__icon"><i class="fa fa-location-dot"></i></div>
                    <h3>Find ONJECASA</h3>
                    <p>{{ $address }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="onjecasa-contact-form-wrap">
        <div class="onjecasa-contact-form-wrap__bg" style="background-image: url({{ asset('themes/zabaga/assets/images/backgrounds/contact-one-bg.jpg') }});"></div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-xl-6">
                    <div class="onjecasa-contact-content">
                        <div class="section-title text-left sec-title-animation animation-style2">
                            <div class="section-title__tagline-box">
                                <span class="section-title__tagline">Send A Message</span>
                            </div>
                            <h2 class="section-title__title title-animation">Plan Your Next<br> <span>ONJECASA Product.</span></h2>
                        </div>
                        <p>Send delivery questions, pickup requests, bulk shopping enquiries, and group product details. Include quantities, date, and location for larger orders.</p>
                        <ul class="onjecasa-contact-list">
                            <li>
                                <div class="onjecasa-contact-list__icon"><i class="fa fa-envelope"></i></div>
                                <div><small>Email</small><a href="mailto:{{ $email }}">{{ $email }}</a></div>
                            </li>
                            <li>
                                <div class="onjecasa-contact-list__icon"><i class="fa fa-location-dot"></i></div>
                                <div><small>Address</small><span>{{ $address }}</span></div>
                            </li>
                            <li>
                                <div class="onjecasa-contact-list__icon"><i class="fa fa-utensils"></i></div>
                                <div><small>Orders</small><span>Retail products, events, office supplies, and bulk food requests.</span></div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-xl-6">
                    <form method="POST" action="{{ route('contact.submit') }}" class="onjecasa-contact-form">
                        @csrf
                        <h3 class="onjecasa-contact-form__title">Send an enquiry</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="onjecasa-contact-input">
                                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Your name" required>
                                    @error('name')<small class="onjecasa-contact-error">{{ $message }}</small>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="onjecasa-contact-input">
                                    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email address" required>
                                    @error('email')<small class="onjecasa-contact-error">{{ $message }}</small>@enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="onjecasa-contact-input">
                                    <input type="text" name="subject" value="{{ old('subject') }}" placeholder="Subject" required>
                                    @error('subject')<small class="onjecasa-contact-error">{{ $message }}</small>@enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="onjecasa-contact-input">
                                    <textarea name="message" placeholder="Tell us what you need" required>{{ old('message') }}</textarea>
                                    @error('message')<small class="onjecasa-contact-error">{{ $message }}</small>@enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <button class="thm-btn" type="submit">
                                    <span class="thm-btn-text">Send Enquiry</span>
                                    <span class="thm-btn-icon-box"><i class="fas fa-arrow-right"></i></span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="onjecasa-contact-map">
        <div class="container">
            <div class="onjecasa-contact-map__heading">
                <div class="section-title text-left sec-title-animation animation-style2">
                    <div class="section-title__tagline-box">
                        <span class="section-title__tagline">ONJECASA Locations</span>
                    </div>
                    <h2 class="section-title__title title-animation">Find A Branch<br> <span>Near You.</span></h2>
                </div>
                <div class="onjecasa-contact-map__count">
                    <i class="fa fa-location-dot"></i>
                    {{ $branchMapLocations->count() }} {{ \Illuminate\Support\Str::plural('location', $branchMapLocations->count()) }}
                </div>
            </div>
            <div class="onjecasa-contact-map__frame">
                @if($branchMapLocations->isNotEmpty())
                    <div id="onjecasaContactMap" aria-label="ONJECASA branch location map"></div>
                @elseif(!empty($settings->map_embed_url))
                    <iframe src="{{ $settings->map_embed_url }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="ONJECASA location"></iframe>
                @else
                    <div class="onjecasa-contact-map__fallback">
                        <div>
                            <img src="{{ $headerImage }}" alt="ONJECASA" style="max-height: 210px; width: auto; max-width: 100%; object-fit: contain; margin-bottom: 18px;">
                            <div>{{ $address }}</div>
                        </div>
                    </div>
                @endif
            </div>
            @if($branchMapLocations->isNotEmpty())
                <div class="onjecasa-contact-branch-list">
                    @foreach($branches as $branch)
                        <div class="onjecasa-contact-branch">
                            <h3>{{ $branch->name }}</h3>
                            @if($branch->address)
                                <p>{{ $branch->address }}</p>
                            @endif
                            @if($branch->phone)
                                <p><a href="tel:{{ preg_replace('/\s+/', '', $branch->phone) }}">{{ $branch->phone }}</a></p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
@endsection

@push('scripts')
@if($branchMapLocations->isNotEmpty())
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const locations = @json($branchMapLocations);
    const mapElement = document.getElementById('onjecasaContactMap');
    if (!mapElement || !window.L || !locations.length) return;

    const firstLocation = locations[0];
    const map = L.map(mapElement, {scrollWheelZoom: false}).setView([firstLocation.latitude, firstLocation.longitude], locations.length > 1 ? 11 : 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    const markerIcon = L.divIcon({
        className: '',
        html: '<div class="onjecasa-contact-marker"><i class="fa fa-utensils"></i></div>',
        iconSize: [42, 42],
        iconAnchor: [21, 42],
        popupAnchor: [0, -42]
    });

    const markers = locations.map(function (location) {
        const popup = [
            '<div class="onjecasa-contact-popup">',
            '<strong>' + escapeHtml(location.name) + '</strong>',
            location.address ? '<span>' + escapeHtml(location.address) + '</span><br>' : '',
            location.phone ? '<a href="tel:' + escapeHtml(String(location.phone).replace(/\s+/g, '')) + '">' + escapeHtml(location.phone) + '</a>' : '',
            '</div>'
        ].join('');

        return L.marker([location.latitude, location.longitude], {icon: markerIcon})
            .addTo(map)
            .bindPopup(popup);
    });

    if (markers.length > 1) {
        const group = L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.2));
    }

    setTimeout(function () {
        map.invalidateSize();
    }, 100);

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }
});
</script>
@endif
@endpush


