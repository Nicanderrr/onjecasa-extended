@extends('layouts.pos-admin')

@section('title', trim($__env->yieldContent('page-title', 'Website Admin')) . ' - ONJECASA Admin')

@unless($__env->yieldContent('page-icon'))
  @section('page-icon', 'bi bi-globe2')
@endunless

@unless($__env->yieldContent('page-eyebrow'))
  @section('page-eyebrow', 'Website Admin')
@endunless

@unless($__env->yieldContent('page-description'))
  @section('page-description', 'Manage storefront content, online catalog, customer orders, and public website settings.')
@endunless

@push('styles')
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons+Round">
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <link rel="stylesheet" href="{{ asset('backend/material-dashboard/assets/css/nucleo-icons.css') }}">
  <link rel="stylesheet" href="{{ asset('backend/material-dashboard/assets/css/nucleo-svg.css') }}">
  <link rel="stylesheet" href="{{ asset('css/responsive-system.css') }}">
  <script src="{{ asset('backend/src/assets/libs/jquery/dist/jquery.min.js') }}"></script>
  <style>
    .website-admin-scope .material-icons,
    .website-admin-scope .material-icons-round {
      font-family: 'Material Icons Round', 'Material Icons';
      font-weight: normal;
      font-style: normal;
      font-size: 20px;
      line-height: 1;
      letter-spacing: normal;
      text-transform: none;
      display: inline-block;
      white-space: nowrap;
      word-wrap: normal;
      direction: ltr;
      -webkit-font-feature-settings: 'liga';
      -webkit-font-smoothing: antialiased;
    }

    .website-admin-scope .bg-gradient-primary,
    .website-admin-scope .btn.bg-gradient-primary,
    .website-admin-scope .badge.bg-gradient-primary {
      background-image: linear-gradient(195deg, #a42c4b 0%, #6b1730 100%) !important;
    }

    .website-admin-scope .card {
      border: 1px solid #dbe4ef;
      border-radius: 8px;
      box-shadow: 0 16px 34px rgba(15, 23, 42, .06);
    }

    .website-admin-scope .card-header {
      border-bottom: 1px solid #e5edf6;
      background: #fff;
    }

    .website-admin-scope .form-control,
    .website-admin-scope .form-select,
    .website-admin-scope select,
    .website-admin-scope input,
    .website-admin-scope textarea {
      border-radius: .55rem;
    }

    .website-admin-scope .table thead th {
      color: #64748b;
      font-size: .72rem;
      letter-spacing: .04em;
      text-transform: uppercase;
    }

    .website-admin-scope .material-table img {
      width: 48px;
      height: 48px;
      object-fit: cover;
      border-radius: 8px;
    }
  </style>
@endpush

@section('page-actions')
  <a class="btn btn-outline-secondary btn-sm" href="{{ route('home_page') }}">
    <i class="bi bi-shop"></i>
    View Storefront
  </a>
  <a class="btn btn-primary btn-sm" href="{{ route('pos.admin.dashboard') }}">
    <i class="bi bi-arrow-left-right"></i>
    In-Store POS
  </a>
@endsection

@section('content')
  <div class="source-banner source-banner-website">
    <span class="source-banner-icon"><i class="bi bi-globe2"></i></span>
    <div>
      <strong>Website Admin</strong>
      <span>These screens control the public storefront, online products, online customer orders, and website content.</span>
    </div>
  </div>

  <div class="website-admin-scope">
    @yield('admin')
  </div>
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('.website-admin-scope table').forEach(function (table) {
        if (table.closest('.table-responsive')) return;
        var wrapper = document.createElement('div');
        wrapper.className = 'table-responsive';
        table.parentNode.insertBefore(wrapper, table);
        wrapper.appendChild(table);
      });
    });

    if (window.jQuery) {
      $(function() {
        $(document).on('click', '#delete, [data-confirm-delete]', function(e) {
          e.preventDefault();
          var link = $(this).attr('href');
          Swal.fire({
            title: 'Delete this record?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it'
          }).then((result) => {
            if (result.isConfirmed && link) {
              window.location.href = link;
            }
          });
        });
      });
    }

    @if(Session::has('message'))
      var type = "{{ Session::get('alert-type','info') }}";
      if (window.toastr && toastr[type]) {
        toastr[type]("{{ Session::get('message') }}");
      }
    @endif
  </script>
@endpush

