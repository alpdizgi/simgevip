@extends('store.layout')

@section('title', 'Sayfa bulunamadı')
@section('meta_description', 'Aradığınız sayfa yayında değil veya taşınmış olabilir.')
@section('robots', 'noindex, nofollow')

@section('content')
<section class="page-hero page-hero--compact">
    <div class="container">
        <p class="eyebrow">404</p>
        <h1>Sayfa bulunamadı</h1>
        <p>Aradığınız sayfa yayında değil veya taşınmış olabilir.</p>
        <a class="btn btn--dark" href="{{ route('home') }}">Ana sayfaya dön</a>
    </div>
</section>
@endsection
