@extends('Frontend.layout')

@section('title', 'FAQs - ONJECASA')

@section('content')
@php
    $generalFaqs = $generalFaqs ?? \App\Models\FAQ::active()->byCategory('general')->ordered()->get();
    $servicesFaqs = $servicesFaqs ?? \App\Models\FAQ::active()->byCategory('services')->ordered()->get();
@endphp

<section class="page-hero shop-card">
    <div class="section-kicker">FAQs</div>
    <h1>Answers before checkout.</h1>
    <p>Common questions about buying products, bulk supply, delivery, and order handling.</p>
</section>

<section class="row g-4">
    <div class="col-lg-6">
        <div class="shop-panel p-4 h-100">
            <div class="section-kicker">General</div>
            <h2 class="section-title mt-1 mb-4">Customer questions</h2>
            <div class="accordion accordion-flush" id="generalFaqs">
                @forelse($generalFaqs as $faq)
                    <div class="accordion-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#generalFaq{{ $faq->id }}">
                                {{ $faq->question }}
                            </button>
                        </h3>
                        <div id="generalFaq{{ $faq->id }}" class="accordion-collapse collapse" data-bs-parent="#generalFaqs">
                            <div class="accordion-body muted-copy">{{ $faq->answer }}</div>
                        </div>
                    </div>
                @empty
                    <p class="muted-copy">No general FAQs added yet.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="shop-panel p-4 h-100">
            <div class="section-kicker">Orders & supply</div>
            <h2 class="section-title mt-1 mb-4">Buying support</h2>
            <div class="accordion accordion-flush" id="serviceFaqs">
                @forelse($servicesFaqs as $faq)
                    <div class="accordion-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#serviceFaq{{ $faq->id }}">
                                {{ $faq->question }}
                            </button>
                        </h3>
                        <div id="serviceFaq{{ $faq->id }}" class="accordion-collapse collapse" data-bs-parent="#serviceFaqs">
                            <div class="accordion-body muted-copy">{{ $faq->answer }}</div>
                        </div>
                    </div>
                @empty
                    <p class="muted-copy">No order FAQs added yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection

