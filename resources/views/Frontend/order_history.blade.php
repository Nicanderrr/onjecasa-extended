@extends('Frontend.layout')

@section('title', 'Order History - ONJECASA')
@section('meta_description', 'Review ONJECASA order history, delivery status, pickup details, totals, and purchased products.')

@section('full_width_content')
@php
    $orders = collect($orders ?? []);
    $statusTone = function (?string $status): string {
        return match (strtolower((string) $status)) {
            'paid', 'completed', 'delivered', 'ready', 'accepted' => 'is-success',
            'cancelled', 'failed', 'rejected' => 'is-danger',
            'processing', 'in_progress', 'on_the_way', 'dispatched' => 'is-warning',
            default => 'is-pending',
        };
    };
@endphp

<div class="onjecasa-orders-page">
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
                <h2>Order History</h2>
                <div class="thm-breadcrumb__box">
                    <ul class="thm-breadcrumb list-unstyled">
                        <li><a href="{{ route('home_page') }}">Home</a></li>
                        <li><span class="icon-right-arrow"></span></li>
                        <li>Orders</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="orders-dashboard">
        <div class="container">
            <div class="section-title text-center">
                <div class="section-title__tagline-box">
                    <span class="section-title__tagline">Your Orders</span>
                </div>
                <h2 class="section-title__title">Track Every <span>ONJECASA Purchase.</span></h2>
            </div>

            <div class="orders-summary">
                <article class="orders-summary-card">
                    <span class="orders-summary-card__icon"><i class="fas fa-receipt"></i></span>
                    <small>Orders</small>
                    <strong>{{ number_format((int) ($totalOrders ?? 0)) }}</strong>
                </article>
                <article class="orders-summary-card">
                    <span class="orders-summary-card__icon"><i class="fas fa-wallet"></i></span>
                    <small>Total Spent</small>
                    <strong>GHC {{ number_format((float) ($totalSpent ?? 0), 2) }}</strong>
                </article>
                <article class="orders-summary-card">
                    <span class="orders-summary-card__icon"><i class="fas fa-shopping-basket"></i></span>
                    <small>Items Bought</small>
                    <strong>{{ number_format((int) ($totalItems ?? 0)) }}</strong>
                </article>
                <article class="orders-summary-card">
                    <span class="orders-summary-card__icon"><i class="fas fa-chart-line"></i></span>
                    <small>Average Order</small>
                    <strong>GHC {{ number_format((float) ($avgOrderValue ?? 0), 2) }}</strong>
                </article>
            </div>

            @if($orders->isNotEmpty())
                <div class="orders-layout">
                    <div class="orders-list">
                        @foreach($orders as $order)
                            @php
                                $items = collect($order->orderProducts ?? []);
                                $total = (float) ($order->total ?? $items->sum('subtotal'));
                                $status = $order->delivery_status ?? $order->status ?? 'pending';
                                $fulfillment = $order->fulfillment_method ?? 'delivery';
                                $created = $order->created_at ? \Illuminate\Support\Carbon::parse($order->created_at) : null;
                            @endphp
                            <article class="order-card">
                                <div class="order-card__head">
                                    <div>
                                        <span class="order-card__eyebrow">Order #{{ $order->id }}</span>
                                        <h3>{{ $created ? $created->format('M j, Y') : 'Recent order' }}</h3>
                                    </div>
                                    <span class="order-status {{ $statusTone($status) }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                                </div>

                                <div class="order-card__meta">
                                    <div>
                                        <small>Fulfillment</small>
                                        <strong>{{ ucfirst($fulfillment) }}</strong>
                                    </div>
                                    <div>
                                        <small>Branch</small>
                                        <strong>{{ $order->branch?->name ?? 'Selected branch' }}</strong>
                                    </div>
                                    <div>
                                        <small>Total</small>
                                        <strong class="orders-price">GHC {{ number_format($total, 2) }}</strong>
                                    </div>
                                </div>

                                <div class="order-products">
                                    @forelse($items->take(4) as $orderProduct)
                                        <div class="order-product">
                                            <div class="order-product__thumb">
                                                <img src="{{ $orderProduct->product?->thumbnail_media_url ?: asset('images/onjecasa-products.svg') }}" alt="{{ $orderProduct->product_name }}" onerror="this.onerror=null;this.src='{{ asset('images/onjecasa-products.svg') }}';">
                                            </div>
                                            <div>
                                                <strong>{{ $orderProduct->product_name }}</strong>
                                                <span>x{{ $orderProduct->quantity }} Â· GHC {{ number_format((float) $orderProduct->price, 2) }}</span>
                                                @if(\App\Support\MealExtras::label($orderProduct->color))
                                                    <em>{{ \App\Support\MealExtras::label($orderProduct->color) }}</em>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <p class="orders-muted mb-0">No line items are attached to this order.</p>
                                    @endforelse
                                </div>

                                <div class="order-card__foot">
                                    <div>
                                        @if($fulfillment === 'delivery')
                                            <span>Delivery fee: GHC {{ number_format((float) ($order->delivery_fee ?? 0), 2) }}</span>
                                            <span>{{ number_format((float) ($order->delivery_distance_km ?? 0), 2) }} km</span>
                                        @else
                                            <span>Pickup order</span>
                                            <span>No delivery fee</span>
                                        @endif
                                    </div>
                                    <a href="{{ route('order.details', $order->id) }}" class="thm-btn">
                                        <span class="thm-btn-text">View Details</span>
                                        <span class="thm-btn-icon-box"><i class="fas fa-arrow-right"></i></span>
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <aside class="orders-aside">
                        <div class="orders-aside-card">
                            <span class="orders-aside-card__icon"><i class="fas fa-headset"></i></span>
                            <h3>Need help with an order?</h3>
                            <p>Contact the store team with your order number and we will help with delivery, pickup, or product questions.</p>
                            <a href="{{ route('contact_page') }}" class="orders-link">Contact support</a>
                        </div>
                        <div class="orders-aside-card orders-aside-card--dark">
                            <h3>Keep shopping</h3>
                            <p>Browse groceries, beverages, household essentials, and restock items from the live catalog.</p>
                            <a href="{{ route('product_page') }}" class="orders-link">Shop products</a>
                        </div>
                    </aside>
                </div>
            @else
                <div class="orders-empty">
                    <span><i class="fas fa-shopping-basket"></i></span>
                    <h3>No orders yet.</h3>
                    <p>Your purchases will appear here after checkout.</p>
                    <a href="{{ route('product_page') }}" class="thm-btn">
                        <span class="thm-btn-text">Start Shopping</span>
                        <span class="thm-btn-icon-box"><i class="fas fa-arrow-right"></i></span>
                    </a>
                </div>
            @endif
        </div>
    </section>
</div>
@endsection

@push('styles')
<link href="{{ asset('themes/zabaga/assets/css/module-css/page-header.css') }}" rel="stylesheet">
<style>
    .onjecasa-template-main { padding: 0; background: #fff; }
    .onjecasa-orders-page .page-header {
        padding: 150px 0 138px;
        -webkit-mask-size: 100% 100%;
        mask-size: 100% 100%;
    }
    .orders-dashboard {
        padding: 105px 0;
        background: #fff;
    }
    .orders-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 24px;
        margin-bottom: 34px;
    }
    .orders-summary-card,
    .order-card,
    .orders-aside-card,
    .orders-empty {
        border: 1px solid rgba(var(--helpest-black-rgb), .12);
        border-radius: 14px;
        background: var(--helpest-white);
        box-shadow: 0 22px 60px rgba(var(--helpest-black-rgb), .10);
    }
    .orders-summary-card {
        padding: 26px;
        min-height: 150px;
    }
    .orders-summary-card__icon,
    .orders-aside-card__icon,
    .orders-empty span {
        display: inline-grid;
        place-items: center;
        width: 54px;
        height: 54px;
        margin-bottom: 18px;
        border-radius: 50%;
        background: var(--helpest-base);
        color: #fff;
        font-size: 20px;
    }
    .orders-summary-card small,
    .order-card__eyebrow,
    .order-card__meta small {
        display: block;
        color: var(--helpest-gray);
        font-size: 12px;
        font-weight: 900;
        line-height: 1;
        text-transform: uppercase;
        letter-spacing: .08em;
    }
    .orders-summary-card strong {
        display: block;
        margin-top: 10px;
        color: var(--helpest-black);
        font-size: 28px;
        line-height: 1.05;
        font-weight: 900;
    }
    .orders-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 360px;
        gap: 30px;
        align-items: start;
    }
    .orders-list {
        display: grid;
        gap: 24px;
    }
    .order-card {
        padding: 30px;
    }
    .order-card__head,
    .order-card__foot,
    .order-card__meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
    }
    .order-card__head {
        padding-bottom: 22px;
        border-bottom: 1px solid rgba(var(--helpest-black-rgb), .10);
    }
    .order-card__head h3 {
        margin-top: 8px;
        color: var(--helpest-black);
        font-size: 30px;
        line-height: 1.1;
        font-weight: 900;
    }
    .order-status {
        flex: 0 0 auto;
        border-radius: 999px;
        padding: 10px 15px;
        color: #fff;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .08em;
    }
    .order-status.is-success { background: #15803d; }
    .order-status.is-warning { background: #b45309; }
    .order-status.is-danger { background: #b91c1c; }
    .order-status.is-pending { background: var(--helpest-black); }
    .order-card__meta {
        margin: 24px 0;
        padding: 18px 20px;
        border-radius: 12px;
        background: var(--helpest-extra);
    }
    .order-card__meta strong {
        display: block;
        margin-top: 7px;
        color: var(--helpest-black);
        font-size: 16px;
        line-height: 1.25;
        font-weight: 900;
    }
    .orders-price {
        color: var(--helpest-base) !important;
    }
    .order-products {
        display: grid;
        gap: 14px;
    }
    .order-product {
        display: grid;
        grid-template-columns: 74px minmax(0, 1fr);
        gap: 14px;
        align-items: center;
        padding-bottom: 14px;
        border-bottom: 1px solid rgba(var(--helpest-black-rgb), .08);
    }
    .order-product:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }
    .order-product__thumb {
        width: 74px;
        height: 74px;
        overflow: hidden;
        border-radius: 12px;
        background: #fef3c7;
    }
    .order-product__thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .order-product strong,
    .order-product span,
    .order-product em {
        display: block;
    }
    .order-product strong {
        color: var(--helpest-black);
        font-size: 16px;
        font-weight: 900;
    }
    .order-product span,
    .order-product em,
    .orders-muted,
    .order-card__foot span,
    .orders-aside-card p,
    .orders-empty p {
        color: var(--helpest-gray);
        font-size: 14px;
        line-height: 1.55;
        font-style: normal;
    }
    .order-card__foot {
        margin-top: 26px;
        padding-top: 22px;
        border-top: 1px solid rgba(var(--helpest-black-rgb), .10);
    }
    .order-card__foot div {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }
    .orders-aside {
        position: sticky;
        top: 120px;
        display: grid;
        gap: 24px;
    }
    .orders-aside-card {
        padding: 30px;
    }
    .orders-aside-card--dark {
        background: var(--helpest-black);
        color: #fff;
    }
    .orders-aside-card h3 {
        margin-bottom: 12px;
        color: inherit;
        font-size: 27px;
        line-height: 1.1;
        font-weight: 900;
    }
    .orders-aside-card--dark p {
        color: rgba(255,255,255,.74);
    }
    .orders-link {
        display: inline-flex;
        margin-top: 10px;
        color: var(--helpest-base);
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .06em;
    }
    .orders-empty {
        padding: 60px 28px;
        text-align: center;
    }
    .orders-empty span {
        width: 88px;
        height: 88px;
        margin-bottom: 24px;
        font-size: 34px;
    }
    .orders-empty h3 {
        margin-bottom: 10px;
        color: var(--helpest-black);
        font-size: 36px;
        font-weight: 900;
    }
    @media (max-width: 1199px) {
        .orders-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .orders-layout {
            grid-template-columns: 1fr;
        }
        .orders-aside {
            position: relative;
            top: auto;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 767px) {
        .onjecasa-orders-page .page-header {
            padding: 115px 0 110px;
        }
        .orders-dashboard {
            padding: 72px 0;
        }
        .orders-summary,
        .orders-aside {
            grid-template-columns: 1fr;
        }
        .order-card {
            padding: 22px;
        }
        .order-card__head,
        .order-card__foot,
        .order-card__meta {
            align-items: flex-start;
            flex-direction: column;
        }
        .order-card__meta {
            gap: 14px;
        }
        .order-card__head h3 {
            font-size: 26px;
        }
        .order-card__foot .thm-btn {
            width: 100%;
        }
    }
</style>
@endpush
