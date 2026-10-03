@extends('store.layout')

@section('title', 'Hakkımızda')
@section('meta_description', $siteName . ' marka hikayesi — sade, kurumsal ve zamansız giyim.')
@section('canonical', route('about'))

@section('content')
<section class="page-hero">
    <div class="container">
        <p class="eyebrow">Marka</p>
        <h1>{{ $siteName }}</h1>
        <p>Gürültüsüz bir stil dili. Net siluetler, kaliteli kumaşlar ve uzun ömürlü parçalar.</p>
    </div>
</section>

<section class="section">
    <div class="container about-grid">
        <div class="about-copy">
            <h2>Kurumsal sadelik</h2>
            <p>{{ $siteName }}, modern yaşamın temposuna uyum sağlayan; abartıdan uzak, ölçülü bir giyim yaklaşımı sunar. Ofisten akşama, her ortamda dengeli duran parçalar tasarlarız.</p>
            <p>Koleksiyonumuz; seçilmiş renk paleti, temiz kesimler ve işlevsel detaylarla kurgulanır. Amaç gösteriş değil: güven veren, zamansız bir gardırop.</p>
        </div>
        <div class="about-media">
            <img src="https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?auto=format&fit=crop&w=1200&q=80" alt="Atölye ve stil">
        </div>
    </div>
</section>

<section class="section section--muted">
    <div class="container values">
        <article>
            <h3>Malzeme</h3>
            <p>Dokunuşu iyi, formu koruyan kumaşlara öncelik veririz.</p>
        </article>
        <article>
            <h3>Kesim</h3>
            <p>Vücuda saygılı, abartısız ve modern kalıplar kullanırız.</p>
        </article>
        <article>
            <h3>Dürüstlük</h3>
            <p>Net ürün bilgisi, şeffaf iletişim ve sürdürülebilir seçimler.</p>
        </article>
    </div>
</section>
@endsection
