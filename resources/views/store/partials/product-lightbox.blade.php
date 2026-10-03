<div class="product-lightbox" id="productLightbox" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Ürün Görseli İnceleme">
    <div class="lightbox__backdrop" data-lightbox-close></div>

    <div class="lightbox__dialog">
        <header class="lightbox__header">
            <div class="lightbox__title-wrap">
                <span class="lightbox__product-title">{{ $product->name }}</span>
                <span class="lightbox__counter" data-lightbox-counter>1 / {{ count($initialImages) }}</span>
            </div>

            <div class="lightbox__toolbar">
                <button type="button" class="lightbox__btn" data-lightbox-zoom-out aria-label="Uzaklaştır" title="Uzaklaştır (-)">
                    <x-store-icon name="zoom-out" :size="18" />
                </button>
                <button type="button" class="lightbox__btn lightbox__zoom-label-btn" data-lightbox-reset aria-label="Boyutu sıfırla" title="Orijinal Boyut (100%)">
                    <span class="lightbox__zoom-level" data-lightbox-zoom-level>100%</span>
                </button>
                <button type="button" class="lightbox__btn" data-lightbox-zoom-in aria-label="Yakınlaştır" title="Yakınlaştır (+)">
                    <x-store-icon name="zoom-in" :size="18" />
                </button>
                <button type="button" class="lightbox__btn lightbox__btn--close" data-lightbox-close aria-label="Kapat" title="Kapat (Esc)">
                    <x-store-icon name="x" :size="20" />
                </button>
            </div>
        </header>

        <div class="lightbox__body">
            <button type="button" class="lightbox__nav-btn lightbox__nav-btn--prev" data-lightbox-prev aria-label="Önceki görsel">
                <x-store-icon name="chevron-left" :size="24" />
            </button>

            <div class="lightbox__stage" data-lightbox-stage>
                <div class="lightbox__canvas" data-lightbox-canvas>
                    <img src="{{ $initialImages[0] ?? $product->image_url }}" alt="{{ $product->name }}" class="lightbox__img" data-lightbox-img draggable="false">
                </div>
                <div class="lightbox__hint">
                    <span>Detayları incelemek için görsele tıklayın veya kaydırın</span>
                </div>
            </div>

            <button type="button" class="lightbox__nav-btn lightbox__nav-btn--next" data-lightbox-next aria-label="Sonraki görsel">
                <x-store-icon name="chevron-right" :size="24" />
            </button>
        </div>

        <footer class="lightbox__footer">
            <div class="lightbox__thumbs" data-lightbox-thumbs>
                @foreach ($initialImages as $idx => $img)
                    <button type="button" class="lightbox__thumb {{ $idx === 0 ? 'is-active' : '' }}" data-lightbox-thumb-index="{{ $idx }}" aria-label="Görsel {{ $idx + 1 }}">
                        <img src="{{ $img }}" alt="">
                    </button>
                @endforeach
            </div>
        </footer>
    </div>
</div>
