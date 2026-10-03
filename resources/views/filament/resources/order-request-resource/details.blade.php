@if ($record)
    @php
        $customer = $record->customer;
        $money = fn ($amount) => number_format((float) $amount, 2, ',', '.') . ' TL';
    @endphp
    <div class="sv-order-details">
        @if ($record->status === 'cancel_requested')
            <div class="sv-order-alert" role="status">
                <x-heroicon-o-exclamation-circle />
                <div><strong>Müşteri iptal talebinde bulundu</strong><p>{{ $record->cancel_reason ?: 'Gerekçe belirtilmedi.' }}</p></div>
            </div>
        @elseif (filled($record->cancel_reason))
            <div class="sv-order-note">
                <strong>{{ $record->status === 'cancelled' ? 'İptal gerekçesi' : 'Geçmiş iptal gerekçesi' }}</strong>
                <p>{{ $record->cancel_reason }}</p>
            </div>
        @endif

        <dl class="sv-order-summary">
            <div>
                <dt>Müşteri</dt>
                <dd><strong>{{ $customer?->name ?? '—' }}</strong></dd>
                <dd class="sv-order-contact"><x-heroicon-o-phone /><span>{{ $customer?->phone ?: ($customer?->login_phone ?: '—') }}</span></dd>
                <dd class="sv-order-contact"><x-heroicon-o-mail /><span>{{ $customer?->email ?: ($customer?->login_email ?: '—') }}</span></dd>
            </div>
            <div>
                <dt>Kaynak ve tarih</dt>
                <dd>{{ ['cart' => 'Sepet', 'product' => 'Ürün Sayfası'][$record->source] ?? '—' }}</dd>
                <dd>{{ $record->created_at?->format('d.m.Y H:i') ?? '—' }}</dd>
                <dd>{{ \App\Models\CustomerOrderRequest::STATUS_LABELS[$record->status] ?? $record->status }}</dd>
            </div>
            <div><dt>Talep toplamı</dt><dd class="sv-order-total">{{ $money($record->total) }}</dd><dd>Talep anında kaydedilen tutar</dd></div>
        </dl>

        <div class="sv-order-items" role="region" aria-label="Sipariş ürünleri ve fiyat dökümü" tabindex="0">
            <table>
                <caption>Talep edilen ürünler</caption>
                <thead><tr><th scope="col">Ürün</th><th scope="col">Adet</th><th scope="col">Birim fiyat</th><th scope="col">Satır toplamı</th></tr></thead>
                <tbody>
                    @forelse (($record->items ?? []) as $item)
                        @php
                            // Normalize only the stored snapshot; never look up today's product prices.
                            $line = app(\App\Services\CartService::class)->normalizeItem($item);
                            $lineTotal = $item['line_total'] ?? $line['line_total'];
                            $discount = max(0, round($line['original_price'] * $line['qty'] - (float) $lineTotal, 2));
                        @endphp
                        <tr>
                            <td><div class="sv-order-product">
                                <img src="{{ $item['image'] ?? asset('images/store/placeholder.svg') }}" alt="" loading="lazy">
                                <div><strong>{{ $item['name'] ?? 'Ürün' }}</strong>
                                    @if (! empty($item['color']))<small>Renk: {{ $item['color'] }}</small>@endif
                                    @if (! empty($item['size']))<small>Beden: {{ $item['size'] }}</small>@endif
                                </div>
                            </div></td>
                            <td>{{ $line['qty'] }}</td>
                            <td>{{ $money($line['price']) }}
                                @if ($line['original_price'] > $line['price'])<small>Normal: <del>{{ $money($line['original_price']) }}</del></small>@endif
                            </td>
                            <td><strong>{{ $money($lineTotal) }}</strong>
                                @if ($discount > 0)<small>Toplam indirim: {{ $money($discount) }}</small>@endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Ürün kaydı bulunamadı.</td></tr>
                    @endforelse
                </tbody>
                <tfoot><tr><th colspan="3" scope="row">Kaydedilen talep toplamı</th><td>{{ $money($record->total) }}</td></tr></tfoot>
            </table>
        </div>
    </div>
@endif
