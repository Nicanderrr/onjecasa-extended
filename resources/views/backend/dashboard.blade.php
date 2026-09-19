@extends('backend.admin')

@section('title', 'Website Dashboard - ONJECASA')
@section('page-icon', 'bi bi-globe2')
@section('page-eyebrow', 'Storefront')
@section('page-title', 'Website Dashboard')
@section('page-description', 'Monitor online catalog, storefront orders, customer activity, and website content.')

@section('admin')
@php
  $products = \App\Models\ProductPage::latest()->take(5)->get();
  $recentOrders = \App\Models\OrderProduct::latest()->take(8)->get();
  $totalProducts = \App\Models\ProductPage::count();
  $totalCategories = \App\Models\Category::count();
  $totalUsers = $totalUsers ?? \App\Models\User::count();
  $orderRevenue = \App\Models\OrderProduct::sum('total');
  $dashboardSales = $orderRevenue > 0 ? $orderRevenue : ($totalSales ?? 0);
  $pendingOrders = \App\Models\OrderProduct::where('status', 'pending')->count();
  $completedOrders = \App\Models\OrderProduct::whereIn('status', ['completed', 'paid'])->count();

  $salesLabels = [];
  $salesSeries = [];
  for ($i = 6; $i >= 0; $i--) {
      $date = now()->subDays($i);
      $salesLabels[] = $date->format('M j');
      $salesSeries[] = (float) \App\Models\OrderProduct::whereDate('created_at', $date->toDateString())->sum('total');
  }

  $categoryData = \App\Models\ProductPage::select('description')
      ->selectRaw('COUNT(*) as total')
      ->groupBy('description')
      ->orderByDesc('total')
      ->limit(5)
      ->get();

  $categoryLabels = $categoryData->pluck('description')->map(fn ($label) => $label ?: 'Uncategorized')->values();
  $categorySeries = $categoryData->pluck('total')->map(fn ($value) => (int) $value)->values();
@endphp

<section class="row g-3 mt-1 dashboard-metrics" aria-label="Website dashboard metrics">
  <div class="col-12 col-sm-6 col-xl-3">
    <a class="metric-card metric-link metric-primary" href="{{ route('view_product') }}" aria-label="Open website products">
      <div class="metric-top">
        <span class="metric-label">Website Products</span>
        <span class="metric-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span>
      </div>
      <div class="metric-value">{{ number_format($totalProducts) }}</div>
      <div class="metric-meta"><span class="text-success">Storefront</span><span>{{ number_format($totalCategories) }} categories</span></div>
    </a>
  </div>

  <div class="col-12 col-sm-6 col-xl-3">
    <a class="metric-card metric-link metric-success" href="{{ route('all_orders') }}" aria-label="Open online orders">
      <div class="metric-top">
        <span class="metric-label">Online Orders</span>
        <span class="metric-icon"><i class="bi bi-cart-check" aria-hidden="true"></i></span>
      </div>
      <div class="metric-value">{{ number_format($newOrders ?? $recentOrders->count()) }}</div>
      <div class="metric-meta"><span class="text-warning">{{ number_format($pendingOrders) }}</span><span>pending fulfillment</span></div>
    </a>
  </div>

  <div class="col-12 col-sm-6 col-xl-3">
    <a class="metric-card metric-link metric-warning" href="{{ route('all_orders') }}" aria-label="Open online sales">
      <div class="metric-top">
        <span class="metric-label">Online Sales</span>
        <span class="metric-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span>
      </div>
      <div class="metric-value">{{ number_format($dashboardSales, 2) }}</div>
      <div class="metric-meta"><span class="text-success">{{ number_format($completedOrders) }}</span><span>completed orders</span></div>
    </a>
  </div>

  <div class="col-12 col-sm-6 col-xl-3">
    <a class="metric-card metric-link metric-danger" href="{{ route('admin.faqs.index') }}" aria-label="Open website content">
      <div class="metric-top">
        <span class="metric-label">Customers</span>
        <span class="metric-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
      </div>
      <div class="metric-value">{{ number_format($totalUsers) }}</div>
      <div class="metric-meta"><span class="text-danger">Website</span><span>registered accounts</span></div>
    </a>
  </div>
</section>

<section class="row g-3 mt-1 dashboard-charts" aria-label="Website charts">
  <div class="col-12 col-xl-8">
    <div class="panel chart-panel h-100">
      <div class="panel-header">
        <div>
          <h2 class="h5 mb-1 section-title"><i class="bi bi-activity" aria-hidden="true"></i><span>Online Sales Trend</span></h2>
          <p class="text-muted mb-0">Storefront order value over the last seven days.</p>
        </div>
        <a class="btn btn-light btn-sm" href="{{ route('all_orders') }}">View Orders</a>
      </div>
      <div class="dashboard-chart dashboard-line-chart">
        <canvas id="websiteSalesTrendChart" aria-label="Seven-day website sales trend graph" role="img"></canvas>
      </div>
    </div>
  </div>

  <div class="col-12 col-xl-4">
    <div class="panel chart-panel h-100">
      <div class="panel-header">
        <div>
          <h2 class="h5 mb-1 section-title"><i class="bi bi-pie-chart" aria-hidden="true"></i><span>Catalog Mix</span></h2>
          <p class="text-muted mb-0">Website products grouped by storefront category.</p>
        </div>
      </div>
      <div class="dashboard-chart dashboard-pie-chart">
        @if($categorySeries->sum() > 0)
          <canvas id="websiteCatalogMixChart" aria-label="Website catalog category chart" role="img"></canvas>
        @else
          <p class="chart-empty mb-0">Catalog data will appear after products are added.</p>
        @endif
      </div>
    </div>
  </div>
</section>

<section class="row g-3 mt-1">
  <div class="col-12 col-xl-8">
    <div class="panel recent-orders-panel">
      <div class="panel-header">
        <div>
          <h2 class="h5 mb-1 section-title"><i class="bi bi-receipt" aria-hidden="true"></i><span>Recent Website Orders</span></h2>
          <p class="text-muted mb-0">Latest online checkout activity from the public storefront.</p>
        </div>
        <a class="btn btn-light btn-sm" href="{{ route('admin_orders') }}">Review Orders</a>
      </div>
      <div class="table-responsive">
        <table class="table align-middle mb-0 cashier-performance-table">
          <thead>
            <tr>
              <th>Customer</th>
              <th>Product</th>
              <th>Qty</th>
              <th>Total</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            @forelse($recentOrders as $order)
              <tr>
                <td>
                  <div class="dashboard-cashier">
                    <span>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($order->username ?? 'C', 0, 1)) }}</span>
                    <div>
                      <strong>{{ $order->username ?: 'Customer' }}</strong>
                      <small>{{ $order->phone ?: 'No phone' }}</small>
                    </div>
                  </div>
                </td>
                <td>
                  <strong>{{ $order->product_name ?: 'Product' }}</strong>
                  <small class="d-block text-muted">{{ $order->size ?: 'Single' }}{{ $order->color ? ' / '.$order->color : '' }}</small>
                </td>
                <td><strong>{{ (int) $order->quantity }}</strong></td>
                <td><span class="cashier-sales">{{ number_format((float) $order->total, 2) }}</span></td>
                <td><span class="badge text-bg-{{ ($order->status ?? 'pending') === 'completed' ? 'success' : (($order->status ?? 'pending') === 'cancelled' ? 'danger' : 'warning') }}">{{ ucfirst($order->status ?? 'pending') }}</span></td>
                <td><span class="cashier-last-sale">{{ optional($order->created_at)->format('d M Y, h:i A') }}</span></td>
              </tr>
            @empty
              <tr><td colspan="6" class="text-center py-5 text-muted">Website orders will appear after customers check out.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-12 col-xl-4">
    <div class="panel h-100 quick-actions-panel">
      <div class="panel-header">
        <div>
          <h2 class="h5 mb-1 section-title"><i class="bi bi-lightning-charge" aria-hidden="true"></i><span>Website Actions</span></h2>
          <p class="text-muted mb-0">Fast access to storefront admin tasks.</p>
        </div>
      </div>
      <div class="d-grid gap-2">
        <a class="btn btn-primary" href="{{ route('add_product') }}"><i class="bi bi-plus-circle"></i> Add Website Product</a>
        <a class="btn btn-outline-secondary" href="{{ route('view_product') }}"><i class="bi bi-box-seam"></i> Manage Products</a>
        <a class="btn btn-outline-secondary" href="{{ route('categories.index') }}"><i class="bi bi-tags"></i> Manage Categories</a>
        <a class="btn btn-outline-secondary" href="{{ route('meal_extras.index') }}"><i class="bi bi-basket2"></i> Add-ons & Basket Items</a>
        <a class="btn btn-outline-secondary" href="{{ route('home_slide') }}"><i class="bi bi-images"></i> Homepage Slides</a>
        <a class="btn btn-outline-secondary" href="{{ route('admin.contact.edit') }}"><i class="bi bi-telephone"></i> Contact Settings</a>
      </div>

      <div class="mt-4 pt-3 border-top">
        <h3 class="h6 mb-3">Latest Website Products</h3>
        <div class="d-grid gap-2">
          @forelse($products as $product)
            <a class="admin-nav-link text-dark bg-light" href="{{ route('editproduct', $product->id) }}">
              <i class="bi bi-box"></i>
              <span>{{ \Illuminate\Support\Str::limit($product->name, 34) }}</span>
            </a>
          @empty
            <p class="text-muted mb-0">No website products yet.</p>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/vendor/chart.js/dist/Chart.bundle.min.js') }}"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;

    var salesCanvas = document.getElementById('websiteSalesTrendChart');
    var catalogCanvas = document.getElementById('websiteCatalogMixChart');

    if (salesCanvas) {
      new Chart(salesCanvas.getContext('2d'), {
        type: 'line',
        data: {
          labels: @json($salesLabels),
          datasets: [{
            label: 'Online sales',
            data: @json($salesSeries),
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37, 99, 235, 0.10)',
            borderWidth: 3,
            pointBackgroundColor: '#ffffff',
            pointBorderColor: '#2563eb',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
            lineTension: 0.35,
            fill: true
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          legend: { display: false },
          tooltips: {
            callbacks: {
              label: function (item) {
                return 'Sales: ' + Number(item.yLabel).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
              }
            }
          },
          scales: {
            xAxes: [{ gridLines: { display: false }, ticks: { fontSize: 11 } }],
            yAxes: [{
              gridLines: { color: 'rgba(148, 163, 184, 0.18)', drawBorder: false },
              ticks: { beginAtZero: true, fontSize: 11, callback: function (value) { return Number(value).toLocaleString(); } }
            }]
          }
        }
      });
    }

    if (catalogCanvas) {
      new Chart(catalogCanvas.getContext('2d'), {
        type: 'doughnut',
        data: {
          labels: @json($categoryLabels),
          datasets: [{
            data: @json($categorySeries),
            backgroundColor: ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#0ea5e9'],
            borderColor: '#ffffff',
            borderWidth: 3
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutoutPercentage: 62,
          legend: {
            display: true,
            position: 'bottom',
            labels: { usePointStyle: true, boxWidth: 8, padding: 14, fontSize: 11 }
          }
        }
      });
    }
  });
</script>
@endpush


