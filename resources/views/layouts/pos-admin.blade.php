<!doctype html>
<html lang="en" data-theme="light">
@php
  $globalLogoUrl = \App\Support\BrandAssets::logoUrl();
@endphp
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Admin - ONJECASA POS')</title>
  <link rel="icon" href="{{ $globalLogoUrl }}">
  <link rel="stylesheet" href="{{ asset('assets/adminhmd/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/adminhmd/vendors/bootstrap-icons/bootstrap-icons.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/adminhmd/css/style.css') }}?v={{ filemtime(public_path('assets/adminhmd/css/style.css')) }}">
  <link rel="stylesheet" href="{{ asset('assets/admin/vendor/@fortawesome/fontawesome-free/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin-mobile.css') }}?v={{ filemtime(public_path('css/admin-mobile.css')) }}">
  <style>
    .page-heading .h3, .page-heading h1 {
      margin: 0;
      font-size: 1.35rem;
      line-height: 1.2;
    }
    .page-heading .eyebrow {
      font-size: .68rem;
      line-height: 1.2;
    }
    .page-heading .text-muted {
      font-size: .84rem;
      line-height: 1.45;
    }
    .topbar-tools { flex-wrap: wrap; }
    .topbar-search { flex: 1 1 280px; width: auto; min-width: 220px; }
    .profile-button .avatar-img { border-radius: 50%; }
    html,
    body {
      max-width: 100%;
      overflow-x: hidden;
    }
    .admin-main {
      max-width: 100%;
      overflow-x: hidden;
    }
    .dashboard-content {
      width: 100%;
      max-width: 100%;
      min-width: 0;
      overflow-x: hidden;
      padding-bottom: 2rem;
    }
    .dashboard-content > .container-fluid {
      width: 100%;
      max-width: 100%;
      min-width: 0;
      overflow-x: hidden;
    }
    .dashboard-content .row,
    .dashboard-content [class*="col-"],
    .dashboard-content .card,
    .dashboard-content .table-responsive {
      min-width: 0;
      max-width: 100%;
    }
    .dashboard-content .table-responsive {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }
    .dashboard-content table {
      width: max-content;
      min-width: 100%;
    }
    .sidebar-user .avatar-img { object-fit: cover; }
    .brand-icon img { width: 100%; height: 100%; display: block; object-fit: cover; border-radius: 50%; }

    .admin-sidebar {
      background:
        radial-gradient(circle at top, rgba(148, 163, 184, 0.12), transparent 35%),
        linear-gradient(180deg, #030405 0%, #0b0d10 52%, #000000 100%);
      border-right: 1px solid rgba(148, 163, 184, 0.22);
      box-shadow: 18px 0 42px rgba(0, 0, 0, 0.28);
      color: #f8fafc;
    }

    .sidebar-header {
      padding: 1.25rem 1rem 1rem;
      border-bottom: 1px solid rgba(148, 163, 184, 0.18);
    }

    .brand-mark {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: .9rem;
      padding: 1rem .9rem 1.15rem;
      border-radius: 1.35rem;
      border: 1px solid rgba(148, 163, 184, 0.2);
      background: linear-gradient(135deg, rgba(18, 22, 27, 0.92), rgba(5, 6, 8, 0.86));
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
      color: #f8fafc;
    }

    .brand-mark:hover,
    .brand-mark:focus {
      color: #f8fafc;
    }

    .brand-icon {
      width: 5.5rem;
      height: 5.5rem;
      display: inline-grid;
      place-items: center;
      overflow: hidden;
      padding: 0;
      border: 3px solid rgba(203, 213, 225, 0.42);
      border-radius: 50%;
      background: transparent;
      box-shadow: 0 0 0 5px rgba(148, 163, 184, 0.14), 0 20px 40px -28px rgba(0, 0, 0, 0.78);
    }

    .brand-copy {
      display: grid;
      place-items: center;
      gap: .1rem;
      text-align: center;
    }

    .brand-copy .sidebar-identity-label {
      color: #e5e7eb;
      font-size: .72rem;
      font-weight: 800;
      letter-spacing: .18em;
      text-transform: uppercase;
    }

    .brand-title {
      font-family: Constantia, Georgia, serif;
      font-size: 1.1rem;
      font-weight: 600;
      letter-spacing: 0;
      color: #f8fafc;
    }

    .brand-status {
      display: inline-flex;
      align-items: center;
      gap: .45rem;
      margin-top: .35rem;
      color: #d1d5db;
      font-size: .78rem;
      font-weight: 600;
    }

    .brand-subtitle {
      font-size: .78rem;
      font-weight: 800;
      letter-spacing: .18em;
      text-transform: uppercase;
      color: #9ca3af;
    }

    .sidebar-nav {
      display: grid;
      gap: .9rem;
      padding: 1rem .9rem 1.2rem;
    }

    .admin-nav-group {
      overflow: hidden;
      border: 1px solid rgba(148, 163, 184, 0.16);
      border-radius: 1.25rem;
      background: rgba(18, 22, 27, 0.58);
    }

    .admin-nav-group[open] {
      background: rgba(18, 22, 27, 0.78);
      border-color: rgba(148, 163, 184, 0.24);
    }

    .admin-nav-group-summary {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: .75rem;
      padding: .95rem 1rem;
      cursor: pointer;
      list-style: none;
      user-select: none;
    }

    .admin-nav-group-summary::-webkit-details-marker {
      display: none;
    }

    .admin-nav-group-meta {
      display: flex;
      align-items: center;
      gap: .75rem;
      min-width: 0;
    }

    .admin-nav-group-symbol {
      width: 2.55rem;
      height: 2.55rem;
      display: inline-grid;
      place-items: center;
      border-radius: .95rem;
      border: 1px solid rgba(148, 163, 184, 0.18);
      background: linear-gradient(135deg, rgba(51, 65, 85, 0.72), rgba(15, 23, 42, 0.72));
      color: #f8fafc;
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
      flex: 0 0 auto;
    }

    .admin-nav-group-symbol svg {
      width: 1.05rem;
      height: 1.05rem;
    }

    .admin-nav-group-title {
      margin: 0;
      font-size: .78rem;
      font-weight: 800;
      letter-spacing: .18em;
      text-transform: uppercase;
      color: #f8fafc;
    }

    .admin-nav-group-source {
      display: block;
      margin-top: .15rem;
      color: #9ca3af;
      font-size: .68rem;
      font-weight: 800;
      letter-spacing: .12em;
      line-height: 1;
      text-transform: uppercase;
    }

    .admin-nav-group-count {
      display: inline-flex;
      align-items: center;
      border-radius: 999px;
      border: 1px solid rgba(148, 163, 184, 0.2);
      padding: .25rem .55rem;
      font-size: .68rem;
      font-weight: 800;
      color: #9ca3af;
      background: rgba(3, 4, 5, 0.72);
    }

    .admin-nav-group-icon {
      width: 1rem;
      height: 1rem;
      color: #94a3b8;
      transition: transform .18s ease, color .18s ease;
      flex: 0 0 auto;
    }

    .admin-nav-group[open] .admin-nav-group-icon {
      transform: rotate(180deg);
      color: #f8fafc;
    }

    .admin-nav-group-links {
      display: grid;
      gap: .6rem;
      padding: .9rem .9rem 1rem;
      border-top: 1px solid rgba(246, 205, 112, 0.14);
    }

    .admin-nav-link {
      display: flex;
      align-items: center;
      gap: .75rem;
      padding: .8rem .95rem;
      border-radius: .95rem;
      border: 1px solid transparent;
      background: rgba(18, 22, 27, 0.46);
      color: #d1d5db;
      font-size: .92rem;
      font-weight: 700;
      transition: transform .16s ease, background .16s ease, border-color .16s ease, color .16s ease;
    }

    .admin-nav-link:hover {
      color: #f8fafc;
      background: rgba(31, 41, 55, 0.84);
      border-color: rgba(148, 163, 184, 0.22);
      transform: translateX(2px);
    }

    .admin-nav-link-active {
      color: #f8fafc;
      background: linear-gradient(135deg, rgba(51, 65, 85, 0.82), rgba(15, 23, 42, 0.82));
      border-color: rgba(246, 205, 112, 0.36);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06);
    }

    .panel-switch-card {
      display: flex;
      align-items: center;
      gap: .75rem;
      margin: .95rem .9rem 0;
      padding: .9rem .95rem;
      border-radius: 1rem;
      border: 1px solid rgba(148, 163, 184, .25);
      background: linear-gradient(135deg, rgba(31, 41, 55, .86), rgba(3, 4, 5, .86));
      color: #f8fafc;
      font-weight: 800;
    }

    .panel-switch-card:hover,
    .panel-switch-card:focus {
      color: #f8fafc;
      transform: translateX(2px);
    }

    .panel-switch-card span {
      display: block;
      color: #f8fafc;
      font-size: .92rem;
      line-height: 1.1;
    }

    .panel-switch-card small {
      display: block;
      margin-top: .18rem;
      color: #9ca3af;
      font-size: .72rem;
      font-weight: 700;
    }

    .sidebar-user {
      margin: 0 1rem 1rem;
      padding: 1rem;
      display: grid;
      justify-items: center;
      gap: .25rem;
      text-align: center;
      border: 1px solid rgba(148, 163, 184, 0.16);
      border-radius: 1.15rem;
      background: rgba(18, 22, 27, 0.72);
    }

    .sidebar-user strong {
      color: #f8fafc;
      font-size: 1rem;
      line-height: 1.1;
    }

    .sidebar-user small {
      color: #9ca3af;
      font-size: .84rem;
    }

    .sidebar-footer {
      display: flex;
      align-items: center;
      gap: .65rem;
      margin-top: auto;
      margin-inline: 1rem;
      padding: 1rem 0;
      border-top: 1px solid rgba(148, 163, 184, 0.18);
      color: #d1d5db;
      font-size: .9rem;
      white-space: nowrap;
    }

    .admin-main {
      margin-left: 18rem;
      min-height: 100vh;
      width: calc(100% - 18rem);
    }

    body.sidebar-mini .admin-main {
      margin-left: 84px;
      width: calc(100% - 84px);
    }

    body.sidebar-mini .admin-sidebar {
      width: 84px;
    }

    body.sidebar-mini .sidebar-header,
    body.sidebar-mini .sidebar-footer {
      margin-inline: .6rem;
      padding-inline: 0;
    }

    body.sidebar-mini .brand-mark {
      padding: .85rem .55rem;
      gap: .6rem;
    }

    body.sidebar-mini .brand-icon {
      width: 3.9rem;
      height: 3.9rem;
    }

    body.sidebar-mini .brand-copy,
    body.sidebar-mini .admin-nav-group-title,
    body.sidebar-mini .admin-nav-group-source,
    body.sidebar-mini .admin-nav-group-count,
    body.sidebar-mini .admin-nav-group-icon,
    body.sidebar-mini .panel-switch-card div,
    body.sidebar-mini .sidebar-status-text,
    body.sidebar-mini .sidebar-footer-text {
      display: none;
    }

    body.sidebar-mini .sidebar-nav {
      gap: .6rem;
      padding-inline: .6rem;
    }

    body.sidebar-mini .admin-nav-group-summary {
      justify-content: center;
      padding: .8rem .55rem;
    }

    body.sidebar-mini .admin-nav-group-meta {
      justify-content: center;
    }

    body.sidebar-mini .admin-nav-group-symbol {
      width: 2.8rem;
      height: 2.8rem;
    }

    body.sidebar-mini .admin-nav-group-links {
      display: none;
    }

    body.sidebar-mini .admin-nav-group {
      border-radius: 1rem;
    }

    .page-heading .page-icon {
      background: #1f2937;
      color: #e5e7eb;
    }

    .page-heading .section-title i {
      background: #1f2937;
      color: #e5e7eb;
    }

    .source-banner {
      display: flex;
      align-items: flex-start;
      gap: .9rem;
      margin-bottom: 1rem;
      padding: .95rem 1rem;
      border: 1px solid #dbe4ef;
      border-radius: 8px;
      background: #fff;
      box-shadow: 0 12px 28px rgba(0, 0, 0, .1);
    }

    .source-banner-icon {
      width: 2.45rem;
      height: 2.45rem;
      display: inline-grid;
      place-items: center;
      border-radius: 8px;
      flex: 0 0 auto;
    }

    .source-banner strong,
    .source-banner span {
      display: block;
    }

    .source-banner strong {
      color: #111827;
      line-height: 1.1;
    }

    .source-banner span {
      margin-top: .18rem;
      color: #64748b;
      font-size: .88rem;
      line-height: 1.4;
    }

    .source-banner-website .source-banner-icon {
      background: #fef3c7;
      color: #b45309;
    }

    .source-banner-pos .source-banner-icon {
      background: #1f2937;
      color: #e5e7eb;
    }

    .pos-order-fab {
      position: fixed;
      left: calc(18rem + 24px);
      bottom: 24px;
      z-index: 9998;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: .55rem;
      min-height: 56px;
      padding: .85rem 1.05rem;
      border: 1px solid rgba(148, 163, 184, .3);
      border-radius: 999px;
      background: linear-gradient(135deg, #1f2937, #030405);
      color: #f8fafc;
      font-weight: 800;
      line-height: 1;
      text-decoration: none;
      box-shadow: 0 18px 38px rgba(0, 0, 0, .32);
      transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
    }

    .pos-order-fab:hover,
    .pos-order-fab:focus {
      color: #f8fafc;
      background: linear-gradient(135deg, #374151, #050608);
      box-shadow: 0 22px 44px rgba(0, 0, 0, .42);
      text-decoration: none;
      transform: translateY(-2px);
    }

    .pos-order-fab i {
      font-size: 1.15rem;
    }

    body.sidebar-mini .pos-order-fab {
      left: calc(84px + 24px);
    }

    @media (min-width: 1024px) {
      .admin-sidebar {
        width: 18rem;
      }

      .admin-main {
        width: calc(100% - 18rem);
        max-width: calc(100% - 18rem);
        margin-left: 18rem;
      }

      .admin-sidebar-nav {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        overscroll-behavior: contain;
      }
    }

    @media (max-width: 991.98px) {
      .admin-sidebar {
        width: min(18rem, calc(100vw - 48px));
        transform: translateX(-100%);
      }

      .admin-main {
        margin-left: 0;
        width: 100%;
        max-width: 100%;
      }

      .pos-order-fab {
        left: 16px;
        bottom: 16px;
        min-height: 54px;
        padding: .8rem .95rem;
      }

      body.sidebar-open {
        overflow: hidden;
      }

      body.sidebar-open .admin-sidebar {
        transform: translateX(0);
      }

      body.sidebar-open .sidebar-backdrop {
        display: block;
      }
    }

    .admin-sidebar {
      background:
        radial-gradient(circle at top, rgba(148, 163, 184, 0.12), transparent 35%),
        linear-gradient(180deg, #030405 0%, #0b0d10 52%, #000000 100%);
      border-right-color: rgba(148, 163, 184, 0.22);
      box-shadow: 18px 0 42px rgba(0, 0, 0, 0.28);
      color: #f8fafc;
    }

    .brand-mark,
    .brand-mark:hover,
    .brand-mark:focus,
    .brand-copy .sidebar-identity-label,
    .brand-title,
    .admin-nav-group-title,
    .admin-nav-link:hover,
    .admin-nav-link-active,
    .sidebar-user strong {
      color: #f8fafc;
    }

    .brand-subtitle,
    .brand-status,
    .admin-nav-group-source,
    .admin-nav-group-count,
    .sidebar-user small,
    .sidebar-footer,
    .panel-switch-card small {
      color: #e5e7eb;
    }

    .brand-mark,
    .admin-nav-group,
    .sidebar-user {
      border-color: rgba(148, 163, 184, 0.2);
      background: rgba(18, 22, 27, 0.72);
    }

    .admin-nav-group-symbol {
      border-color: rgba(148, 163, 184, 0.22);
      background: linear-gradient(135deg, rgba(51, 65, 85, 0.82), rgba(15, 23, 42, 0.82));
      color: #f8fafc;
    }

    .admin-nav-link {
      background: rgba(18, 22, 27, 0.5);
      color: #d1d5db;
    }

    .admin-nav-link:hover,
    .admin-nav-link-active {
      background: linear-gradient(135deg, rgba(51, 65, 85, 0.82), rgba(3, 4, 5, 0.82));
      border-color: rgba(148, 163, 184, 0.32);
    }

    .panel-switch-card,
    .pos-order-fab {
      border-color: rgba(148, 163, 184, 0.3);
      background: linear-gradient(135deg, #1f2937, #030405);
      color: #f8fafc;
      box-shadow: 0 18px 38px rgba(0, 0, 0, 0.34);
    }

    .page-heading .page-icon,
    .page-heading .section-title i,
    .source-banner-pos .source-banner-icon {
      background: #1f2937;
      color: #e5e7eb;
    }

    .source-banner-website .source-banner-icon {
      background: #1f2937;
      color: #e5e7eb;
    }

    @media (max-width: 575.98px) {
      .admin-nav-group-summary,
      .admin-nav-link,
      .sidebar-user,
      .brand-mark {
        border-radius: .9rem;
      }

      .brand-icon {
        width: 4.9rem;
        height: 4.9rem;
      }

      .pos-order-fab span {
        display: none;
      }

      .pos-order-fab {
        width: 54px;
        padding-inline: 0;
      }
    }
  </style>
  @stack('styles')
</head>
@php
  $user = auth()->user();
  $userName = $user?->name ?? 'Admin';
  $userEmail = $user?->email ?? 'admin@example.com';
  $userAvatar = asset('assets/adminhmd/images/avatar/avatar.jpg');
  $unreadCount = $user ? $user->unreadNotifications()->count() : 0;
  $recentNotifications = $user ? $user->unreadNotifications()->latest()->take(5)->get() : collect();
  $systemName = \Illuminate\Support\Facades\DB::table('pos_settings')->where('key', 'system_name')->value('value') ?? 'ONJECASA POS';
  $sidebarLogo = \Illuminate\Support\Facades\DB::table('pos_settings')->where('key', 'sidebar_logo')->value('value');
  $sidebarLogoUrl = $globalLogoUrl;
  $availableBranches = \App\Support\BranchContext::availableBranches($user);
  $activeBranch = \App\Support\BranchContext::active();

  $navLinkClass = function (string $pattern): string {
      return 'admin-nav-link'.(request()->routeIs($pattern) ? ' admin-nav-link-active' : '');
  };

  $navGroupIsActive = function (array $group): bool {
      foreach ($group['links'] as $link) {
          if (! empty($link['pattern']) && request()->routeIs($link['pattern'])) {
              return true;
          }
      }

      return false;
  };

  $isPosAdminRoute = request()->routeIs('pos.admin.*');
  $isSuperAdminRoute = request()->routeIs('superadmin.*');

  $websiteNavGroups = [
      [
          'label' => 'Website Admin',
          'icon' => 'website',
          'source' => 'Storefront',
          'links' => [
              [
                  'label' => 'Website Dashboard',
                  'route' => route('admin.panel'),
                  'pattern' => 'admin.panel',
              ],
              [
                  'label' => 'Homepage Slides',
                  'route' => route('home_slide'),
                  'pattern' => 'home_slide',
              ],
              [
                  'label' => 'Website Categories',
                  'route' => route('categories.index'),
                  'pattern' => 'categories.*',
              ],
              [
                  'label' => 'Add-ons & Basket Items',
                  'route' => route('meal_extras.index'),
                  'pattern' => 'meal_extras.*',
              ],
              [
                  'label' => 'Website Products',
                  'route' => route('view_product'),
                  'pattern' => 'view_product',
              ],
              [
                  'label' => 'Add Website Product',
                  'route' => route('add_product'),
                  'pattern' => 'add_product',
              ],
          ],
      ],
      [
          'label' => 'Website Orders',
          'icon' => 'website',
          'source' => 'Storefront',
          'links' => [
              [
                  'label' => 'New Online Orders',
                  'route' => route('admin_orders'),
                  'pattern' => 'admin_orders',
              ],
              [
                  'label' => 'All Online Orders',
                  'route' => route('all_orders'),
                  'pattern' => 'all_orders',
              ],
              [
                  'label' => 'FAQs',
                  'route' => route('admin.faqs.index'),
                  'pattern' => 'admin.faqs.*',
              ],
              [
                  'label' => 'Contact Settings',
                  'route' => route('admin.contact.edit'),
                  'pattern' => 'admin.contact.*',
              ],
          ],
      ],
  ];

  $posNavGroups = [
      [
          'label' => 'POS Overview',
          'icon' => 'overview',
          'source' => 'In-Store',
          'links' => [
              [
                  'label' => 'Dashboard',
                  'route' => route('pos.admin.dashboard'),
                  'pattern' => 'pos.admin.dashboard',
              ],
              [
                  'label' => 'AI Assistant',
                  'route' => route('pos.admin.ai.index'),
                  'pattern' => 'pos.admin.ai.*',
              ],
          ],
      ],
      [
          'label' => 'POS Inventory',
          'icon' => 'editorial',
          'source' => 'In-Store',
          'links' => [
              [
                  'label' => 'Products',
                  'route' => route('pos.admin.products.index'),
                  'pattern' => 'pos.admin.products.*',
              ],
              [
                  'label' => 'Categories',
                  'route' => route('pos.admin.categories.index'),
                  'pattern' => 'pos.admin.categories.*',
              ],
          ],
      ],
      [
          'label' => 'POS Users',
          'icon' => 'users',
          'source' => 'In-Store',
          'links' => [
              [
                  'label' => 'System Users',
                  'route' => route('pos.admin.users.index'),
                  'pattern' => 'pos.admin.users.*',
              ],
              [
                  'label' => 'Cashiers',
                  'route' => route('pos.admin.staff.index'),
                  'pattern' => 'pos.admin.staff.*',
              ],
          ],
      ],
      [
          'label' => 'POS Operations',
          'icon' => 'broadcast',
          'source' => 'In-Store',
          'links' => [
              [
                  'label' => 'Orders',
                  'route' => route('pos.admin.orders.index'),
                  'pattern' => 'pos.admin.orders.*',
              ],
              [
                  'label' => 'Online Orders',
                  'route' => route('pos.admin.online-orders.index'),
                  'pattern' => 'pos.admin.online-orders.*',
              ],
              [
                  'label' => 'Payments',
                  'route' => route('pos.admin.payments.index'),
                  'pattern' => 'pos.admin.payments.*',
              ],
              [
                  'label' => 'Receipts',
                  'route' => route('pos.admin.receipts.index'),
                  'pattern' => 'pos.admin.receipts.*',
              ],
          ],
      ],
      [
          'label' => 'POS Reports',
          'icon' => 'presentation',
          'source' => 'In-Store',
          'links' => [
              [
                  'label' => 'Orders Reports',
                  'route' => route('pos.admin.orders-reports.index'),
                  'pattern' => 'pos.admin.orders-reports.*',
              ],
              [
                  'label' => 'Payments Reports',
                  'route' => route('pos.admin.payments-reports.index'),
                  'pattern' => 'pos.admin.payments-reports.*',
              ],
              [
                  'label' => 'Sales',
                  'route' => route('pos.admin.sales.index'),
                  'pattern' => 'pos.admin.sales.*',
              ],
              [
                  'label' => 'Audit Trail',
                  'route' => route('pos.admin.audit-trails.index'),
                  'pattern' => 'pos.admin.audit-trails.*',
              ],
          ],
      ],
      [
          'label' => 'POS System',
          'icon' => 'system',
          'source' => 'In-Store',
          'links' => [
              [
                  'label' => 'Settings',
                  'route' => route('pos.admin.settings.index'),
                  'pattern' => 'pos.admin.settings.*',
              ],
          ],
      ],
  ];

  $superadminNavGroups = [
      [
          'label' => 'Superadmin',
          'icon' => 'system',
          'source' => 'Full Control',
          'links' => [
              [
                  'label' => 'Command Center',
                  'route' => route('superadmin.dashboard'),
                  'pattern' => 'superadmin.dashboard',
              ],
              [
                  'label' => 'Homepage',
                  'route' => route('home_slide'),
                  'pattern' => 'home_slide',
              ],
              [
                  'label' => 'Website Admin',
                  'route' => route('admin.panel'),
                  'pattern' => 'admin.panel',
              ],
              [
                  'label' => 'Branches',
                  'route' => route('superadmin.branches'),
                  'pattern' => 'superadmin.branches',
              ],
              [
                  'label' => 'Users & Roles',
                  'route' => route('superadmin.users'),
                  'pattern' => 'superadmin.users',
              ],
              [
                  'label' => 'Audit Logs',
                  'route' => route('superadmin.audit'),
                  'pattern' => 'superadmin.audit',
              ],
              [
                  'label' => 'Security Center',
                  'route' => route('superadmin.security'),
                  'pattern' => 'superadmin.security',
              ],
              [
                  'label' => 'System Settings',
                  'route' => route('superadmin.settings'),
                  'pattern' => 'superadmin.settings',
              ],
              [
                  'label' => 'Maintenance',
                  'route' => route('superadmin.maintenance'),
                  'pattern' => 'superadmin.maintenance',
              ],
          ],
      ],
  ];

  $navGroups = $isSuperAdminRoute ? $superadminNavGroups : ($isPosAdminRoute ? $posNavGroups : $websiteNavGroups);
@endphp
<body>
  <x-shared.mouse-trail />
  <div class="admin-shell">
    <div class="sidebar-backdrop" data-sidebar-close></div>

    <aside class="admin-sidebar" id="adminSidebar" aria-label="Main navigation">
      <div class="sidebar-header">
        <a class="brand-mark" href="{{ $isSuperAdminRoute ? route('superadmin.dashboard') : route('pos.admin.dashboard') }}" aria-label="{{ $systemName }} dashboard">
          <span class="brand-icon">
            <img src="{{ $sidebarLogoUrl }}" alt="{{ $systemName }}">
          </span>
          <span class="brand-copy">
            <span class="brand-title">{{ $systemName }}</span>
            <span class="brand-status">
              <span class="status-dot"></span>
              <span>Command center online</span>
            </span>
          </span>
        </a>
      </div>

      @if($isSuperAdminRoute)
        <a class="panel-switch-card" href="{{ route('pos.admin.dashboard') }}">
          <i class="bi bi-arrow-left-right fs-5"></i>
          <div>
            <span>Switch to POS Admin</span>
            <small>Walk-in sales, cashiers, receipts</small>
          </div>
        </a>
        <a class="panel-switch-card" href="{{ route('admin.panel') }}">
          <i class="bi bi-globe2 fs-5"></i>
          <div>
            <span>Switch to Online Admin</span>
            <small>Storefront orders and website catalog</small>
          </div>
        </a>
      @elseif($isPosAdminRoute)
        <a class="panel-switch-card" href="{{ route('admin.panel') }}">
          <i class="bi bi-arrow-left-right fs-5"></i>
          <div>
            <span>Switch to Online Admin</span>
            <small>Orders, storefront, catalog</small>
          </div>
        </a>
      @else
        <a class="panel-switch-card" href="{{ route('pos.admin.dashboard') }}">
          <i class="bi bi-arrow-left-right fs-5"></i>
          <div>
            <span>Switch to POS Admin</span>
            <small>Walk-in sales, cashiers, receipts</small>
          </div>
        </a>
      @endif

      <nav class="sidebar-nav">
        @foreach ($navGroups as $group)
          @continue(empty($group['links']))
          <details class="admin-nav-group" @if ($navGroupIsActive($group)) open @endif>
            <summary class="admin-nav-group-summary">
              <div class="admin-nav-group-meta">
                <span class="admin-nav-group-symbol" aria-hidden="true">
                  @switch($group['icon'])
                    @case('website')
                      <svg viewBox="0 0 24 24" fill="none">
                        <path d="M4 7.75A2.75 2.75 0 0 1 6.75 5h10.5A2.75 2.75 0 0 1 20 7.75v8.5A2.75 2.75 0 0 1 17.25 19H6.75A2.75 2.75 0 0 1 4 16.25v-8.5Z" stroke="currentColor" stroke-width="1.8"/>
                        <path d="M4.5 9h15M8 13h4M8 16h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                      </svg>
                      @break
                    @case('overview')
                      <svg viewBox="0 0 24 24" fill="none">
                        <path d="M5 12h5V5H5v7ZM14 19h5v-7h-5v7ZM14 10h5V5h-5v5ZM5 19h5v-5H5v5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                      </svg>
                      @break
                    @case('editorial')
                      <svg viewBox="0 0 24 24" fill="none">
                        <path d="M6 5.75A2.75 2.75 0 0 1 8.75 3h7.5A2.75 2.75 0 0 1 19 5.75v12.5A2.75 2.75 0 0 1 16.25 21h-7.5A2.75 2.75 0 0 1 6 18.25V5.75Z" stroke="currentColor" stroke-width="1.8"/>
                        <path d="M9 8h6M9 12h6M9 16h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                      </svg>
                      @break
                    @case('broadcast')
                      <svg viewBox="0 0 24 24" fill="none">
                        <path d="M12 18a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.8"/>
                        <path d="M7.76 7.76a6 6 0 0 1 8.48 0M5.64 5.64a9 9 0 0 1 12.72 0M3.52 3.52a12 12 0 0 1 16.96 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                      </svg>
                      @break
                    @case('presentation')
                      <svg viewBox="0 0 24 24" fill="none">
                        <path d="M4 7.75A2.75 2.75 0 0 1 6.75 5h10.5A2.75 2.75 0 0 1 20 7.75v5.5A2.75 2.75 0 0 1 17.25 16H6.75A2.75 2.75 0 0 1 4 13.25v-5.5Z" stroke="currentColor" stroke-width="1.8"/>
                        <path d="M12 16v3M8.5 19h7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                      </svg>
                      @break
                    @case('users')
                      <svg viewBox="0 0 24 24" fill="none">
                        <path d="M16.5 19.5v-1.2a3.3 3.3 0 0 0-3.3-3.3H10.8a3.3 3.3 0 0 0-3.3 3.3v1.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M12 12.2a3.2 3.2 0 1 0 0-6.4 3.2 3.2 0 0 0 0 6.4Z" stroke="currentColor" stroke-width="1.8"/>
                        <path d="M18.8 19.5v-1a2.7 2.7 0 0 0-1.9-2.6M15.9 6.6a3.1 3.1 0 0 1 0 6.1" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                      </svg>
                      @break
                    @case('system')
                      <svg viewBox="0 0 24 24" fill="none">
                        <path d="M10.8 4.36a1 1 0 0 1 2.4 0l.22 1.16a1 1 0 0 0 .82.79l1.2.18a1 1 0 0 1 .56 1.7l-.86.85a1 1 0 0 0-.29.88l.21 1.2a1 1 0 0 1-1.45 1.05L12.56 12a1 1 0 0 0-.93 0l-1.05.55a1 1 0 0 1-1.45-1.05l.2-1.2a1 1 0 0 0-.28-.88l-.87-.86a1 1 0 0 1 .56-1.69l1.2-.19a1 1 0 0 0 .82-.78l.24-1.19Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        <path d="M12 15.5v4M7 18.5h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                      </svg>
                      @break
                  @endswitch
                </span>
                <div>
                  <p class="admin-nav-group-title">{{ $group['label'] }}</p>
                  @if(! empty($group['source']))
                    <span class="admin-nav-group-source">{{ $group['source'] }}</span>
                  @endif
                </div>
                <span class="admin-nav-group-count">{{ count($group['links']) }}</span>
              </div>
              <svg class="admin-nav-group-icon" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <path d="M6 8l4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </summary>

            <div class="admin-nav-group-links">
              @foreach ($group['links'] as $link)
                @continue(isset($link['visible']) && ! $link['visible'])
                <a class="{{ $link['pattern'] ? $navLinkClass($link['pattern']) : 'admin-nav-link' }}" href="{{ $link['route'] }}">
                  {{ $link['label'] }}
                </a>
              @endforeach
            </div>
          </details>
        @endforeach
      </nav>

    </aside>

    <div class="admin-main">
      <nav class="navbar admin-navbar navbar-expand bg-white">
        <div class="container-fluid px-3 px-lg-4">
          <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-controls="adminSidebar" aria-expanded="true" aria-label="Toggle sidebar">
            <span></span>
            <span></span>
            <span></span>
          </button>

          <form class="d-none d-md-flex ms-3 flex-grow-1" role="search">
            <input class="form-control search-input" type="search" placeholder="Search products, orders, reports" aria-label="Search">
          </form>

          <div class="navbar-actions ms-auto">
            @if($availableBranches->isNotEmpty())
              <form method="POST" action="{{ route('branches.switch') }}" class="admin-branch-switch-form me-lg-2">
                @csrf
                <label class="small fw-bold text-muted mb-0" for="adminBranchSwitch">Branch</label>
                <select id="adminBranchSwitch" name="branch_id" class="form-select form-select-sm" style="min-width: 170px;" onchange="this.form.submit()">
                  @if(\App\Support\BranchContext::canUseOverall($user))
                    <option value="overall" @selected(\App\Support\BranchContext::isOverall())>Overall</option>
                  @endif
                  @foreach($availableBranches as $branch)
                    <option value="{{ $branch->id }}" @selected(! \App\Support\BranchContext::isOverall() && $activeBranch && (int) $activeBranch->id === (int) $branch->id)>{{ $branch->name }}</option>
                  @endforeach
                </select>
              </form>
            @endif
            <div class="dropdown">
              <button class="icon-button" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications" title="Notifications">
                <i class="bi bi-bell" aria-hidden="true"></i>
                @if($unreadCount > 0)
                  <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $unreadCount }}</span>
                @endif
              </button>
              <ul class="dropdown-menu dropdown-menu-end profile-menu">
                <li>
                  <div class="d-flex align-items-center justify-content-between px-2 pb-2">
                    <strong class="small">Notifications</strong>
                    @if($unreadCount > 0)
                      <form action="{{ route('admin.notifications.read') }}" method="POST" class="m-0">
                        @csrf
                        <button class="btn btn-link btn-sm p-0 text-decoration-none" type="submit">Mark read</button>
                      </form>
                    @endif
                  </div>
                </li>
                @forelse($recentNotifications as $notification)
                  <li>
                    <a class="dropdown-item rounded-3 py-2" href="{{ route('admin_orders') }}">
                      <strong class="d-block small">{{ $notification->data['title'] ?? 'Order update' }}</strong>
                      <span class="d-block text-muted small">{{ \Illuminate\Support\Str::limit($notification->data['message'] ?? 'New notification', 74) }}</span>
                    </a>
                  </li>
                @empty
                  <li><span class="dropdown-item-text text-muted small">No notifications yet.</span></li>
                @endforelse
              </ul>
            </div>
            <button class="icon-button theme-toggle" type="button" data-theme-toggle aria-label="Switch color theme" title="Switch color theme">
              <i class="bi bi-moon-stars" data-theme-icon aria-hidden="true"></i>
            </button>

            <div class="dropdown">
              <button class="profile-button dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img class="avatar-img avatar-sm" src="{{ $userAvatar }}" alt="{{ $userName }}">
                <span class="profile-name d-none d-sm-inline">{{ $userName }}</span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end profile-menu">
                <li>
                  <div class="profile-menu-card">
                    <img class="avatar-img avatar-sm profile-menu-avatar" src="{{ $userAvatar }}" alt="{{ $userName }}">
                    <div>
                      <strong>{{ $userName }}</strong>
                      <span>{{ $userEmail }}</span>
                    </div>
                  </div>
                </li>
                <li><hr class="dropdown-divider profile-menu-divider"></li>
                <li>
                  <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="dropdown-item profile-menu-logout" type="submit">
                      <i class="bi bi-box-arrow-right"></i>
                      <span>Sign out</span>
                    </button>
                  </form>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </nav>

      <main class="dashboard-content">
        <div class="container-fluid px-3 px-lg-4 py-4">
          <div class="page-heading">
            <div class="page-heading-copy">
              <span class="page-icon"><i class="@yield('page-icon', 'bi bi-speedometer2')" aria-hidden="true"></i></span>
              <div>
                <p class="eyebrow mb-1">@yield('page-eyebrow', 'Overview')</p>
                <h1 class="h3 mb-1">@yield('page-title', 'Dashboard')</h1>
                <p class="text-muted mb-0">@yield('page-description', 'Monitor sales, stock, orders, and operations from one workspace.')</p>
              </div>
            </div>
            <div class="heading-actions">
              @hasSection('page-actions')
                @yield('page-actions')
              @else
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('pos.admin.orders.index') }}"><i class="bi bi-arrow-repeat"></i> Refresh</a>
                <a class="btn btn-primary btn-sm" href="{{ route('pos.admin.orders.create') }}"><i class="bi bi-plus-circle"></i> New Order</a>
              @endif
            </div>
          </div>

          @if(request()->routeIs('pos.admin.*'))
            <div class="source-banner source-banner-pos">
              <span class="source-banner-icon"><i class="bi bi-shop-window"></i></span>
              <div>
                <strong>In-Store POS Admin</strong>
                <span>These screens control walk-in sales, POS inventory, cashier accounts, receipts, payments, and in-store reports.</span>
              </div>
            </div>
          @endif

          @yield('content')
        </div>
      </main>
    </div>
  </div>

  @include('components.admin-ai-assistant')

  @if(request()->routeIs('pos.admin.*'))
    <a class="pos-order-fab" href="{{ route('pos.admin.orders.create') }}" aria-label="Create POS order">
      <i class="bi bi-cart-plus" aria-hidden="true"></i>
      <span>New Order</span>
    </a>
  @endif

  <div class="toast-stack">
    @if(session('success'))
      <div class="toast-note success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
      <div class="toast-note error">{{ $errors->first() }}</div>
    @endif
  </div>

  <script>
    window.adminHMDUser = {
      name: @json($userName),
      avatar: @json($userAvatar)
    };
  </script>
  <script src="{{ asset('assets/adminhmd/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/adminhmd/js/main.js') }}"></script>
  <script src="{{ asset('assets/admin/vendor/@fortawesome/fontawesome-free/js/all.min.js') }}"></script>
  @include('components.notification-sound')
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('.dashboard-content table').forEach((table) => {
        if (table.closest('.table-responsive')) return;

        const wrapper = document.createElement('div');
        wrapper.className = 'table-responsive';
        table.parentNode.insertBefore(wrapper, table);
        wrapper.appendChild(table);
      });
    });

    document.addEventListener('input', (event) => {
      const input = event.target;
      if (!input.matches('[data-table-filter]')) return;

      const selector = input.getAttribute('data-table-filter');
      const table = document.querySelector(selector);
      if (!table) return;

      const term = input.value.trim().toLowerCase();
      const rows = table.querySelectorAll('tbody tr[data-filter-row]');
      const emptyRow = table.querySelector('tbody tr[data-filter-empty]');
      let visibleCount = 0;

      rows.forEach((row) => {
        const match = !term || row.textContent.toLowerCase().includes(term);
        row.style.display = match ? '' : 'none';
        if (match) visibleCount += 1;
      });

      if (emptyRow) {
        emptyRow.style.display = visibleCount === 0 ? '' : 'none';
      }
    });
  </script>
  @stack('scripts')
</body>
</html>







