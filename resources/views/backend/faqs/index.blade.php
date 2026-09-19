@extends('backend.admin')

@section('title', 'FAQ Management - ONJECASA')
@section('page-icon', 'bi bi-question-circle')
@section('page-eyebrow', 'Website Admin')
@section('page-title', 'FAQ Management')
@section('page-description', 'Manage the questions customers see before checkout and delivery.')
@section('page-actions')
  <a href="{{ route('admin.faqs.create') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-circle"></i> Add FAQ
  </a>
@endsection

@section('admin')
@php
  $activeCount = $faqs->where('is_active', true)->count();
  $inactiveCount = $faqs->where('is_active', false)->count();
  $categoryCount = $faqs->pluck('category')->filter()->unique()->count();
@endphp

<style>
  .faq-workspace { display: grid; gap: 1.25rem; min-width: 0; max-width: 100%; }
  .faq-metrics { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; min-width: 0; max-width: 100%; }
  .faq-metric, .faq-card, .faq-empty {
    min-width: 0;
    max-width: 100%;
    border: 1px solid var(--admin-border); border-radius: 8px; background: var(--admin-surface);
    box-shadow: 0 14px 30px rgba(15, 23, 42, .06);
  }
  .faq-metric { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; padding: 1rem; }
  .faq-metric strong { display: block; color: var(--admin-text); font-size: 1.45rem; line-height: 1.1; }
  .faq-metric span { display: block; margin-top: .25rem; color: var(--admin-muted); font-size: .78rem; font-weight: 900; text-transform: uppercase; letter-spacing: .04em; }
  .faq-metric i { width: 42px; height: 42px; display: inline-grid; place-items: center; border-radius: 8px; background: #eaf2ff; color: var(--admin-primary); }
  .faq-list { display: grid; gap: .9rem; }
  .faq-card { padding: 1rem; }
  .faq-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; min-width: 0; max-width: 100%; margin-bottom: .85rem; }
  .faq-meta { display: flex; align-items: center; gap: .45rem; flex-wrap: wrap; }
  .faq-pill { display: inline-flex; align-items: center; gap: .35rem; border-radius: 999px; padding: .34rem .65rem; font-size: .72rem; font-weight: 900; }
  .faq-pill.category { background: #eef6ff; color: #1d4ed8; }
  .faq-pill.active { background: #ecfdf5; color: #047857; }
  .faq-pill.inactive { background: #fef2f2; color: #b91c1c; }
  .faq-question { color: var(--admin-text); font-size: 1rem; font-weight: 900; line-height: 1.3; overflow-wrap: anywhere; }
  .faq-answer { margin-top: .45rem; color: var(--admin-muted); line-height: 1.55; overflow-wrap: anywhere; }
  .faq-actions { display: flex; align-items: center; justify-content: flex-end; gap: .45rem; flex-wrap: wrap; max-width: 100%; }
  .faq-empty { padding: 3rem 1rem; text-align: center; color: var(--admin-muted); }
  @media (max-width: 767.98px) {
    .faq-metrics { grid-template-columns: 1fr; }
    .faq-card-top { flex-direction: column; }
    .faq-actions { justify-content: flex-start; }
  }
</style>

<div class="faq-workspace">
  @if(session('success'))
    <div class="alert alert-success mb-0">{{ session('success') }}</div>
  @endif

  <section class="faq-metrics">
    <div class="faq-metric"><div><strong>{{ number_format($faqs->count()) }}</strong><span>Total FAQs</span></div><i class="bi bi-question-circle"></i></div>
    <div class="faq-metric"><div><strong>{{ number_format($activeCount) }}</strong><span>Active</span></div><i class="bi bi-eye"></i></div>
    <div class="faq-metric"><div><strong>{{ number_format($categoryCount) }}</strong><span>Categories</span></div><i class="bi bi-tags"></i></div>
  </section>

  <section class="faq-list">
    @forelse($faqs as $faq)
      <article class="faq-card">
        <div class="faq-card-top">
          <div class="min-w-0">
            <div class="faq-meta mb-2">
              <span class="faq-pill category">{{ ucfirst($faq->category) }}</span>
              <span class="faq-pill {{ $faq->is_active ? 'active' : 'inactive' }}">{{ $faq->is_active ? 'Active' : 'Inactive' }}</span>
              @if($faq->order > 0)<span class="faq-pill category">Order {{ $faq->order }}</span>@endif
            </div>
            <div class="faq-question">{{ $faq->question }}</div>
            <div class="faq-answer">{{ Str::limit($faq->answer, 220) }}</div>
            <div class="text-muted small fw-semibold mt-2">Updated {{ $faq->updated_at->format('M d, Y') }}</div>
          </div>
          <div class="faq-actions">
            <a href="{{ route('admin.faqs.edit', $faq->id) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
            <a href="{{ route('admin.faqs.toggle', $faq->id) }}" class="btn btn-outline-secondary btn-sm"><i class="bi {{ $faq->is_active ? 'bi-eye-slash' : 'bi-eye' }}"></i> {{ $faq->is_active ? 'Deactivate' : 'Activate' }}</a>
            <form action="{{ route('admin.faqs.destroy', $faq->id) }}" method="POST" class="m-0">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this FAQ?')"><i class="bi bi-trash"></i></button>
            </form>
          </div>
        </div>
      </article>
    @empty
      <div class="faq-empty">
        <i class="bi bi-journal-text fs-1 d-block mb-2"></i>
        <h3>No FAQs yet</h3>
        <p>Add the first customer question for the website FAQ page.</p>
        <a href="{{ route('admin.faqs.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Add FAQ</a>
      </div>
    @endforelse
  </section>
</div>
@endsection


