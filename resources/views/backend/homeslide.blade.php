@extends('backend.admin')

@section('title', 'Homepage Sections - ONJECASA')
@section('page-icon', 'bi bi-images')
@section('page-eyebrow', 'Website Admin')
@section('page-title', 'Homepage Sections')
@section('page-description', 'Control the three editable content areas at the top of the public homepage.')

@section('admin')
<style>
    .hero-mgmt-container { max-width: 1400px; margin: 0 auto; }
    .hero-tabs { border-bottom: 2px solid #edf2f9; margin-bottom: 2rem; display: flex; gap: .75rem; overflow-x: auto; }
    .hero-tab { padding: .8rem 1.4rem; font-weight: 700; color: #4b5b70; background: transparent; border: 0; border-radius: 8px 8px 0 0; cursor: pointer; white-space: nowrap; }
    .hero-tab.active { color: #2563eb; background: #fff; box-shadow: 0 -4px 12px rgba(0,0,0,.02); }
    .hero-tab.active::after { content: ''; display: block; height: 3px; margin: .65rem -.2rem -.8rem; background: #2563eb; border-radius: 999px; }
    .section-badge { margin-left: .55rem; padding: .24rem .62rem; border-radius: 999px; background: #eff6ff; color: #1d4ed8; font-size: .68rem; font-weight: 800; text-transform: uppercase; }
    .hero-panel { display: none; animation: fadeIn .2s ease; }
    .hero-panel.active-panel { display: block; }
    @keyframes fadeIn { from { opacity: .5; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    .hero-card { border: 1px solid #dbe4ef; border-radius: 8px; background: #fff; box-shadow: 0 16px 34px rgba(15,23,42,.06); }
    .hero-card .card-body { padding: 1.5rem; }
    .hero-card .card-title { display: flex; align-items: center; gap: .7rem; margin-bottom: 1rem; color: #0f172a; font-size: 1.1rem; font-weight: 800; }
    .placement-note { display: flex; gap: .8rem; margin-bottom: 1.4rem; padding: .9rem 1rem; border-radius: 8px; border: 1px solid #bfdbfe; background: #eff6ff; color: #1e3a8a; }
    .placement-note i { margin-top: .12rem; }
    .form-label-modern { display: block; margin-bottom: .4rem; color: #475569; font-size: .78rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
    .form-control-modern { width: 100%; border: 1px solid #dbe4ef; border-radius: 8px; padding: .78rem .9rem; background: #fff; color: #0f172a; }
    .form-control-modern:focus { border-color: #93c5fd; outline: 0; box-shadow: 0 0 0 .18rem rgba(37,99,235,.15); }
    .image-preview-wrapper { display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; padding: 1rem; border: 2px dashed #dbe4ef; border-radius: 8px; background: #f8fafc; }
    .media-preview-stack { display: grid; gap: .7rem; }
    .preview-img, .preview-video { width: 190px; height: 124px; border-radius: 8px; object-fit: cover; background: #fff; border: 1px solid #dbe4ef; }
    .preview-video { display: block; background: #111; }
    .preview-video.is-empty { display: none; }
    .file-input-label { display: inline-flex; align-items: center; gap: .5rem; padding: .65rem 1rem; border: 1px solid #dbe4ef; border-radius: 8px; background: #fff; color: #0f172a; font-weight: 800; cursor: pointer; }
    .media-remove-actions { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .8rem; }
    .btn-remove-media { display: inline-flex; align-items: center; gap: .4rem; border: 1px solid #fecaca; border-radius: 8px; padding: .55rem .75rem; background: #fff1f2; color: #b91c1c; font-size: .78rem; font-weight: 900; }
    .btn-remove-media:hover { background: #fee2e2; }
    .homepage-media-input { display: none; }
    .btn-update-hero { border: 0; border-radius: 8px; padding: .78rem 1.2rem; background: #2563eb; color: #fff; font-weight: 800; }
    .btn-update-hero:hover { background: #1d4ed8; }
    .favorites-bg-preview { position: relative; min-height: 220px; overflow: hidden; border-radius: 8px; border: 1px solid #dbe4ef; background: #111; }
    .favorites-bg-preview img { width: 100%; height: 220px; object-fit: cover; }
    .favorites-bg-preview::after { content: ""; position: absolute; inset: 0; background: var(--overlay-color, #111111); opacity: var(--overlay-opacity, .9); }
    .favorites-bg-preview-text { position: absolute; inset: auto 1rem 1rem; z-index: 2; color: #fff; font-weight: 900; text-shadow: 0 2px 8px rgba(0,0,0,.35); }
    .global-site-preview { position: relative; min-height: 220px; overflow: hidden; border-radius: 8px; border: 1px solid #dbe4ef; background: #111; }
    .global-site-preview img { width: 100%; height: 220px; object-fit: cover; }
    .global-site-preview::after { content: ""; position: absolute; inset: 0; background: rgba(0,0,0,.36); }
    .global-site-preview-text { position: absolute; left: 1rem; right: 1rem; bottom: 1rem; z-index: 2; color: #fff; font-weight: 900; text-shadow: 0 2px 8px rgba(0,0,0,.35); }
    .color-input-modern { width: 72px; height: 44px; padding: .2rem; border: 1px solid #dbe4ef; border-radius: 8px; background: #fff; }
</style>

@php
    $sections = [
        [
            'tab' => 'tab1',
            'active' => true,
            'slide' => $homeslide,
            'route' => route('updateHero'),
            'file_id' => 'image1',
            'video_file_id' => 'video1',
            'image_id' => 'showImage1',
            'video_id' => 'showVideo1',
            'tab_label' => 'Section 1',
            'badge' => 'Main hero',
            'title' => 'Section 1: Main homepage hero',
            'note' => 'Appears as the large image banner on the left side of the website homepage. The main header becomes the large headline; the small header becomes the supporting text.',
            'main_label' => 'Large homepage headline',
            'main_placeholder' => 'e.g. Groceries, fresh produce, household goods, and bulk orders',
            'small_label' => 'Supporting homepage text',
            'small_placeholder' => 'e.g. Shop groceries, household essentials, pickup, delivery, and bulk orders',
            'image_label' => 'Large hero background image',
            'button' => 'Update Main Hero',
        ],
        [
            'tab' => 'tab2',
            'active' => false,
            'slide' => $homeslide2,
            'route' => route('updateHero2'),
            'file_id' => 'image2',
            'video_file_id' => 'video2',
            'image_id' => 'showImage2',
            'video_id' => 'showVideo2',
            'tab_label' => 'Section 2',
            'badge' => 'Top promo',
            'title' => 'Section 2: Top-right homepage promo card',
            'note' => 'Appears as the upper small card beside the main homepage hero. Use this for quick shopping, featured category, or online-order messaging.',
            'main_label' => 'Promo card title',
            'main_placeholder' => 'e.g. Rice plates ready daily',
            'small_label' => 'Promo card text',
            'small_placeholder' => 'e.g. Shop groceries, beverages, produce, and household essentials',
            'image_label' => 'Promo card image',
            'button' => 'Update Top Promo',
        ],
        [
            'tab' => 'tab3',
            'active' => false,
            'slide' => $homeslide3,
            'route' => route('updateHero3'),
            'file_id' => 'image3',
            'video_file_id' => 'video3',
            'image_id' => 'showImage3',
            'video_id' => 'showVideo3',
            'tab_label' => 'Section 3',
            'badge' => 'Bottom promo',
            'title' => 'Section 3: Bottom-right homepage promo card',
            'note' => 'Appears as the lower small card beside the main homepage hero. Use this for bulk orders, events, business supply, or contact prompts.',
            'main_label' => 'Promo card title',
            'main_placeholder' => 'e.g. Request bulk supply',
            'small_label' => 'Promo card text',
            'small_placeholder' => 'e.g. Send quantities, date, and delivery location for bulk supply packages',
            'image_label' => 'Promo card image',
            'button' => 'Update Bottom Promo',
        ],
        [
            'tab' => 'tab4',
            'active' => false,
            'slide' => $aboutImageOne,
            'route' => route('updateHero'),
            'file_id' => 'image4',
            'video_file_id' => 'video4',
            'image_id' => 'showImage4',
            'video_id' => 'showVideo4',
            'tab_label' => 'About Image 1',
            'badge' => 'Large photo',
            'title' => 'About section: Large left photo',
            'note' => 'Appears beside the homepage About Us heading: Everyday Supermarket With Fresh Stock Energy. This is the larger image.',
            'main_label' => 'Internal label',
            'main_placeholder' => 'About Us Image One',
            'small_label' => 'Internal note',
            'small_placeholder' => 'Large about section photo',
            'image_label' => 'About section large image',
            'button' => 'Update About Image 1',
        ],
        [
            'tab' => 'tab5',
            'active' => false,
            'slide' => $aboutImageTwo,
            'route' => route('updateHero'),
            'file_id' => 'image5',
            'video_file_id' => 'video5',
            'image_id' => 'showImage5',
            'video_id' => 'showVideo5',
            'tab_label' => 'About Image 2',
            'badge' => 'Small photo',
            'title' => 'About section: Small overlay photo',
            'note' => 'Appears beside the homepage About Us heading: Everyday Supermarket With Fresh Stock Energy. This is the smaller overlay image.',
            'main_label' => 'Internal label',
            'main_placeholder' => 'About Us Image Two',
            'small_label' => 'Internal note',
            'small_placeholder' => 'Small about section photo',
            'image_label' => 'About section small image',
            'button' => 'Update About Image 2',
        ],
        [
            'tab' => 'tab6',
            'active' => false,
            'slide' => $orderModeOne,
            'route' => route('updateHero'),
            'file_id' => 'image6',
            'video_file_id' => 'video6',
            'image_id' => 'showImage6',
            'video_id' => 'showVideo6',
            'tab_label' => 'Order Mode 1',
            'badge' => 'Pickup',
            'title' => 'Order Modes: Pickup Orders photo',
            'note' => 'Appears in the Order Modes section under Choose How You Want Your Shopping. This is the Pickup Orders image.',
            'main_label' => 'Internal label',
            'main_placeholder' => 'Order Mode Image One',
            'small_label' => 'Internal note',
            'small_placeholder' => 'Pickup orders photo',
            'image_label' => 'Pickup Orders image',
            'button' => 'Update Pickup Image',
        ],
        [
            'tab' => 'tab7',
            'active' => false,
            'slide' => $orderModeTwo,
            'route' => route('updateHero'),
            'file_id' => 'image7',
            'video_file_id' => 'video7',
            'image_id' => 'showImage7',
            'video_id' => 'showVideo7',
            'tab_label' => 'Order Mode 2',
            'badge' => 'Delivery',
            'title' => 'Order Modes: Delivery Orders photo',
            'note' => 'Appears in the Order Modes section under Choose How You Want Your Shopping. This is the Delivery Orders image.',
            'main_label' => 'Internal label',
            'main_placeholder' => 'Order Mode Image Two',
            'small_label' => 'Internal note',
            'small_placeholder' => 'Delivery plates photo',
            'image_label' => 'Delivery Orders image',
            'button' => 'Update Delivery Image',
        ],
        [
            'tab' => 'tab8',
            'active' => false,
            'slide' => $orderModeThree,
            'route' => route('updateHero'),
            'file_id' => 'image8',
            'video_file_id' => 'video8',
            'image_id' => 'showImage8',
            'video_id' => 'showVideo8',
            'tab_label' => 'Order Mode 3',
            'badge' => 'Office',
            'title' => 'Order Modes: Office Restock photo',
            'note' => 'Appears in the Order Modes section under Choose How You Want Your Shopping. This is the Office Restock image.',
            'main_label' => 'Internal label',
            'main_placeholder' => 'Order Mode Image Three',
            'small_label' => 'Internal note',
            'small_placeholder' => 'Office lunch photo',
            'image_label' => 'Office Restock image',
            'button' => 'Update Office Restock Image',
        ],
        [
            'tab' => 'tab9',
            'active' => false,
            'slide' => $orderModeFour,
            'route' => route('updateHero'),
            'file_id' => 'image9',
            'video_file_id' => 'video9',
            'image_id' => 'showImage9',
            'video_id' => 'showVideo9',
            'tab_label' => 'Order Mode 4',
            'badge' => 'Family',
            'title' => 'Order Modes: Family Packs photo',
            'note' => 'Appears in the Order Modes section under Choose How You Want Your Shopping. This is the Family Packs image.',
            'main_label' => 'Internal label',
            'main_placeholder' => 'Order Mode Image Four',
            'small_label' => 'Internal note',
            'small_placeholder' => 'Family packs photo',
            'image_label' => 'Family Packs image',
            'button' => 'Update Family Packs Image',
        ],
        [
            'tab' => 'tab10',
            'active' => false,
            'slide' => $orderModeFive,
            'route' => route('updateHero'),
            'file_id' => 'image10',
            'video_file_id' => 'video10',
            'image_id' => 'showImage10',
            'video_id' => 'showVideo10',
            'tab_label' => 'Order Mode 5',
            'badge' => 'Bulk Supply',
            'title' => 'Order Modes: Bulk Supplies photo',
            'note' => 'Appears in the Order Modes section under Choose How You Want Your Shopping. This is the Bulk Supplies image.',
            'main_label' => 'Internal label',
            'main_placeholder' => 'Order Mode Image Five',
            'small_label' => 'Internal note',
            'small_placeholder' => 'Event bulk supply photo',
            'image_label' => 'Bulk Supplies image',
            'button' => 'Update Bulk Supply Image',
        ],
    ];
@endphp

<div class="container-fluid hero-mgmt-container">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color:#131b30;">Homepage display manager</h2>
            <p class="text-muted mb-0">These sections update the public website homepage. Each tab controls a specific visible area.</p>
        </div>
        <a href="{{ route('home_page') }}" class="btn btn-outline-primary btn-sm" target="_blank">
            <i class="bi bi-box-arrow-up-right"></i> View Homepage
        </a>
    </div>

    <div class="card hero-card mb-4">
        <div class="card-body">
            <div class="card-title">
                <i class="bi bi-layout-text-window"></i> Global page banner and footer images
            </div>
            <div class="placement-note">
                <i class="bi bi-info-circle"></i>
                <div>Controls the shared banner image behind inner page titles and the shared footer background across the public website.</div>
            </div>
            <form method="POST" action="{{ route('updateGlobalSiteImages') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="page_banner_id" value="{{ $pageBannerBg->id ?? 12 }}">
                <input type="hidden" name="site_footer_id" value="{{ $siteFooterBg->id ?? 13 }}">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <label class="form-label-modern">All pages banner image</label>
                        <div class="global-site-preview mb-3">
                            <img id="globalPageBannerPreview" src="{{ $pageBannerBg->image_url }}" alt="All pages banner preview">
                            <div class="global-site-preview-text">About / Products / Contact / Cart / Checkout</div>
                        </div>
                        <label for="globalPageBannerFile" class="file-input-label">
                            <i class="bi bi-upload"></i> Choose banner image
                        </label>
                        <input id="globalPageBannerFile" class="homepage-media-input" name="page_banner" type="file" accept="image/*" data-preview="globalPageBannerPreview">
                        <div class="text-muted small mt-2">JPG, PNG, GIF, WEBP. Max 5MB. Wide images work best.</div>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label-modern">All pages footer image</label>
                        <div class="global-site-preview mb-3">
                            <img id="globalFooterPreview" src="{{ $siteFooterBg->image_url }}" alt="All pages footer preview">
                            <div class="global-site-preview-text">Website Footer Background</div>
                        </div>
                        <label for="globalFooterFile" class="file-input-label">
                            <i class="bi bi-upload"></i> Choose footer image
                        </label>
                        <input id="globalFooterFile" class="homepage-media-input" name="site_footer" type="file" accept="image/*" data-preview="globalFooterPreview">
                        <div class="text-muted small mt-2">JPG, PNG, GIF, WEBP. Max 5MB. Darker store or product images work best.</div>
                    </div>
                </div>
                <div class="d-flex justify-content-end flex-wrap gap-2 mt-4">
                    <button type="submit" class="btn-update-hero"><i class="bi bi-save me-2"></i>Update Global Images</button>
                </div>
            </form>
            <div class="media-remove-actions">
                <form method="POST" action="{{ route('removeHeroMedia') }}" onsubmit="return confirm('Remove this page banner and restore the default template banner?');">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="id" value="{{ $pageBannerBg->id ?? 12 }}">
                    <input type="hidden" name="type" value="image">
                    <button type="submit" class="btn-remove-media"><i class="bi bi-trash"></i> Remove Page Banner</button>
                </form>
                <form method="POST" action="{{ route('removeHeroMedia') }}" onsubmit="return confirm('Remove this footer image and restore the default template footer?');">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="id" value="{{ $siteFooterBg->id ?? 13 }}">
                    <input type="hidden" name="type" value="image">
                    <button type="submit" class="btn-remove-media"><i class="bi bi-trash"></i> Remove Footer Image</button>
                </form>
            </div>
        </div>
    </div>

    <div class="card hero-card mb-4">
        <div class="card-body">
            <div class="card-title">
                <i class="bi bi-palette"></i> ONJECASA Store Favorites background
            </div>
            <div class="placement-note">
                <i class="bi bi-info-circle"></i>
                <div>Controls the background behind: ONJECASA Store Favorites / Everyday Products Ready For Pickup & Delivery. The overlay strength changes how dark or tinted that background appears.</div>
            </div>
            <form method="POST" action="{{ route('updateMenuFavoritesBackground') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" value="{{ $menuFavoritesBg->id ?? 11 }}">
                <div class="row g-4 align-items-start">
                    <div class="col-lg-6">
                        <label class="form-label-modern">Current background preview</label>
                        <div id="favoritesBgPreview" class="favorites-bg-preview" style="--overlay-color: {{ preg_match('/^#[0-9A-Fa-f]{6}$/', $menuFavoritesOverlayColor) ? $menuFavoritesOverlayColor : '#111111' }}; --overlay-opacity: {{ max(0, min(95, $menuFavoritesOverlayStrength)) / 100 }};">
                            <img id="menuFavoritesBgImage" src="{{ $menuFavoritesBg->image_url }}" alt="Store Favorites background preview">
                            <div class="favorites-bg-preview-text">ONJECASA Store Favorites</div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label-modern">Background image</label>
                        <label for="menuFavoritesBgFile" class="file-input-label">
                            <i class="bi bi-upload"></i> Choose background image
                        </label>
                        <input id="menuFavoritesBgFile" class="homepage-media-input" name="picture" type="file" accept="image/*" data-preview="menuFavoritesBgImage">
                        <div class="text-muted small mt-2 mb-4">JPG, PNG, GIF, WEBP. Max 5MB. Wide supermarket or product images work best.</div>

                        <label class="form-label-modern">Overlay strength</label>
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <input id="menuFavoritesOverlayRange" class="form-range" name="overlay_strength" type="range" min="0" max="95" value="{{ max(0, min(95, $menuFavoritesOverlayStrength)) }}">
                            <input id="menuFavoritesOverlayNumber" class="form-control-modern" type="number" min="0" max="95" value="{{ max(0, min(95, $menuFavoritesOverlayStrength)) }}" style="max-width: 96px;">
                            <span class="text-muted small">%</span>
                        </div>

                        <label class="form-label-modern">Overlay color</label>
                        <input id="menuFavoritesOverlayColor" class="color-input-modern" name="overlay_color" type="color" value="{{ preg_match('/^#[0-9A-Fa-f]{6}$/', $menuFavoritesOverlayColor) ? $menuFavoritesOverlayColor : '#111111' }}">

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <button type="submit" class="btn-update-hero"><i class="bi bi-save me-2"></i>Update Store Favorites Background</button>
                        </div>
                    </div>
                </div>
            </form>
            <div class="media-remove-actions">
                <form method="POST" action="{{ route('removeHeroMedia') }}" onsubmit="return confirm('Remove this background image and restore the default template background?');">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="id" value="{{ $menuFavoritesBg->id ?? 11 }}">
                    <input type="hidden" name="type" value="image">
                    <button type="submit" class="btn-remove-media"><i class="bi bi-trash"></i> Remove Background Image</button>
                </form>
            </div>
        </div>
    </div>

    <div class="hero-tabs">
        @foreach($sections as $section)
            <button type="button" class="hero-tab {{ $section['active'] ? 'active' : '' }}" data-tab="{{ $section['tab'] }}">
                {{ $section['tab_label'] }} <span class="section-badge">{{ $section['badge'] }}</span>
            </button>
        @endforeach
    </div>

    @foreach($sections as $section)
        @php($slide = $section['slide'])
        <div id="{{ $section['tab'] }}" class="hero-panel {{ $section['active'] ? 'active-panel' : '' }}">
            <div class="card hero-card">
                <div class="card-body">
                    <div class="card-title">
                        <i class="bi bi-image"></i> {{ $section['title'] }}
                    </div>
                    <div class="placement-note">
                        <i class="bi bi-info-circle"></i>
                        <div>{{ $section['note'] }}</div>
                    </div>

                    <form method="POST" action="{{ $section['route'] }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id" value="{{ $slide->id ?? '' }}">

                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="mb-4">
                                    <label class="form-label-modern">{{ $section['main_label'] }}</label>
                                    <input class="form-control-modern" type="text" name="main_header" value="{{ old('main_header', $slide->main_header ?? '') }}" placeholder="{{ $section['main_placeholder'] }}" required>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label-modern">{{ $section['small_label'] }}</label>
                                    <textarea class="form-control-modern" name="small_header" rows="4" placeholder="{{ $section['small_placeholder'] }}" required>{{ old('small_header', $slide->small_header ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label-modern">{{ $section['image_label'] }}</label>
                                <div class="image-preview-wrapper">
                                    <div class="media-preview-stack">
                                        <img id="{{ $section['image_id'] }}" class="preview-img" src="{{ $slide->image_url }}" alt="{{ $section['title'] }} image preview">
                                        <video id="{{ $section['video_id'] }}" class="preview-video {{ $slide->video_url ? '' : 'is-empty' }}" src="{{ $slide->video_url }}" controls muted playsinline></video>
                                    </div>
                                    <div>
                                        <label for="{{ $section['file_id'] }}" class="file-input-label">
                                            <i class="bi bi-upload"></i> Choose image
                                        </label>
                                        <input id="{{ $section['file_id'] }}" class="homepage-media-input" name="picture" type="file" accept="image/*" data-preview="{{ $section['image_id'] }}">
                                        <div class="text-muted small mt-2">JPG, PNG, GIF, WEBP. Max 5MB.</div>
                                        <label for="{{ $section['video_file_id'] }}" class="file-input-label mt-3">
                                            <i class="bi bi-camera-video"></i> Choose short video
                                        </label>
                                        <input id="{{ $section['video_file_id'] }}" class="homepage-media-input" name="video" type="file" accept="video/mp4,video/webm,video/quicktime,video/x-m4v" data-preview="{{ $section['video_id'] }}">
                                        <div class="text-muted small mt-2">MP4, WEBM, MOV, M4V. Max 50MB. Leave empty to keep current video.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn-update-hero"><i class="bi bi-save me-2"></i>{{ $section['button'] }}</button>
                        </div>
                    </form>

                    <div class="media-remove-actions">
                        <form method="POST" action="{{ route('removeHeroMedia') }}" onsubmit="return confirm('Remove this image and restore the default template image?');">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="id" value="{{ $slide->id ?? '' }}">
                            <input type="hidden" name="type" value="image">
                            <button type="submit" class="btn-remove-media"><i class="bi bi-trash"></i> Remove Image</button>
                        </form>
                        <form method="POST" action="{{ route('removeHeroMedia') }}" onsubmit="return confirm('Remove this uploaded video?');">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="id" value="{{ $slide->id ?? '' }}">
                            <input type="hidden" name="type" value="video">
                            <button type="submit" class="btn-remove-media"><i class="bi bi-camera-video-off"></i> Remove Video</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="text-center mt-4 text-muted">
        <i class="bi bi-info-circle me-1"></i> Changes are saved immediately and appear on the public homepage.
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.hero-tab').forEach(function(tab) {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.hero-tab').forEach(function(item) { item.classList.remove('active'); });
            document.querySelectorAll('.hero-panel').forEach(function(panel) { panel.classList.remove('active-panel'); });
            tab.classList.add('active');
            document.getElementById(tab.dataset.tab)?.classList.add('active-panel');
        });
    });

    document.querySelectorAll('input[type="file"][data-preview]').forEach(function(input) {
        var preview = document.getElementById(input.dataset.preview);
        if (!input || !preview) return;

        if (input.name === 'video') return;

        input.addEventListener('change', function(event) {
            var file = event.target.files && event.target.files[0] ? event.target.files[0] : null;
            if (!file) return;

            var reader = new FileReader();
            reader.onload = function(loadEvent) {
                preview.src = loadEvent.target.result;
            };
            reader.readAsDataURL(file);
        });
    });

    document.querySelectorAll('input[name="video"][data-preview]').forEach(function(input) {
        var preview = document.getElementById(input.dataset.preview);
        if (!input || !preview) return;

        input.addEventListener('change', function(event) {
            var file = event.target.files && event.target.files[0] ? event.target.files[0] : null;
            if (!file) return;

            preview.src = URL.createObjectURL(file);
            preview.classList.remove('is-empty');
            preview.load();
        });
    });

    (function() {
        var preview = document.getElementById('favoritesBgPreview');
        var range = document.getElementById('menuFavoritesOverlayRange');
        var number = document.getElementById('menuFavoritesOverlayNumber');
        var color = document.getElementById('menuFavoritesOverlayColor');
        if (!preview || !range || !number || !color) return;

        function syncOverlay(value) {
            var bounded = Math.max(0, Math.min(95, parseInt(value || 0, 10)));
            range.value = bounded;
            number.value = bounded;
            preview.style.setProperty('--overlay-opacity', String(bounded / 100));
        }

        range.addEventListener('input', function() { syncOverlay(range.value); });
        number.addEventListener('input', function() { syncOverlay(number.value); });
        color.addEventListener('input', function() {
            preview.style.setProperty('--overlay-color', color.value);
        });
    })();
</script>
@endpush




