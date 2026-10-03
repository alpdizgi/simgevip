@extends('store.layout')

@section('title', $page->title)
@section('meta_description', \App\Support\SeoText::plain($page->content, 500))
@section('canonical', route('page.show', $page->slug))

@section('content')
<section class="page-hero page-hero--compact">
    <div class="container">
        <h1>{{ $page->title }}</h1>
        <p>{{ config('app.name') }} site politikaları ve bilgilendirme metni.</p>
    </div>
</section>

<style>
    .dynamic-page-content {
        max-width: 800px;
        margin: 3rem auto 6rem;
        font-size: 1.05rem;
        line-height: 1.8;
        color: #444;
        padding: 0 1.5rem;
    }
    .dynamic-page-content h2, 
    .dynamic-page-content h3 {
        color: #111;
        margin-top: 2.5rem;
        margin-bottom: 1rem;
        font-family: var(--display, inherit);
    }
    .dynamic-page-content p {
        margin-bottom: 1.5rem;
    }
    .dynamic-page-content ul, 
    .dynamic-page-content ol {
        margin-bottom: 1.5rem;
        padding-left: 1.5rem;
    }
    .dynamic-page-content li {
        margin-bottom: 0.5rem;
    }
    .dynamic-page-content a {
        text-decoration: underline;
        text-underline-offset: 4px;
        color: var(--ink, #000);
    }
</style>

<main class="site-main">
    <div class="container">
        <div class="dynamic-page-content">
            {!! $page->content !!}
        </div>
    </div>
</main>
@endsection
