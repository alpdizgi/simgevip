@if ($record)
    <div class="sv-reservation-summary">
        <div class="sv-reservation-heading"><x-heroicon-o-calendar /><strong>{{ $record->is_admin_hold ? 'Mağaza Ayırması' : 'Web Talebi' }}</strong></div>
        <p>{{ $record->customer_name }} · {{ $record->reservation_date?->format('d.m.Y H:i') }}</p>
        @if ($record->product)
            <div class="sv-reservation-product">
                <img src="{{ $record->product->image_url }}" alt="" loading="lazy">
                <div><strong>{{ $record->product->name }}</strong>
                    <p>Güncel fiyat: {{ number_format((float) ($record->product->discounted_price ?? $record->product->price), 2, ',', '.') }} TL</p>
                    <a href="{{ route('product.show', $record->product->slug) }}" target="_blank" rel="noopener noreferrer">Ürünü incele</a>
                </div>
            </div>
        @else
            <p>{{ $record->product_id ? 'Ürün kaydı artık mevcut değil.' : 'Genel randevu · Belirli bir ürün seçilmedi.' }}</p>
        @endif
        <div class="sv-reservation-tags">
            @if ($record->selected_color)<span>Renk: {{ $record->selected_color }}</span>@endif
            @if ($record->selected_size)<span>Beden: {{ $record->selected_size }}</span>@endif
            @if ($sku = ($record->parsed_notes['sku'] ?? $record->product?->sku))<span>SKU: {{ $sku }}</span>@endif
        </div>
    </div>
@endif
