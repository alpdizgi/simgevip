@php
    $campaign = $campaign ?? \App\Models\Campaign::active()->first();
@endphp
@if ($campaign)
<section class="campaign" style="padding-top: 2rem;">
    <div class="container campaign__inner">
        <div class="campaign__copy">
            <p class="eyebrow">Özel Kampanya</p>
            <h2>{{ $campaign->title }}</h2>
            @if ($campaign->description)
                <p>{{ $campaign->description }}</p>
            @endif
            @if (($campaign->discount_type ?? 'percentage') === 'nth_item' && $campaign->nth_item)
                <p class="campaign__discount">Sepetteki {{ $campaign->nth_item }}. ürüne %{{ \App\Models\Product::formatPercentLabel((float) $campaign->discount_percentage) }} indirim fırsatı</p>
            @elseif ($campaign->discount_percentage)
                <p class="campaign__discount">%{{ \App\Models\Product::formatPercentLabel((float) $campaign->discount_percentage) }} İndirim</p>
            @endif
            <a href="{{ route('shop.index', ['kampanya' => $campaign->id]) }}" class="btn btn--dark">Kampanyayı İncele</a>
        </div>
        @if ($campaign->image_path)
            <div class="campaign__media">
                <img src="{{ asset('storage/' . ltrim($campaign->image_path, '/')) }}" alt="{{ $campaign->title }}">
            </div>
        @elseif ($campaign->products->isNotEmpty())
            <div class="campaign__media">
                <img src="{{ $campaign->products->first()->image_url }}" alt="{{ $campaign->title }}">
            </div>
        @endif
    </div>
</section>
@endif
