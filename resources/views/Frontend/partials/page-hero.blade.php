@php
    $page = $page ?? 'home';
    $slide = isset($homeSlides) ? $homeSlides->first() : null;
    $heroImage = $slide && !empty($slide->image)
        ? asset('storage/' . $slide->image)
        : asset('images/onjecasa-products.svg');

    $heroCopy = [
        'home' => [
            'eyebrow' => 'ONJECASA',
            'title' => 'Groceries, household goods, fresh produce, and more.',
            'text' => 'Shop quality supermarket products for pickup, delivery, home restocks, office supplies, and bulk occasions.',
        ],
        'products' => [
            'eyebrow' => 'Products Catalog',
            'title' => 'Choose your product and option.',
            'text' => 'Browse groceries, beverages, household goods, fresh produce, and add-on options.',
        ],
        'about' => [
            'eyebrow' => 'About Us',
            'title' => 'A reliable product partner for every occasion.',
            'text' => 'We stock fresh products for daily shopping, work supplies, home restocks, events, and family needs.',
        ],
        'contact' => [
            'eyebrow' => 'Contact',
            'title' => 'Need products for an event or business?',
            'text' => 'Reach out for single orders, delivery, pickup, and larger group product requests.',
        ],
    ][$page] ?? [
        'eyebrow' => 'ONJECASA',
        'title' => 'Quality products, ready when you need them.',
        'text' => 'Shop fresh ONJECASA products for pickup, delivery, and bulk requests.',
    ];
@endphp

<section class="atta-page-hero" style="background-image: linear-gradient(90deg, rgba(16,42,31,.88), rgba(16,42,31,.45)), url('{{ $heroImage }}');">
    <div class="container">
        <div class="atta-page-hero__content">
            <span class="atta-page-hero__eyebrow">{{ $heroCopy['eyebrow'] }}</span>
            <h1>{{ $heroCopy['title'] }}</h1>
            <p>{{ $heroCopy['text'] }}</p>
            @if($page === 'home')
                <div class="atta-page-hero__actions">
                    <a href="{{ route('product_page') }}" class="btn-primary-modern">Shop Products</a>
                    <a href="{{ route('contact_page') }}" class="btn-outline-modern">Bulk Enquiry</a>
                </div>
            @endif
        </div>
    </div>
</section>

@once
    <style>
        .atta-page-hero {
            min-height: 520px;
            display: flex;
            align-items: center;
            background-size: cover;
            background-position: center;
            color: #fff;
            position: relative;
        }

        .atta-page-hero__content {
            max-width: 720px;
            padding: 96px 0 110px;
        }

        .atta-page-hero__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            color: #f4d28a;
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        .atta-page-hero h1 {
            max-width: 680px;
            font-size: clamp(2.35rem, 5vw, 4.75rem);
            line-height: 1;
            margin-bottom: 1.25rem;
            color: #fff;
        }

        .atta-page-hero p {
            max-width: 600px;
            font-size: 1.08rem;
            line-height: 1.75;
            color: rgba(255, 255, 255, .86);
            margin-bottom: 1.75rem;
        }

        .atta-page-hero__actions {
            display: flex;
            flex-wrap: wrap;
            gap: .85rem;
        }

        .atta-page-hero .btn-outline-modern {
            border-color: rgba(255, 255, 255, .75);
            color: #fff;
        }

        .atta-page-hero .btn-outline-modern:hover {
            border-color: #fff;
            background: #fff;
            color: #0f1f4d;
        }

        @media (max-width: 767.98px) {
            .atta-page-hero {
                min-height: 460px;
            }

            .atta-page-hero__content {
                padding: 72px 0 84px;
            }
        }
    </style>
@endonce


