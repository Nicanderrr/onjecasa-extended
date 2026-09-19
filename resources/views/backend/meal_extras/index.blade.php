@extends('backend.admin')

@section('page-icon', 'bi bi-basket2')
@section('page-eyebrow', 'Storefront Catalog')
@section('page-title', 'Add-ons & Basket Items')
@section('page-description', 'Manage customer add-ons, prices, descriptions, images, and availability.')

@section('admin')
@php
    $editing = isset($mealExtra);
    $formAction = $editing ? route('meal_extras.update', $mealExtra) : route('meal_extras.store');
@endphp

<style>
    .extras-admin {
        display: grid;
        gap: 1rem;
    }

    .extras-layout {
        display: grid;
        grid-template-columns: minmax(280px, 380px) minmax(0, 1fr);
        gap: 1rem;
        align-items: start;
    }

    .extras-panel {
        border: 1px solid #dbe4ef;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 18px 40px rgba(15, 23, 42, .08);
        overflow: hidden;
    }

    .extras-panel-header {
        padding: 1rem 1.1rem;
        border-bottom: 1px solid #e5edf6;
        background: #fffaf0;
    }

    .extras-panel-header strong,
    .extras-panel-header span {
        display: block;
    }

    .extras-panel-header strong {
        color: #172033;
        font-size: 1rem;
    }

    .extras-panel-header span {
        margin-top: .2rem;
        color: #7b5f3f;
        font-size: .86rem;
    }

    .extras-form,
    .extras-table-wrap {
        padding: 1rem;
    }

    .extras-form {
        display: grid;
        gap: .85rem;
    }

    .extras-form label {
        display: grid;
        gap: .35rem;
        color: #172033;
        font-size: .82rem;
        font-weight: 800;
    }

    .extras-form input,
    .extras-form textarea {
        width: 100%;
        border: 1px solid #d8e0ec;
        border-radius: 8px;
        padding: .76rem .85rem;
        font-weight: 600;
    }

    .extras-form textarea {
        min-height: 92px;
        resize: vertical;
    }

    .extras-toggle {
        display: flex !important;
        grid-template-columns: none !important;
        align-items: center;
        gap: .6rem !important;
    }

    .extras-toggle input {
        width: auto;
        accent-color: #2f9b55;
    }

    .extras-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .55rem;
    }

    .extra-thumb {
        width: 64px;
        height: 64px;
        object-fit: cover;
        border-radius: 8px;
        background: #fff7df;
        border: 1px solid #f0dfb6;
    }

    .extras-table {
        width: 100%;
        border-collapse: collapse;
    }

    .extras-table th,
    .extras-table td {
        padding: .85rem .75rem;
        border-bottom: 1px solid #eef2f7;
        vertical-align: middle;
    }

    .extras-table th {
        color: #667085;
        font-size: .72rem;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .extra-name {
        display: grid;
        gap: .15rem;
    }

    .extra-name strong {
        color: #172033;
    }

    .extra-name span {
        color: #667085;
        font-size: .86rem;
    }

    .extra-status {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: .32rem .6rem;
        font-size: .72rem;
        font-weight: 900;
        text-transform: uppercase;
    }

    .extra-status.active {
        background: #ecfdf3;
        color: #027a48;
    }

    .extra-status.hidden {
        background: #fff1f3;
        color: #b42318;
    }

    @media (max-width: 991.98px) {
        .extras-layout {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="extras-admin">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="extras-layout">
        <section class="extras-panel">
            <div class="extras-panel-header">
                <strong>{{ $editing ? 'Edit Add-on' : 'Add Add-on' }}</strong>
                <span>Prices entered here are used on product pages and cart totals.</span>
            </div>

            <form class="extras-form" method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
                @csrf
                @if($editing)
                    @method('PUT')
                @endif

                <label>
                    Name
                    <input type="text" name="name" value="{{ old('name', $mealExtra->name ?? '') }}" placeholder="e.g. Carrier Bag" required>
                </label>

                <label>
                    Price
                    <input type="number" name="price" value="{{ old('price', $mealExtra->price ?? '') }}" min="0" step="0.01" placeholder="0.00" required>
                </label>

                <label>
                    Description
                    <textarea name="description" placeholder="Short note customers can understand.">{{ old('description', $mealExtra->description ?? '') }}</textarea>
                </label>

                <label>
                    Image
                    <input type="file" name="image" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                </label>

                @if($editing && !empty($mealExtra->image))
                    <img class="extra-thumb" src="{{ $mealExtra->image_url }}" alt="{{ $mealExtra->name }}">
                @endif

                <label>
                    Bootstrap Icon Class
                    <input type="text" name="icon" value="{{ old('icon', $mealExtra->icon ?? 'bi-basket2') }}" placeholder="bi-basket2">
                </label>

                <label>
                    Sort Order
                    <input type="number" name="sort_order" value="{{ old('sort_order', $mealExtra->sort_order ?? 0) }}" min="0" step="1">
                </label>

                <label class="extras-toggle">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $mealExtra->is_active ?? true))>
                    Show this add-on to customers
                </label>

                <div class="extras-actions">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-check2-circle"></i> {{ $editing ? 'Save Extra' : 'Add Extra' }}
                    </button>
                    @if($editing)
                        <a class="btn btn-outline-secondary" href="{{ route('meal_extras.index') }}">Cancel</a>
                    @endif
                </div>
            </form>
        </section>

        <section class="extras-panel">
            <div class="extras-panel-header">
                <strong>Available Add-ons & Basket Items</strong>
                <span>{{ $extras->count() }} items in the master list.</span>
            </div>

            <div class="extras-table-wrap">
                <table class="extras-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th style="width: 170px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($extras as $extra)
                            <tr>
                                <td><img class="extra-thumb" src="{{ $extra->image_url }}" alt="{{ $extra->name }}"></td>
                                <td>
                                    <span class="extra-name">
                                        <strong>{{ $extra->name }}</strong>
                                        <span>{{ \Illuminate\Support\Str::limit($extra->description ?: 'No description yet.', 70) }}</span>
                                    </span>
                                </td>
                                <td><strong>GHC {{ number_format($extra->price, 2) }}</strong></td>
                                <td>
                                    <span class="extra-status {{ $extra->is_active ? 'active' : 'hidden' }}">
                                        {{ $extra->is_active ? 'Active' : 'Hidden' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('meal_extras.edit', $extra) }}">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('meal_extras.destroy', $extra) }}" onsubmit="return confirm('Delete this add-on?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">No add-ons have been added yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection


