@extends('backend.admin')
@section('admin')

<style>
    .category-page {
        max-width: 1100px;
        margin: 0 auto;
    }
    .category-card {
        background: #fff;
        border: 0;
        border-radius: 8px;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
        padding: 2rem;
    }
    .category-header {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    .category-header h2 {
        margin: 0;
        color: #172033;
        font-size: 1.6rem;
        font-weight: 700;
    }
    .category-form {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }
    .category-input {
        border: 1px solid #d8e0ec;
        border-radius: 8px;
        padding: 0.8rem 1rem;
        width: 100%;
    }
    .category-btn {
        border: 0;
        border-radius: 8px;
        background: #ff8615;
        color: #fff;
        font-weight: 700;
        padding: 0.8rem 1.2rem;
    }
    .category-table {
        width: 100%;
        border-collapse: collapse;
    }
    .category-table th,
    .category-table td {
        border-bottom: 1px solid #eef2f7;
        padding: 1rem 0.75rem;
        text-align: left;
    }
    .category-table th {
        color: #667085;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .delete-btn {
        border: 1px solid #fecaca;
        border-radius: 8px;
        background: #fff5f5;
        color: #b91c1c;
        padding: 0.45rem 0.75rem;
        font-weight: 600;
    }
    .alert {
        border-radius: 8px;
        padding: 0.85rem 1rem;
        margin-bottom: 1rem;
    }
    .alert-success {
        background: #ecfdf3;
        color: #027a48;
    }
    .alert-error {
        background: #fff1f3;
        color: #b42318;
    }
</style>

<div class="container-fluid category-page">
    <div class="category-card">
        <div class="category-header">
            <div>
                <h2>Product Categories</h2>
                <p class="text-muted mb-0">Add categories used by the product category dropdown.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @isset($errors)
            @if($errors->any())
                <div class="alert alert-error">{{ $errors->first() }}</div>
            @endif
        @endisset

        <form method="POST" action="{{ route('categories.store') }}" class="category-form">
            @csrf
            <input class="category-input" name="name" type="text" value="{{ old('name') }}" placeholder="e.g. Groceries, Spirits, Soft drinks" required>
            <button class="category-btn" type="submit">Add Category</button>
        </form>

        <table class="category-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th style="width: 140px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td>{{ $category->name }}</td>
                        <td>
                            <form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?');">
                                @csrf
                                @method('DELETE')
                                <button class="delete-btn" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-muted">No categories yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

