@extends('store.layout')
@section('title', 'Destek Talebi #' . $ticket->id)
@section('robots', 'noindex, nofollow')
@section('body_class', 'customer-dashboard')
@push('head')<link rel="stylesheet" href="{{ asset('css/customer-dashboard.css') }}?v={{ filemtime(public_path('css/customer-dashboard.css')) }}">@endpush
@section('content')
<div class="customer-dashboard__page container">
    <a class="support-back" href="{{ route('customer.account') }}#support">← Destek Taleplerime Dön</a>
    <section class="customer-dashboard__section support-conversation">
        <div class="customer-dashboard__section-head"><div><span>DESTEK TALEBİ #{{ $ticket->id }}</span><h1>{{ $ticket->subject }}</h1><p>{{ $ticket->created_at->format('d.m.Y H:i') }} · {{ ['general' => 'Genel', 'order' => 'Sipariş', 'product' => 'Ürün', 'reservation' => 'Mağazada Ayırma', 'account' => 'Hesap'][$ticket->category] ?? $ticket->category }}</p></div><span class="support-status support-status--{{ $ticket->status }}">{{ ['open' => 'Açık', 'waiting_customer' => 'Yanıtınız Bekleniyor', 'resolved' => 'Çözüldü', 'closed' => 'Kapalı'][$ticket->status] ?? $ticket->status }}</span></div>
        <div class="support-messages">
            @foreach ($ticket->messages as $message)
                <article class="support-message {{ $message->author_type === 'customer' ? 'support-message--mine' : 'support-message--admin' }}">
                    <header><strong>{{ $message->author_type === 'admin' ? 'Destek Ekibi' : 'Siz' }}</strong><time datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->format('d.m.Y H:i') }}</time></header>
                    <p>{{ $message->body }}</p>
                </article>
            @endforeach
        </div>
        <form class="support-create-form" method="POST" action="{{ route('customer.support.reply', $ticket) }}">
            @csrf
            <h2>Yanıt Yaz</h2>
            <label>Mesajınız<textarea name="body" rows="5" minlength="2" maxlength="10000" required>{{ old('body') }}</textarea></label>
            @error('body')<small class="customer-dashboard__error">{{ $message }}</small>@enderror
            <button class="customer-dashboard__primary" type="submit">Yanıtı Gönder</button>
        </form>
    </section>
</div>
@endsection
