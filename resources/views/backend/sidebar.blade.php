@php
  $navItem = function (array $routes) {
      return request()->routeIs(...$routes) ? 'active bg-gradient-primary' : '';
  };
@endphp

<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 bg-gradient-dark" id="sidenav-main">
  <style>
    #sidenav-main .navbar-collapse {
      height: calc(100vh - 190px);
      overflow-y: auto;
      padding-bottom: 1rem;
      scrollbar-width: thin;
      scrollbar-color: rgba(255,255,255,.26) transparent;
    }

    #sidenav-main .navbar-collapse::-webkit-scrollbar {
      width: 6px;
    }

    #sidenav-main .navbar-collapse::-webkit-scrollbar-thumb {
      background: rgba(255,255,255,.26);
      border-radius: 999px;
    }

    #sidenav-main .nav-link {
      margin-bottom: .125rem;
    }

    #sidenav-main .panel-switch-card {
      display: block;
      margin: .75rem 1rem 0;
      padding: .85rem .9rem;
      border-radius: .75rem;
      background: linear-gradient(195deg, #a42c4b 0%, #6b1730 100%);
      color: #fff;
      box-shadow: 0 10px 22px rgba(123, 23, 49, .28);
      font-weight: 700;
      line-height: 1.15;
    }

    #sidenav-main .panel-switch-card small {
      display: block;
      margin-top: .2rem;
      color: rgba(255,255,255,.75);
      font-size: .68rem;
      font-weight: 600;
    }
  </style>

  <div class="sidenav-header">
    <i class="material-icons p-3 cursor-pointer text-white opacity-5 position-absolute end-0 top-0 d-xl-none" id="iconSidenav">close</i>
    <a class="navbar-brand m-0" href="{{ route('admin.panel') }}">
      <span class="material-icons text-white me-2 align-middle">restaurant</span>
      <span class="ms-1 font-weight-bold text-white">ONJECASA</span>
    </a>
  </div>
  <hr class="horizontal light mt-0 mb-2">

  <a class="panel-switch-card" href="{{ route('pos.admin.dashboard') }}">
    <span class="material-icons text-white me-1 align-middle">point_of_sale</span>
    Switch to POS
    <small>In-store sales workspace</small>
  </a>

  <div class="collapse navbar-collapse w-auto max-height-vh-100" id="sidenav-collapse-main">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link text-white {{ $navItem(['admin.panel', 'admin.dashboard', 'dashboard']) }}" href="{{ route('admin.panel') }}">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="material-icons opacity-10">dashboard</i>
          </div>
          <span class="nav-link-text ms-1">Dashboard</span>
        </a>
      </li>

      <li class="nav-item mt-3">
        <h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Storefront</h6>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white {{ $navItem(['home_page', 'frontend.index']) }}" href="{{ route('home_page') }}">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="material-icons opacity-10">storefront</i>
          </div>
          <span class="nav-link-text ms-1">Homepage</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white {{ $navItem(['home_slide']) }}" href="{{ route('home_slide') }}">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="material-icons opacity-10">view_carousel</i>
          </div>
          <span class="nav-link-text ms-1">Home Slides</span>
        </a>
      </li>

      <li class="nav-item mt-3">
        <h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">products Catalog</h6>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white {{ $navItem(['categories.*']) }}" href="{{ route('categories.index') }}">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="material-icons opacity-10">category</i>
          </div>
          <span class="nav-link-text ms-1">Categories</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white {{ $navItem(['meal_extras.*']) }}" href="{{ route('meal_extras.index') }}">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="material-icons opacity-10">restaurant_menu</i>
          </div>
          <span class="nav-link-text ms-1">Add-ons & Basket Items</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white {{ $navItem(['add_product']) }}" href="{{ route('add_product') }}">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="material-icons opacity-10">add_box</i>
          </div>
          <span class="nav-link-text ms-1">Add Product</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white {{ $navItem(['view_product', 'editproduct']) }}" href="{{ route('view_product') }}">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="material-icons opacity-10">inventory_2</i>
          </div>
          <span class="nav-link-text ms-1">Manage products</span>
        </a>
      </li>

      <li class="nav-item mt-3">
        <h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Orders</h6>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white {{ $navItem(['admin_orders']) }}" href="{{ route('admin_orders') }}">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="material-icons opacity-10">receipt_long</i>
          </div>
          <span class="nav-link-text ms-1">New Orders</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white {{ $navItem(['all_orders']) }}" href="{{ route('all_orders') }}">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="material-icons opacity-10">analytics</i>
          </div>
          <span class="nav-link-text ms-1">All Orders</span>
        </a>
      </li>

      <li class="nav-item mt-3">
        <h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">In-Store POS</h6>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white {{ $navItem(['pos.admin.*']) }}" href="{{ route('pos.admin.dashboard') }}">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="material-icons opacity-10">point_of_sale</i>
          </div>
          <span class="nav-link-text ms-1">POS Admin</span>
        </a>
      </li>

      <li class="nav-item mt-3">
        <h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Content</h6>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white {{ $navItem(['admin.faqs.*']) }}" href="{{ route('admin.faqs.index') }}">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="material-icons opacity-10">help</i>
          </div>
          <span class="nav-link-text ms-1">FAQs</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white {{ $navItem(['admin.faqs.create']) }}" href="{{ route('admin.faqs.create') }}">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="material-icons opacity-10">add_circle</i>
          </div>
          <span class="nav-link-text ms-1">Add FAQ</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white {{ $navItem(['admin.contact.*']) }}" href="{{ route('admin.contact.edit') }}">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="material-icons opacity-10">contact_phone</i>
          </div>
          <span class="nav-link-text ms-1">Contact Settings</span>
        </a>
      </li>
    </ul>
  </div>

  <div class="sidenav-footer position-absolute w-100 bottom-0">
    <div class="mx-3">
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn bg-gradient-primary mt-4 w-100">
          <i class="material-icons opacity-10 align-middle me-1">logout</i>
          Logout
        </button>
      </form>
    </div>
  </div>
</aside>


