@extends('store.layout')

@section('title', 'Sıkça Sorulan Sorular')
@section('meta_description', $siteName . ' sıkça sorulan sorular: sipariş, beden, iade ve mağaza bilgileri.')
@section('canonical', route('faq.index'))

@push('head')
@php
    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $faqs->map(function ($faq) {
            return [
                '@type' => 'Question',
                'name' => \App\Support\SeoText::plain($faq->question, 300),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => \App\Support\SeoText::plain($faq->answer, 2000),
                ],
            ];
        })->filter(fn ($item) => $item['name'] !== '' && $item['acceptedAnswer']['text'] !== '')->values()->all(),
    ];
@endphp
@if ($faqSchema['mainEntity'] !== [])
<script type="application/ld+json">{!! json_encode($faqSchema, \App\Support\SeoText::jsonFlags()) !!}</script>
@endif
@endpush

@section('content')
<section class="page-hero page-hero--compact">
    <div class="container">
        <h1>Sıkça Sorulan Sorular</h1>
        <p>Müşterilerimizden en çok gelen soruları ve yanıtlarını aşağıda bulabilirsiniz.</p>
    </div>
</section>

<style>
    .faq-page-container {
        max-width: 800px;
        margin: 3rem auto 6rem;
        padding: 0 1.5rem;
    }
    .faq-accordion {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .faq-item {
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 8px;
        overflow: hidden;
        transition: border-color 0.2s ease;
    }
    .faq-item.is-active {
        border-color: var(--ink, #000);
    }
    .faq-question {
        width: 100%;
        text-align: left;
        background: #fafafa;
        border: none;
        padding: 1.25rem 1.5rem;
        font-size: 1.05rem;
        font-weight: 600;
        color: #111;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background 0.2s ease;
    }
    .faq-question:hover {
        background: #f4f4f5;
    }
    .faq-question svg {
        transition: transform 0.3s ease;
    }
    .faq-item.is-active .faq-question svg {
        transform: rotate(180deg);
    }
    .faq-answer {
        padding: 0 1.5rem;
        max-height: 0;
        overflow: hidden;
        background: #ffffff;
        color: #555;
        line-height: 1.7;
        font-size: 0.95rem;
        transition: max-height 0.3s ease, padding 0.3s ease;
    }
    .faq-item.is-active .faq-answer {
        padding: 1.25rem 1.5rem;
        max-height: 500px;
        border-top: 1px solid rgba(0,0,0,0.05);
    }
    .faq-empty {
        text-align: center;
        color: #888;
        padding: 2rem 0;
    }
</style>

<main class="site-main">
    <div class="faq-page-container">
        <div class="faq-accordion">
            @forelse($faqs as $index => $faq)
            <div class="faq-item" id="faq-item-{{ $index }}">
                <button class="faq-question" onclick="toggleFaq({{ $index }})">
                    <span>{{ $faq->question }}</span>
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div class="faq-answer">
                    {!! nl2br(e($faq->answer)) !!}
                </div>
            </div>
            @empty
            <p class="faq-empty">Henüz sıkça sorulan soru eklenmedi.</p>
            @endforelse
        </div>
    </div>
</main>

<script>
    function toggleFaq(index) {
        const item = document.getElementById('faq-item-' + index);
        item.classList.toggle('is-active');
    }
</script>
@endsection
