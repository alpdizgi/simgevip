document.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('[data-header]');
    const toggle = document.querySelector('[data-nav-toggle]');
    const nav = document.querySelector('[data-nav]');

    let headerHeightRaf = null;
    const syncHeaderHeight = () => {
        if (!header) return;
        // Mobil veya masaüstü temel yüksekliğini ölçüp ayarla, ancak scroll anındaki topbar gizlenmesiyle layout zıplatma
        if (headerHeightRaf) cancelAnimationFrame(headerHeightRaf);
        headerHeightRaf = requestAnimationFrame(() => {
            const h = header.offsetHeight;
            if (h > 0) {
                // Eğer is-scrolled ise ve önceki değer varsa ani layout jump yapmamak için koru
                if (!header.classList.contains('is-scrolled') || !document.documentElement.style.getPropertyValue('--header-h')) {
                    document.documentElement.style.setProperty('--header-h', `${h}px`);
                }
            }
            headerHeightRaf = null;
        });
    };

    // Hysteresis: eşiğin iki yanında farklı limit → sürekli aç/kapa titremesi olmaz
    let scrolled = false;
    const onScroll = () => {
        if (!header) return;
        const y = window.scrollY || window.pageYOffset || 0;
        if (!scrolled && y > 48) {
            scrolled = true;
            header.classList.add('is-scrolled');
        } else if (scrolled && y < 8) {
            scrolled = false;
            header.classList.remove('is-scrolled');
        }
    };

    syncHeaderHeight();
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', syncHeaderHeight, { passive: true });
    window.addEventListener('load', syncHeaderHeight, { passive: true });

    if (window.ResizeObserver && header) {
        new ResizeObserver(() => syncHeaderHeight()).observe(header);
    }

    const openNavDrawer = () => {
        if (!nav) return;
        nav.classList.add('is-open');
        nav.setAttribute('aria-hidden', 'false');
        document.body.classList.add('nav-open');
    };

    const closeNavDrawer = () => {
        if (!nav) return;
        nav.classList.remove('is-open');
        nav.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('nav-open');
        if (document.activeElement && nav.contains(document.activeElement)) {
            document.activeElement.blur();
        }
    };

    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            nav.classList.contains('is-open') ? closeNavDrawer() : openNavDrawer();
        });
    }

    document.querySelectorAll('[data-nav-close]').forEach((el) => {
        el.addEventListener('click', closeNavDrawer);
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeNavDrawer();
    });

    const searchToggle = document.querySelector('[data-search-toggle]');
    const searchPanel = document.querySelector('[data-search-panel]');
    const searchBackdrop = document.querySelector('.header-search-backdrop');
    const searchAnchor = searchPanel ? searchPanel.parentElement : null;
    const desktopSearch = window.matchMedia('(min-width: 992px)');

    const mountDesktopSearch = () => {
        if (!desktopSearch.matches || !searchPanel || !searchAnchor) return;
        if (searchBackdrop && searchBackdrop.parentElement !== document.body) {
            document.body.appendChild(searchBackdrop);
        }
        if (searchPanel.parentElement !== document.body) {
            document.body.appendChild(searchPanel);
        }
    };

    const unmountDesktopSearch = () => {
        if (!searchPanel || !searchAnchor || searchPanel.parentElement === searchAnchor) return;
        if (searchPanel.classList.contains('is-open')) return;
        if (searchBackdrop) searchAnchor.appendChild(searchBackdrop);
        searchAnchor.appendChild(searchPanel);
    };

    const openSearch = () => {
        if (!searchPanel) return;
        mountDesktopSearch();
        searchPanel.classList.add('is-open');
        searchPanel.setAttribute('aria-hidden', 'false');
        if (searchBackdrop) {
            searchBackdrop.classList.add('is-open');
            searchBackdrop.setAttribute('aria-hidden', 'false');
        }
        if (searchToggle) {
            searchToggle.setAttribute('aria-expanded', 'true');
        }
        document.body.classList.add('search-open');
        const input = searchPanel.querySelector('input');
        if (input) {
            setTimeout(() => {
                input.focus({ preventScroll: true });
            }, 60);
        }
    };

    const closeSearch = () => {
        if (!searchPanel) return;
        searchPanel.classList.remove('is-open');
        searchPanel.setAttribute('aria-hidden', 'true');
        if (searchBackdrop) {
            searchBackdrop.classList.remove('is-open');
            searchBackdrop.setAttribute('aria-hidden', 'true');
        }
        if (searchToggle) {
            searchToggle.setAttribute('aria-expanded', 'false');
        }
        document.body.classList.remove('search-open');
        if (document.activeElement && searchPanel.contains(document.activeElement)) {
            document.activeElement.blur();
        }
        window.setTimeout(unmountDesktopSearch, 760);
    };

    if (searchToggle && searchPanel) {
        searchToggle.addEventListener('click', (e) => {
            e.preventDefault();
            searchPanel.classList.contains('is-open') ? closeSearch() : openSearch();
        });

        document.querySelectorAll('[data-search-close]').forEach((el) => {
            el.addEventListener('click', (e) => {
                e.preventDefault();
                closeSearch();
            });
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && searchPanel.classList.contains('is-open')) {
                closeSearch();
            }
        });
    }

    // Shop page compact filter / sort toolbar
    const shopToolbar = document.querySelector('[data-shop-toolbar]');
    if (shopToolbar) {
        const closeAllShopDropdowns = (except = null) => {
            shopToolbar.querySelectorAll('[data-shop-dropdown]').forEach((dropdown) => {
                if (except && dropdown === except) return;
                const toggleBtn = dropdown.querySelector('[data-shop-dropdown-toggle]');
                const panel = dropdown.querySelector('[data-shop-dropdown-panel]');
                if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'false');
                if (panel) panel.hidden = true;
            });
        };

        shopToolbar.querySelectorAll('[data-shop-dropdown]').forEach((dropdown) => {
            const toggleBtn = dropdown.querySelector('[data-shop-dropdown-toggle]');
            const panel = dropdown.querySelector('[data-shop-dropdown-panel]');
            if (!toggleBtn || !panel) return;

            toggleBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const willOpen = panel.hidden;
                closeAllShopDropdowns();
                if (willOpen) {
                    panel.hidden = false;
                    toggleBtn.setAttribute('aria-expanded', 'true');
                    const focusable = panel.querySelector('input, select, button');
                    if (focusable) setTimeout(() => focusable.focus(), 40);
                }
            });
        });

        document.addEventListener('click', (e) => {
            if (!shopToolbar.contains(e.target)) closeAllShopDropdowns();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeAllShopDropdowns();
        });
    }

    // Category accordion inside the menu drawer
    document.querySelectorAll('[data-dropdown]').forEach((item) => {
        const link = item.querySelector('.nav-link');
        if (!link) return;

        link.addEventListener('click', (e) => {
            e.preventDefault();
            item.classList.toggle('is-open');
        });
    });


    initProductDetail();
    initLiveSearch();
    initCategoryCarousels();
    initCartAndActions();
    initFavoriteActions();
    initScrollTop();
    initStoreToast();

    const initHeroSlider = () => {
        const sliders = document.querySelectorAll('.js-hero-slider');
        sliders.forEach(slider => {
            const slides = slider.querySelectorAll('.hero-slide');
            if (slides.length <= 1) return;

            let current = 0;
            let timer = null;
            const intervalTime = parseInt(slider.dataset.autoplay) || 5000;
            const dots = slider.querySelectorAll('[data-hero-dot]');
            const prevBtn = slider.querySelector('[data-hero-prev]');
            const nextBtn = slider.querySelector('[data-hero-next]');

            const goToSlide = (index) => {
                const nextIndex = (index + slides.length) % slides.length;
                if (nextIndex === current) return;

                slides[current].classList.remove('is-active');
                if (dots[current]) dots[current].classList.remove('is-active');

                current = nextIndex;

                slides[current].classList.add('is-active');
                if (dots[current]) dots[current].classList.add('is-active');
            };

            const startAutoplay = () => {
                stopAutoplay();
                timer = setInterval(() => {
                    goToSlide(current + 1);
                }, intervalTime);
            };

            const stopAutoplay = () => {
                if (timer) {
                    clearInterval(timer);
                    timer = null;
                }
            };

            if (prevBtn) {
                prevBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    goToSlide(current - 1);
                    startAutoplay();
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    goToSlide(current + 1);
                    startAutoplay();
                });
            }

            dots.forEach((dot, idx) => {
                dot.addEventListener('click', (e) => {
                    e.preventDefault();
                    goToSlide(idx);
                    startAutoplay();
                });
            });

            slider.addEventListener('mouseenter', stopAutoplay);
            slider.addEventListener('mouseleave', startAutoplay);

            let touchStartX = 0;
            let touchEndX = 0;
            slider.addEventListener('touchstart', (e) => {
                touchStartX = e.changedTouches[0].screenX;
                stopAutoplay();
            }, { passive: true });

            slider.addEventListener('touchend', (e) => {
                touchEndX = e.changedTouches[0].screenX;
                const diff = touchEndX - touchStartX;
                if (Math.abs(diff) > 45) {
                    if (diff < 0) {
                        goToSlide(current + 1);
                    } else {
                        goToSlide(current - 1);
                    }
                }
                startAutoplay();
            }, { passive: true });

            startAutoplay();
        });
    };
    initHeroSlider();
});

function initProductDetail() {
    const root = document.querySelector('[data-product-detail]');
    if (!root) return;

    const track = root.querySelector('[data-gallery-track]');
    const galleryPrevBtn = root.querySelector('[data-gallery-prev]');
    const galleryNextBtn = root.querySelector('[data-gallery-next]');
    const dotsWrap = root.querySelector('[data-gallery-dots]');
    const zoomBadge = root.querySelector('[data-gallery-zoom-trigger]');
    const sizeWrap = root.querySelector('[data-size-pills]');
    const colorBtns = root.querySelectorAll('[data-variant-id]');
    const productImages = JSON.parse(root.dataset.productImages || '[]');
    const variants = JSON.parse(root.dataset.variants || '[]');

    let currentImages = (variants[0]?.images && variants[0].images.length) ? variants[0].images : productImages;
    if (!currentImages.length) {
        const firstImg = track?.querySelector('img');
        if (firstImg?.src) currentImages = [firstImg.src];
    }
    let currentImageIndex = 0;

    // Helper to scroll gallery slider
    const scrollToSlide = (index, smooth = true) => {
        if (!track) return;
        const slides = track.querySelectorAll('[data-gallery-slide]');
        if (!slides.length) return;

        currentImageIndex = Math.max(0, Math.min(index, slides.length - 1));
        const targetSlide = slides[currentImageIndex];
        if (targetSlide) {
            track.scrollTo({
                left: targetSlide.offsetLeft,
                behavior: smooth ? 'smooth' : 'auto'
            });
        }
        updateSliderUI();
    };

    const updateSliderUI = () => {
        if (!track) return;
        const slides = track.querySelectorAll('[data-gallery-slide]');
        slides.forEach((slide, i) => {
            slide.classList.toggle('is-active', i === currentImageIndex);
        });

        if (dotsWrap) {
            dotsWrap.querySelectorAll('.gallery-dot').forEach((dot, i) => {
                dot.classList.toggle('is-active', i === currentImageIndex);
            });
        }

        const count = currentImages.length;
        if (galleryPrevBtn) galleryPrevBtn.style.display = count > 1 ? '' : 'none';
        if (galleryNextBtn) galleryNextBtn.style.display = count > 1 ? '' : 'none';
        if (dotsWrap) dotsWrap.style.display = count > 1 ? '' : 'none';
    };

    // Scroll snap sync for touch swipe & scroll
    let scrollDebounce;
    track?.addEventListener('scroll', () => {
        clearTimeout(scrollDebounce);
        scrollDebounce = setTimeout(() => {
            if (!track) return;
            const slideWidth = track.clientWidth || 1;
            const newIndex = Math.round(track.scrollLeft / slideWidth);
            if (newIndex !== currentImageIndex && newIndex >= 0 && newIndex < currentImages.length) {
                currentImageIndex = newIndex;
                updateSliderUI();
            }
        }, 40);
    }, { passive: true });

    // Prev / Next button listeners
    galleryPrevBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        scrollToSlide(currentImageIndex - 1);
    });

    galleryNextBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        scrollToSlide(currentImageIndex + 1);
    });

    // Dots click listener
    dotsWrap?.addEventListener('click', (e) => {
        const dot = e.target.closest('[data-gallery-dot]');
        if (!dot) return;
        e.stopPropagation();
        const idx = Number(dot.dataset.galleryDot);
        scrollToSlide(idx);
    });

    // Mouse drag support on desktop
    let isTrackDown = false;
    let trackStartX = 0;
    let trackScrollLeft = 0;
    let hasTrackMoved = false;

    track?.addEventListener('mousedown', (e) => {
        if (e.button !== 0) return;
        isTrackDown = true;
        hasTrackMoved = false;
        trackStartX = e.pageX - track.offsetLeft;
        trackScrollLeft = track.scrollLeft;
        track.classList.add('is-dragging');
    });

    window.addEventListener('mousemove', (e) => {
        if (!isTrackDown || !track) return;
        const x = e.pageX - track.offsetLeft;
        const walk = x - trackStartX;
        if (Math.abs(walk) > 6) {
            hasTrackMoved = true;
        }
        track.scrollLeft = trackScrollLeft - walk;
    });

    window.addEventListener('mouseup', () => {
        if (!isTrackDown || !track) return;
        isTrackDown = false;
        track.classList.remove('is-dragging');
        if (hasTrackMoved) {
            const slideWidth = track.clientWidth || 1;
            const targetIdx = Math.round(track.scrollLeft / slideWidth);
            scrollToSlide(targetIdx, true);
        }
    });

    // Render gallery for a specific set of images (e.g. variant color change)
    const renderGallery = (images) => {
        const list = images && images.length ? images : productImages;
        if (!track || !list.length) return;

        currentImages = list;
        currentImageIndex = 0;

        // Render slides inside track
        track.innerHTML = list
            .map((src, i) => `
                <div class="gallery-slide ${i === 0 ? 'is-active' : ''}" data-gallery-slide data-index="${i}">
                    <img src="${src}" alt="" loading="${i === 0 ? 'eager' : 'lazy'}">
                </div>
            `)
            .join('');

        // Render dots
        if (dotsWrap) {
            dotsWrap.innerHTML = list
                .map((_, i) => `<button type="button" class="gallery-dot ${i === 0 ? 'is-active' : ''}" data-gallery-dot="${i}" aria-label="Görsel ${i + 1}"></button>`)
                .join('');
        }

        // Scroll track back to start
        track.scrollTo({ left: 0, behavior: 'auto' });
        updateSliderUI();
        syncLightboxImages();
    };

    const renderSizes = (sizes) => {
        if (!sizeWrap) return;
        const entries = Object.entries(sizes || {});
        if (!entries.length) {
            sizeWrap.innerHTML = '<span class="size-pill is-out">Beden yok</span>';
            const stockIndicator = root.querySelector('[data-stock-indicator]');
            const stockDot = root.querySelector('.stock-pulse-dot');
            const stockText = root.querySelector('[data-stock-text]');
            if (stockIndicator && stockDot && stockText) {
                stockIndicator.classList.add('product-detail__stock-indicator--out');
                stockDot.classList.add('stock-pulse-dot--out');
                stockText.textContent = 'Stokta Yok';
            }
            return;
        }

        sizeWrap.innerHTML = entries
            .map(([size, stock]) => {
                const out = Number(stock) <= 0;
                return `<button type="button" class="size-pill ${out ? 'is-out' : ''}" data-size="${size}" title="Stok: ${stock}" ${out ? 'disabled' : ''}>${size}</button>`;
            })
            .join('');

        const totalStock = Object.values(sizes || {}).reduce((sum, stock) => sum + Number(stock), 0);
        const stockIndicator = root.querySelector('[data-stock-indicator]');
        const stockDot = root.querySelector('.stock-pulse-dot');
        const stockText = root.querySelector('[data-stock-text]');
        if (stockIndicator && stockDot && stockText) {
            if (totalStock <= 0) {
                stockIndicator.classList.add('product-detail__stock-indicator--out');
                stockDot.classList.add('stock-pulse-dot--out');
                stockText.textContent = 'Stokta Yok';
            } else {
                stockIndicator.classList.remove('product-detail__stock-indicator--out');
                stockDot.classList.remove('stock-pulse-dot--out');
                stockText.textContent = 'Stokta Mevcut · Hızlı Kargo';
            }
        }
    };

    const activeColorLabel = root.querySelector('[data-active-color-name]');
    const activeSizeLabel = root.querySelector('[data-active-size-name]');

    const updateColorLabel = (name) => {
        if (activeColorLabel) activeColorLabel.textContent = name ? '— ' + name : '';
    };

    const updateSizeLabel = (size) => {
        if (activeSizeLabel) activeSizeLabel.textContent = size ? '— Beden: ' + size : '';
    };

    const firstActiveColor = root.querySelector('[data-variant-id].is-active');
    if (firstActiveColor) {
        const colorName = (firstActiveColor.querySelector('.color-option__name') || {}).textContent || firstActiveColor.getAttribute('title');
        updateColorLabel(colorName?.trim());
    }

    sizeWrap?.addEventListener('click', (e) => {
        const pill = e.target.closest('[data-size]');
        if (!pill || pill.disabled || pill.classList.contains('is-out')) return;
        sizeWrap.querySelectorAll('[data-size]').forEach((el) => el.classList.remove('is-selected'));
        pill.classList.add('is-selected');
        updateSizeLabel(pill.dataset.size);
    });

    colorBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            const id = Number(btn.dataset.variantId);
            const variant = variants.find((v) => Number(v.id) === id);
            if (!variant) return;

            colorBtns.forEach((el) => el.classList.remove('is-active'));
            btn.classList.add('is-active');
            updateColorLabel(variant.color);
            updateSizeLabel(null);

            const images = variant.images && variant.images.length ? variant.images : productImages;
            renderGallery(images);
            renderSizes(variant.sizes || {});
        });
    });

    // ==========================================
    // LIGHTBOX & INSPECTOR CONTROLLER
    // ==========================================
    const lightboxEl = document.getElementById('productLightbox');
    if (!lightboxEl) return;

    const lightboxImg = lightboxEl.querySelector('[data-lightbox-img]');
    const lightboxCanvas = lightboxEl.querySelector('[data-lightbox-canvas]');
    const counterEl = lightboxEl.querySelector('[data-lightbox-counter]');
    const zoomLevelEl = lightboxEl.querySelector('[data-lightbox-zoom-level]');
    const zoomInBtn = lightboxEl.querySelector('[data-lightbox-zoom-in]');
    const zoomOutBtn = lightboxEl.querySelector('[data-lightbox-zoom-out]');
    const resetBtn = lightboxEl.querySelector('[data-lightbox-reset]');
    const prevBtn = lightboxEl.querySelector('[data-lightbox-prev]');
    const nextBtn = lightboxEl.querySelector('[data-lightbox-next]');
    const closeBtns = lightboxEl.querySelectorAll('[data-lightbox-close]');
    const lightboxThumbsWrap = lightboxEl.querySelector('[data-lightbox-thumbs]');
    const hintEl = lightboxEl.querySelector('.lightbox__hint');

    let scale = 1;
    let posX = 0;
    let posY = 0;
    let isDragging = false;
    let dragStartX = 0;
    let dragStartY = 0;
    let lastMouseX = 0;
    let lastMouseY = 0;
    let hasMoved = false;

    function syncLightboxImages() {
        if (!lightboxThumbsWrap) return;
        lightboxThumbsWrap.innerHTML = currentImages
            .map(
                (src, idx) =>
                    `<button type="button" class="lightbox__thumb ${idx === currentImageIndex ? 'is-active' : ''}" data-lightbox-thumb-index="${idx}" aria-label="Görsel ${idx + 1}">
                        <img src="${src}" alt="">
                    </button>`
            )
            .join('');

        if (prevBtn) prevBtn.style.display = currentImages.length > 1 ? '' : 'none';
        if (nextBtn) nextBtn.style.display = currentImages.length > 1 ? '' : 'none';
        if (lightboxThumbsWrap) lightboxThumbsWrap.style.display = currentImages.length > 1 ? '' : 'none';
    }

    function applyTransform() {
        if (!lightboxImg) return;
        if (scale <= 1) {
            scale = 1;
            posX = 0;
            posY = 0;
            lightboxCanvas?.classList.remove('is-zoomed', 'is-dragging');
            lightboxImg.style.transform = '';
        } else {
            const boundW = lightboxImg.offsetWidth || 500;
            const boundH = lightboxImg.offsetHeight || 650;
            const maxPanX = (boundW * (scale - 1)) / 2 + 100;
            const maxPanY = (boundH * (scale - 1)) / 2 + 100;
            posX = Math.max(-maxPanX, Math.min(maxPanX, posX));
            posY = Math.max(-maxPanY, Math.min(maxPanY, posY));

            lightboxCanvas?.classList.add('is-zoomed');
            lightboxImg.style.transform = `translate3d(${posX}px, ${posY}px, 0) scale(${scale})`;
        }

        if (zoomLevelEl) {
            zoomLevelEl.textContent = `${Math.round(scale * 100)}%`;
        }
    }

    function setLightboxImage(idx) {
        if (!currentImages.length) return;
        currentImageIndex = (idx + currentImages.length) % currentImages.length;
        const newSrc = currentImages[currentImageIndex];

        if (lightboxImg) {
            lightboxImg.src = newSrc;
        }

        if (counterEl) {
            counterEl.textContent = `${currentImageIndex + 1} / ${currentImages.length}`;
        }

        if (lightboxThumbsWrap) {
            lightboxThumbsWrap.querySelectorAll('.lightbox__thumb').forEach((thumb, i) => {
                thumb.classList.toggle('is-active', i === currentImageIndex);
            });
            const activeThumb = lightboxThumbsWrap.querySelector('.lightbox__thumb.is-active');
            activeThumb?.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }

        // Sync main gallery slider
        scrollToSlide(currentImageIndex, false);

        scale = 1;
        posX = 0;
        posY = 0;
        applyTransform();
    }

    function openLightbox(initialIdx = null) {
        syncLightboxImages();
        const startIdx = typeof initialIdx === 'number' ? initialIdx : currentImageIndex;
        setLightboxImage(startIdx);

        lightboxEl.classList.add('is-open');
        lightboxEl.setAttribute('aria-hidden', 'false');
        document.body.classList.add('lightbox-open');

        if (hintEl) {
            hintEl.classList.remove('is-hidden');
            setTimeout(() => {
                hintEl.classList.add('is-hidden');
            }, 3500);
        }

        window.addEventListener('keydown', handleKeyNav);
    }

    function closeLightbox() {
        lightboxEl.classList.remove('is-open');
        lightboxEl.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('lightbox-open');
        scale = 1;
        posX = 0;
        posY = 0;
        applyTransform();
        window.removeEventListener('keydown', handleKeyNav);
    }

    function handleKeyNav(e) {
        if (!lightboxEl.classList.contains('is-open')) return;
        if (e.key === 'Escape') {
            closeLightbox();
        } else if (e.key === 'ArrowLeft') {
            setLightboxImage(currentImageIndex - 1);
        } else if (e.key === 'ArrowRight') {
            setLightboxImage(currentImageIndex + 1);
        } else if (e.key === '+' || e.key === '=') {
            scale = Math.min(3.5, scale + 0.3);
            applyTransform();
        } else if (e.key === '-') {
            scale = Math.max(1, scale - 0.3);
            applyTransform();
        } else if (e.key === '0') {
            scale = 1;
            posX = 0;
            posY = 0;
            applyTransform();
        }
    }

    // Open lightbox when clicking the image (without dragging)
    track?.addEventListener('click', (e) => {
        if (hasTrackMoved) return;
        if (e.target.closest('.gallery-nav-btn, .gallery-dots, .gallery-zoom-badge, .product-badge-group')) return;
        openLightbox(currentImageIndex);
    });

    // Open lightbox when clicking "Büyüt" button
    zoomBadge?.addEventListener('click', (e) => {
        e.stopPropagation();
        openLightbox(currentImageIndex);
    });

    closeBtns.forEach((btn) => {
        btn.addEventListener('click', closeLightbox);
    });

    prevBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        setLightboxImage(currentImageIndex - 1);
    });

    nextBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        setLightboxImage(currentImageIndex + 1);
    });

    lightboxThumbsWrap?.addEventListener('click', (e) => {
        const thumb = e.target.closest('[data-lightbox-thumb-index]');
        if (!thumb) return;
        const idx = Number(thumb.dataset.lightboxThumbIndex);
        setLightboxImage(idx);
    });

    zoomInBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        scale = Math.min(3.5, (scale === 1 ? 1.8 : scale + 0.4));
        applyTransform();
        hintEl?.classList.add('is-hidden');
    });

    zoomOutBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        scale = Math.max(1, scale - 0.4);
        applyTransform();
    });

    resetBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        scale = (scale > 1) ? 1 : 2.2;
        applyTransform();
    });

    lightboxCanvas?.addEventListener('wheel', (e) => {
        e.preventDefault();
        const delta = e.deltaY < 0 ? 0.25 : -0.25;
        scale = Math.min(3.5, Math.max(1, scale + delta));
        applyTransform();
        hintEl?.classList.add('is-hidden');
    }, { passive: false });

    lightboxCanvas?.addEventListener('mousedown', (e) => {
        if (e.button !== 0) return;
        hasMoved = false;
        dragStartX = e.clientX - posX;
        dragStartY = e.clientY - posY;
        lastMouseX = e.clientX;
        lastMouseY = e.clientY;

        if (scale > 1) {
            isDragging = true;
            lightboxCanvas.classList.add('is-dragging');
        }
    });

    window.addEventListener('mousemove', (e) => {
        if (!lightboxEl.classList.contains('is-open')) return;
        const dx = Math.abs(e.clientX - lastMouseX);
        const dy = Math.abs(e.clientY - lastMouseY);
        if (dx > 4 || dy > 4) {
            hasMoved = true;
        }

        if (isDragging && scale > 1) {
            posX = e.clientX - dragStartX;
            posY = e.clientY - dragStartY;
            applyTransform();
        }
    });

    window.addEventListener('mouseup', () => {
        if (isDragging) {
            isDragging = false;
            lightboxCanvas?.classList.remove('is-dragging');
        }
    });

    lightboxCanvas?.addEventListener('click', (e) => {
        if (e.target.closest('.lightbox__btn, .lightbox__nav-btn, .lightbox__thumbs')) return;
        if (hasMoved) return;

        if (scale === 1) {
            const rect = lightboxCanvas.getBoundingClientRect();
            const clickX = e.clientX - rect.left - rect.width / 2;
            const clickY = e.clientY - rect.top - rect.height / 2;
            scale = 2.2;
            posX = -clickX * 1.2;
            posY = -clickY * 1.2;
        } else {
            scale = 1;
            posX = 0;
            posY = 0;
        }
        applyTransform();
        hintEl?.classList.add('is-hidden');
    });

    let touchStartX = 0;
    let touchStartY = 0;
    let initialPinchDist = 0;
    let initialPinchScale = 1;

    lightboxCanvas?.addEventListener('touchstart', (e) => {
        if (e.touches.length === 1) {
            hasMoved = false;
            touchStartX = e.touches[0].clientX - posX;
            touchStartY = e.touches[0].clientY - posY;
            lastMouseX = e.touches[0].clientX;
            lastMouseY = e.touches[0].clientY;
            if (scale > 1) {
                isDragging = true;
            }
        } else if (e.touches.length === 2) {
            isDragging = false;
            initialPinchDist = Math.hypot(
                e.touches[0].clientX - e.touches[1].clientX,
                e.touches[0].clientY - e.touches[1].clientY
            );
            initialPinchScale = scale;
        }
    }, { passive: true });

    lightboxCanvas?.addEventListener('touchmove', (e) => {
        if (e.touches.length === 1 && isDragging && scale > 1) {
            const dx = Math.abs(e.touches[0].clientX - lastMouseX);
            const dy = Math.abs(e.touches[0].clientY - lastMouseY);
            if (dx > 4 || dy > 4) hasMoved = true;

            posX = e.touches[0].clientX - touchStartX;
            posY = e.touches[0].clientY - touchStartY;
            applyTransform();
        } else if (e.touches.length === 2 && initialPinchDist > 0) {
            const dist = Math.hypot(
                e.touches[0].clientX - e.touches[1].clientX,
                e.touches[0].clientY - e.touches[1].clientY
            );
            const pinchRatio = dist / initialPinchDist;
            scale = Math.min(3.5, Math.max(1, initialPinchScale * pinchRatio));
            applyTransform();
            hasMoved = true;
            hintEl?.classList.add('is-hidden');
        }
    }, { passive: true });

    lightboxCanvas?.addEventListener('touchend', () => {
        isDragging = false;
        initialPinchDist = 0;
    }, { passive: true });
}

function initLiveSearch() {
    const searchForms = document.querySelectorAll('[data-live-search]');
    if (!searchForms.length) return;

    searchForms.forEach((form) => {
        const input = form.querySelector('input[type="search"]');
        const dropdown = form.querySelector('[data-search-results]');
        if (!input || !dropdown) return;

        const endpoint = input.dataset.searchEndpoint || (window.location.pathname.startsWith('/simgevip/public') ? '/simgevip/public/arama/canli' : '/arama/canli');
        let debounceTimer = null;
        let abortCtrl = null;
        const cache = {};
        let activeIndex = -1;

        const closeDropdown = () => {
            dropdown.classList.remove('is-open');
            dropdown.setAttribute('aria-hidden', 'true');
            activeIndex = -1;
        };

        const openDropdown = () => {
            dropdown.classList.add('is-open');
            dropdown.setAttribute('aria-hidden', 'false');
        };

        const highlightActiveItem = (items) => {
            items.forEach((item, i) => {
                item.classList.toggle('is-active', i === activeIndex);
            });
            if (activeIndex >= 0 && items[activeIndex]) {
                items[activeIndex].scrollIntoView({ block: 'nearest' });
            }
        };

        const renderResults = (data) => {
            if (!data.results || data.results.length === 0) {
                dropdown.innerHTML = `
                    <div class="search-dropdown__empty">
                        <p class="search-dropdown__empty-title">"${escapeHtml(data.query)}" ile ilgili ürün bulunamadı.</p>
                        <p class="search-dropdown__empty-sub">Farklı bir arama terimi deneyebilir veya koleksiyonumuzu inceleyebilirsiniz.</p>
                    </div>
                `;
                openDropdown();
                return;
            }

            const itemsHtml = data.results.map((item) => {
                const priceHtml = item.discounted_price
                    ? `<span class="search-item__price search-item__price--sale">${item.discounted_price}</span>
                       <span class="search-item__price search-item__price--old">${item.price}</span>`
                    : `<span class="search-item__price">${item.price}</span>`;

                const badgeHtml = item.badge
                    ? `<span class="search-item__badge">${escapeHtml(item.badge)}</span>`
                    : '';

                return `
                    <a href="${item.url}" class="search-item" data-search-item>
                        <div class="search-item__thumb">
                            <img src="${item.image}" alt="${escapeHtml(item.name)}" loading="lazy" />
                        </div>
                        <div class="search-item__info">
                            <div class="search-item__meta">
                                <span class="search-item__tag">${escapeHtml(item.category)}</span>
                                ${badgeHtml}
                            </div>
                            <span class="search-item__title">${escapeHtml(item.name)}</span>
                            <div class="search-item__price-wrap">
                                ${priceHtml}
                            </div>
                        </div>
                        <svg class="search-item__arrow icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 12h14"/><path d="M13 6l6 6-6 6"/>
                        </svg>
                    </a>
                `;
            }).join('');

            dropdown.innerHTML = `
                <div class="search-dropdown__header">
                    <span>${data.results.length} ürün listeleniyor</span>
                    <span class="search-dropdown__count">Toplam ${data.total}</span>
                </div>
                <div class="search-dropdown__list">
                    ${itemsHtml}
                </div>
                <div class="search-dropdown__footer">
                    <a href="${data.view_all_url}" class="search-dropdown__all">
                        <span>Tüm ${data.total} sonucu incele</span>
                        <svg class="icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 12h14"/><path d="M13 6l6 6-6 6"/>
                        </svg>
                    </a>
                </div>
            `;
            openDropdown();
            activeIndex = -1;
        };

        const renderLoading = () => {
            dropdown.innerHTML = `
                <div class="search-dropdown__loading">
                    <svg class="search-spinner" viewBox="0 0 24 24" width="16" height="16">
                        <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-dasharray="28" stroke-dashoffset="10"/>
                    </svg>
                    <span>Koleksiyon taranıyor...</span>
                </div>
            `;
            openDropdown();
        };

        const performSearch = (query) => {
            const trimmed = (query || '').trim();
            if (trimmed.length < 2) {
                closeDropdown();
                return;
            }

            const desktopResults = window.matchMedia('(min-width: 992px)').matches;
            const cacheKey = desktopResults ? trimmed + '|12' : trimmed;

            if (cache[cacheKey]) {
                renderResults(cache[cacheKey]);
                return;
            }

            if (abortCtrl) {
                abortCtrl.abort();
            }
            abortCtrl = new AbortController();

            renderLoading();

            const url = new URL(endpoint, window.location.href);
            url.searchParams.set('q', trimmed);
            if (desktopResults) {
                url.searchParams.set('limit', '12');
            }

            fetch(url.toString(), {
                signal: abortCtrl.signal,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then((res) => {
                    if (!res.ok) throw new Error('Search failed');
                    return res.json();
                })
                .then((data) => {
                    cache[cacheKey] = data;
                    renderResults(data);
                })
                .catch((err) => {
                    if (err.name === 'AbortError') return;
                    dropdown.innerHTML = `
                    <div class="search-dropdown__empty">
                        <p class="search-dropdown__empty-title">Arama sırasında bir hata oluştu.</p>
                    </div>
                `;
                    openDropdown();
                });
        };

        const handleInput = () => {
            clearTimeout(debounceTimer);
            const val = input.value;
            debounceTimer = setTimeout(() => {
                performSearch(val);
            }, 180);
        };

        input.addEventListener('input', handleInput);
        input.addEventListener('keyup', handleInput);
        input.addEventListener('paste', handleInput);
        input.addEventListener('change', handleInput);
        input.addEventListener('focus', () => {
            if (input.value.trim().length >= 2) {
                performSearch(input.value);
            }
        });

        input.addEventListener('keydown', (e) => {
            const items = dropdown.querySelectorAll('[data-search-item]');
            if (!items.length || !dropdown.classList.contains('is-open')) {
                if (e.key === 'Escape') closeDropdown();
                return;
            }

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = (activeIndex + 1) % items.length;
                highlightActiveItem(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = (activeIndex - 1 + items.length) % items.length;
                highlightActiveItem(items);
            } else if (e.key === 'Enter') {
                if (activeIndex >= 0 && items[activeIndex]) {
                    e.preventDefault();
                    window.location.href = items[activeIndex].href;
                }
            } else if (e.key === 'Escape') {
                closeDropdown();
            }
        });

        document.addEventListener('click', (e) => {
            if (!form.contains(e.target)) {
                closeDropdown();
            }
        });
    });
}

function storeIcon(name, size) {
    const s = size || 16;
    const common = `class="icon icon--${name}" width="${s}" height="${s}" viewBox="0 0 24 24" aria-hidden="true" focusable="false"`;
    if (name === 'whatsapp') {
        return `<svg ${common} fill="currentColor"><path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.48-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.61-.92-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.87 1.22 3.07c.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.63.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35z"/><path d="M12.04 2C6.55 2 2.1 6.45 2.1 11.94c0 1.76.46 3.48 1.34 5L2 22l5.2-1.36a9.9 9.9 0 004.84 1.23h.01c5.49 0 9.94-4.45 9.94-9.94S17.53 2 12.04 2zm0 18.15h-.01a8.2 8.2 0 01-4.18-1.15l-.3-.18-3.09.81.82-3.01-.2-.31a8.2 8.2 0 01-1.26-4.37c0-4.53 3.69-8.21 8.22-8.21 2.2 0 4.26.86 5.81 2.41a8.16 8.16 0 012.41 5.8c0 4.53-3.69 8.21-8.22 8.21z"/></svg>`;
    }
    const paths = {
        'arrow-right': '<path d="M5 12h14"/><path d="M13 6l6 6-6 6"/>',
        plus: '<path d="M12 5v14"/><path d="M5 12h14"/>',
        minus: '<path d="M5 12h14"/>',
        x: '<path d="M6 6l12 12"/><path d="M18 6L6 18"/>',
        bag: '<path d="M6 8h12l1 12H5L6 8z"/><path d="M9 8V6a3 3 0 016 0v2"/>',
        trash: '<path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/>',
        tag: '<path d="M20.6 13.4l-7.2 7.2a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8z"/><circle cx="7.5" cy="7.5" r="1.2" fill="currentColor" stroke="none"/>',
        shield: '<path d="M12 3l8 3v6c0 5-3.5 8.5-8 9.5C7.5 20.5 4 17 4 12V6l8-3z"/><path d="M9 12l2 2 4-4"/>',
        spark: '<path d="M12 3v4"/><path d="M12 17v4"/><path d="M3 12h4"/><path d="M17 12h4"/><path d="M5.6 5.6l2.8 2.8"/><path d="M15.6 15.6l2.8 2.8"/><path d="M18.4 5.6l-2.8 2.8"/><path d="M8.4 15.6l-2.8 2.8"/>',
    };
    return `<svg ${common} fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">${paths[name] || ''}</svg>`;
}

function storeConfig() {
    return window.SimgeStore || { csrf: '', customer: false, routes: {}, whatsapp: '905550000000', cartCount: 0 };
}

function redirectToCustomerLogin() {
    const cfg = storeConfig();
    const next = window.location.pathname + window.location.search;
    const login = cfg.routes.login || '/giris';
    const join = login.includes('?') ? '&' : '?';
    window.location.href = login + join + 'next=' + encodeURIComponent(next);
}

function requireCustomer() {
    if (storeConfig().customer) return true;
    redirectToCustomerLogin();
    return false;
}

function showToast(message, type = 'success') {
    const toast = document.querySelector('[data-store-toast]');
    if (!toast) {
        window.alert(message);
        return;
    }
    if (!message) return;
    
    // Geriye dönük uyumluluk (önceki true/false kullanımı)
    if (type === true) type = 'error';
    if (type === false) type = 'success';
    
    clearTimeout(showToast.hideTimer);
    clearTimeout(showToast.finishTimer);
    toast.classList.remove('is-visible', 'is-success', 'is-error', 'is-warning');
    toast.hidden = false;
    
    toast.classList.add(`is-${type}`);
    toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
    toast.setAttribute('aria-live', type === 'error' ? 'assertive' : 'polite');
    
    let symbol = '✓';
    if (type === 'error') symbol = '✖';
    if (type === 'warning') symbol = '!';
    toast.querySelector('[data-toast-symbol]').textContent = symbol;
    
    toast.querySelector('[data-toast-message]').textContent = message;
    void toast.offsetWidth;
    requestAnimationFrame(() => toast.classList.add('is-visible'));
    showToast.hideTimer = setTimeout(hideToast, 4200);
}

function hideToast() {
    const toast = document.querySelector('[data-store-toast]');
    if (!toast) return;
    clearTimeout(showToast.hideTimer);
    toast.classList.remove('is-visible');
    clearTimeout(showToast.finishTimer);
    showToast.finishTimer = setTimeout(() => { toast.hidden = true; }, 300);
}

function initStoreToast() {
    const toast = document.querySelector('[data-store-toast]');
    if (!toast) return;
    toast.querySelector('[data-toast-close]')?.addEventListener('click', hideToast);
    if (toast.dataset.initialMessage) {
        showToast(toast.dataset.initialMessage, toast.dataset.initialType || 'success');
    }
}

function initFavoriteActions() {
    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('[data-favorite-form]');
        if (!form) return;
        event.preventDefault();

        const productId = form.dataset.favoriteProductId;
        const matchingForms = Array.from(document.querySelectorAll('[data-favorite-form]'))
            .filter((item) => item.dataset.favoriteProductId === productId);
        if (matchingForms.some((item) => item.dataset.favoritePending === '1')) return;

        matchingForms.forEach((item) => {
            item.dataset.favoritePending = '1';
            item.querySelector('button')?.setAttribute('disabled', 'disabled');
        });

        try {
            const result = await apiRequest(form.action, { method: 'POST' });
            matchingForms.forEach((item) => {
                const active = Boolean(result.is_favorite);
                const button = item.querySelector('button');
                item.classList.toggle('is-favorite', active);
                button?.setAttribute('aria-pressed', active ? 'true' : 'false');
                if (button) {
                    const actionLabel = active ? 'favorilerden kaldır' : 'favorilere ekle';
                    button.title = actionLabel;
                    button.setAttribute('aria-label', `${item.dataset.favoriteProductName} ${actionLabel}`);
                }
                const label = item.querySelector('[data-favorite-label]');
                if (label) label.textContent = active ? 'Favorilerimden Kaldır' : 'Favorilerime Ekle';
            });
            showToast(result.message || 'Favoriler güncellendi.');
        } catch (error) {
            showToast(error.message || 'Favoriler güncellenemedi.', true);
        } finally {
            matchingForms.forEach((item) => {
                delete item.dataset.favoritePending;
                item.querySelector('button')?.removeAttribute('disabled');
            });
        }
    });
}

async function apiRequest(url, options) {
    const cfg = storeConfig();
    const res = await fetch(url, {
        ...options,
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': cfg.csrf,
            'X-Requested-With': 'XMLHttpRequest',
            ...(options && options.headers ? options.headers : {}),
        },
        credentials: 'same-origin',
    });

    let data = null;
    try {
        data = await res.json();
    } catch (e) {
        data = null;
    }

    if (res.status === 401) {
        redirectToCustomerLogin();
        throw new Error('Bu işlem için giriş yapmalısınız.');
    }

    if (!res.ok) {
        const msg = (data && (data.message || (data.errors && Object.values(data.errors)[0][0]))) || 'İşlem tamamlanamadı.';
        throw new Error(msg);
    }

    return data;
}

function updateCartCount(count) {
    document.querySelectorAll('[data-cart-count]').forEach((el) => {
        el.textContent = String(count || 0);
        el.classList.toggle('is-empty', !count);
    });
    if (window.SimgeStore) window.SimgeStore.cartCount = count || 0;
}

function renderCartDrawer(cart) {
    const body = document.querySelector('[data-cart-items]');
    const foot = document.querySelector('[data-cart-foot]');
    const headerCount = document.querySelector('[data-cart-header-count]');
    const originalTotalEl = document.querySelector('[data-cart-original-total]');
    const discountRow = document.querySelector('[data-cart-row-discount]');
    const discountTotalEl = document.querySelector('[data-cart-discount-total]');
    const grandTotalEl = document.querySelector('[data-cart-grand-total]');
    const subtotalLegacy = document.querySelector('[data-cart-subtotal]');
    const savingsBanner = document.querySelector('[data-cart-savings-banner]');
    const savingsAmount = document.querySelector('[data-cart-savings-amount]');
    const wa = document.querySelector('[data-cart-whatsapp]');
    if (!body || !foot) return;

    const items = (cart && cart.items) || [];
    const count = cart ? (cart.count || 0) : 0;
    updateCartCount(count);
    if (headerCount) headerCount.textContent = count;

    if (!items.length) {
        const shopUrl = window.location.origin + (window.location.pathname.startsWith('/simgevip/public') ? '/simgevip/public/koleksiyon' : '/koleksiyon');
        body.innerHTML = `
            <div class="cart-drawer__empty">
                <div class="cart-drawer__empty-icon">
                    ${storeIcon('bag', 36)}
                </div>
                <h3>Sepetiniz Henüz Boş</h3>
                <p>Koleksiyonumuzdaki zarif parçaları inceleyip beğendiklerinizi sepete ekleyebilirsiniz.</p>
                <a href="${shopUrl}" class="btn btn--dark btn--sm cart-drawer__empty-btn">Koleksiyonu Keşfet</a>
            </div>
        `;
        foot.hidden = true;
        return;
    }

    body.innerHTML = items
        .map((item) => {
            const meta = [
                item.color ? `Renk: ${item.color}` : null,
                item.size ? `Beden: ${item.size}` : null
            ].filter(Boolean).join(' · ');

            const price = Number(item.price || 0);
            const originalPrice = Number(item.original_price || item.price || 0);
            const qty = Number(item.qty || 1);
            const lineTotal = item.line_total != null ? Number(item.line_total) : price * qty;
            const unitDiscounted = originalPrice > price + 0.009;

            const priceFormatted = price.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' TL';
            const originalPriceFormatted = originalPrice.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' TL';
            const lineTotalFormatted = item.line_total_formatted
                || (lineTotal.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' TL');

            let badgeHtml = '';
            if (unitDiscounted) {
                const discountText = item.discount_percent ? `%${item.discount_percent} İndirim` : (item.campaign_badge || 'İndirimli');
                badgeHtml += `<span class="cart-line__badge">${escapeHtml(discountText)}</span>`;
            }
            if (item.nth_discount_note) {
                badgeHtml += `<span class="cart-line__badge">${escapeHtml(item.nth_discount_note)}</span>`;
            }
            if (item.nth_pending_note) {
                badgeHtml += `<span class="cart-line__badge">${escapeHtml(item.nth_pending_note)}</span>`;
            }

            return `
                <article class="cart-line" data-cart-key="${escapeHtml(item.key)}">
                    <a href="${escapeHtml(item.url)}" class="cart-line__thumb">
                        <img src="${escapeHtml(item.image)}" alt="${escapeHtml(item.name)}" loading="lazy">
                    </a>
                    <div class="cart-line__info">
                        <div class="cart-line__top">
                            <a href="${escapeHtml(item.url)}" class="cart-line__name">${escapeHtml(item.name)}</a>
                            <button type="button" class="cart-line__remove" data-cart-remove aria-label="Sepetten Kaldır" title="Sepetten Kaldır">
                                ${storeIcon('trash', 14)}
                            </button>
                        </div>

                        ${meta ? `<p class="cart-line__meta">${escapeHtml(meta)}</p>` : ''}

                        <div class="cart-line__pricing">
                            ${unitDiscounted ? `
                                <span class="cart-line__price-old">${originalPriceFormatted}</span>
                                <span class="cart-line__price-new">${priceFormatted}</span>
                                ${badgeHtml}
                            ` : `
                                <span class="cart-line__price-current">${priceFormatted}</span>
                                ${badgeHtml}
                            `}
                        </div>

                        <div class="cart-line__bottom">
                            <div class="cart-line__qty">
                                <button type="button" class="cart-line__qty-btn" data-cart-qty="-1" aria-label="Adeti Azalt" ${qty <= 1 ? 'title="Sepetten Kaldır"' : ''}>
                                    ${storeIcon('minus', 11)}
                                </button>
                                <span class="cart-line__qty-val" data-cart-qty-val>${qty}</span>
                                <button type="button" class="cart-line__qty-btn" data-cart-qty="1" aria-label="Adeti Artır">
                                    ${storeIcon('plus', 11)}
                                </button>
                            </div>
                            <div class="cart-line__total" title="Satır Toplamı">
                                <span>${lineTotalFormatted}</span>
                            </div>
                        </div>
                    </div>
                </article>
            `;
        })
        .join('');

    foot.hidden = false;

    const originalTotalFormatted = cart.original_total_formatted || cart.subtotal_formatted || '0,00 TL';
    const grandTotalFormatted = cart.grand_total_formatted || cart.subtotal_formatted || '0,00 TL';
    const hasDiscount = Boolean(cart.has_discount && Number(cart.discount_total || 0) > 0);

    if (originalTotalEl) originalTotalEl.textContent = originalTotalFormatted;
    if (grandTotalEl) grandTotalEl.textContent = grandTotalFormatted;
    if (subtotalLegacy) subtotalLegacy.textContent = grandTotalFormatted;

    if (discountRow) {
        discountRow.hidden = !hasDiscount;
        if (discountTotalEl) {
            discountTotalEl.textContent = cart.discount_total_formatted || '-0,00 TL';
        }
    }

    if (savingsBanner) {
        savingsBanner.hidden = !hasDiscount;
        if (savingsAmount && cart.savings_formatted) {
            savingsAmount.textContent = cart.savings_formatted;
        }
    }

    if (wa) {
        wa.href = cart.whatsapp_url || '#';
    }
}

function openCartDrawer() {
    const drawer = document.querySelector('[data-cart-drawer]');
    if (!drawer) return;
    drawer.classList.add('is-open');
    drawer.setAttribute('aria-hidden', 'false');
    document.body.classList.add('cart-open');
}

function closeCartDrawer() {
    const drawer = document.querySelector('[data-cart-drawer]');
    if (!drawer) return;
    drawer.classList.remove('is-open');
    drawer.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('cart-open');
}

async function refreshCart() {
    const cfg = storeConfig();
    if (!cfg.routes || !cfg.routes.cartIndex) return null;
    const cart = await apiRequest(cfg.routes.cartIndex, { method: 'GET' });
    renderCartDrawer(cart);
    return cart;
}

function getSelectionFromContext(actionsEl) {
    const detail = actionsEl.closest('[data-product-detail]') || document.querySelector('[data-product-detail]');
    if (!detail) {
        return { variantId: null, color: null, size: null };
    }

    const variantBtn = detail.querySelector('[data-variant-id].is-active');
    const sizeBtn = detail.querySelector('[data-size].is-selected');
    const colorName = variantBtn
        ? ((variantBtn.querySelector('.color-option__name') || {}).textContent || variantBtn.getAttribute('title'))
        : null;

    return {
        variantId: variantBtn ? Number(variantBtn.dataset.variantId) : null,
        color: colorName ? String(colorName).trim() : null,
        size: sizeBtn ? sizeBtn.dataset.size : null,
    };
}

function buildWhatsAppProductUrl(actionsEl) {
    const cfg = storeConfig();
    const sel = getSelectionFromContext(actionsEl);
    const lines = [
        'Merhaba, bu ürünü sipariş etmek istiyorum:',
        actionsEl.dataset.productName || '',
        actionsEl.dataset.productSku ? 'Stok kodu: ' + actionsEl.dataset.productSku : null,
        sel.color ? 'Renk: ' + sel.color : null,
        sel.size ? 'Beden: ' + sel.size : null,
        actionsEl.dataset.productPrice ? 'Fiyat: ' + actionsEl.dataset.productPrice : null,
        actionsEl.dataset.productUrl || '',
    ].filter(Boolean);

    return 'https://wa.me/' + (cfg.whatsapp || '905550000000') + '?text=' + encodeURIComponent(lines.join('\n'));
}

function openHoldModal(actionsEl, show = true) {
    const modal = document.querySelector('[data-hold-modal]');
    if (!modal) return;
    const sel = getSelectionFromContext(actionsEl);
    const form = modal.querySelector('[data-hold-form]');
    const label = modal.querySelector('[data-hold-product-label]');
    const err = modal.querySelector('[data-hold-error]');

    form.querySelector('[data-hold-product-id]').value = actionsEl.dataset.productId || '';
    form.querySelector('[data-hold-variant-id]').value = sel.variantId || '';
    form.querySelector('[data-hold-color]').value = sel.color || '';
    form.querySelector('[data-hold-size]').value = sel.size || '';
    if (label) {
        const bits = [actionsEl.dataset.productName, sel.color, sel.size].filter(Boolean);
        label.textContent = bits.join(' · ') + ' ürününü mağazada sizin için ayıralım.';
    }
    if (err) {
        err.hidden = true;
        err.textContent = '';
    }
    if (show) {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    }
    return form;
}

function closeHoldModal() {
    const modal = document.querySelector('[data-hold-modal]');
    if (!modal) return;
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
}

function initCartAndActions() {
    const cfg = storeConfig();
    updateCartCount(cfg.cartCount || 0);

    document.querySelector('[data-cart-whatsapp]')?.addEventListener('click', async (e) => {
        const termsCheckbox = document.getElementById('cart-terms');
        if (termsCheckbox && !termsCheckbox.checked) {
            e.preventDefault();
            showToast('Lütfen Siparişi tamamlamadan önce sözleşmeleri onaylayın.', true);
            return;
        }

        if (!cfg.routes?.customerOrderRequest) return;
        e.preventDefault();
        const popup = window.open('', '_blank');
        if (popup) popup.opener = null;
        try {
            const result = await apiRequest(cfg.routes.customerOrderRequest, {
                method: 'POST',
                body: JSON.stringify({ source: 'cart' }),
            });
            if (popup) popup.location.href = result.whatsapp_url;
            else window.location.href = result.whatsapp_url;
        } catch (error) {
            if (popup) popup.close();
            showToast(error.message, true);
        }
    });

    document.querySelectorAll('[data-cart-open]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            try {
                await refreshCart();
            } catch (e) {
                /* ignore */
            }
            openCartDrawer();
        });
    });

    document.querySelectorAll('[data-cart-close]').forEach((el) => {
        el.addEventListener('click', closeCartDrawer);
    });

    document.querySelectorAll('[data-hold-close]').forEach((el) => {
        el.addEventListener('click', closeHoldModal);
    });

    const cartItems = document.querySelector('[data-cart-items]');
    cartItems?.addEventListener('click', async (e) => {
        const line = e.target.closest('[data-cart-key]');
        if (!line) return;
        const key = line.dataset.cartKey;
        try {
            if (e.target.closest('[data-cart-remove]')) {
                const cart = await apiRequest(cfg.routes.cartDestroy, {
                    method: 'POST',
                    body: JSON.stringify({ key }),
                });
                renderCartDrawer(cart.cart);
                return;
            }
            const qtyBtn = e.target.closest('[data-cart-qty]');
            if (qtyBtn) {
                const delta = Number(qtyBtn.dataset.cartQty);
                const qtyValEl = line.querySelector('[data-cart-qty-val]') || line.querySelector('.cart-line__qty span');
                const current = Number(qtyValEl ? qtyValEl.textContent : 1);
                const cart = await apiRequest(cfg.routes.cartUpdate, {
                    method: 'POST',
                    body: JSON.stringify({ key, qty: Math.max(0, current + delta) }),
                });
                renderCartDrawer(cart.cart);
            }
        } catch (err) {
            showToast(err.message, true);
        }
    });

    // Delegated click handler for all product actions (supports dynamically added and cloned cards)
    document.addEventListener('click', async (e) => {
        // WhatsApp button
        const wa = e.target.closest('[data-whatsapp-order]');
        if (wa) {
            e.preventDefault();
            const actionsEl = wa.closest('[data-product-actions]');
            if (!actionsEl) return;

            const hasVariants = actionsEl.dataset.hasVariants === '1';
            const isDetailPage = !!document.querySelector('[data-product-detail]');
            const sel = getSelectionFromContext(actionsEl);

            if (hasVariants && !isDetailPage) {
                if (actionsEl.dataset.productUrl) {
                    window.location.href = actionsEl.dataset.productUrl;
                }
                return;
            }

            if (hasVariants && isDetailPage) {
                const detail = actionsEl.closest('[data-product-detail]') || document.querySelector('[data-product-detail]');
                const hasColorOptions = !!detail?.querySelector('[data-color-options] [data-variant-id]');
                const hasSizeOptions = !!detail?.querySelector('[data-size-pills] [data-size]');

                if (hasColorOptions && !sel.variantId) {
                    showToast('Lütfen renk seçin.', true);
                    detail?.querySelector('[data-color-options]')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }

                if (hasSizeOptions && !sel.size) {
                    showToast('Lütfen beden seçin.', true);
                    detail?.querySelector('[data-size-pills]')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }
            }

            let waUrl = buildWhatsAppProductUrl(actionsEl);
            if (cfg.routes?.customerOrderRequest) {
                const popup = window.open('', '_blank');
                if (popup) popup.opener = null;
                try {
                    const result = await apiRequest(cfg.routes.customerOrderRequest, {
                        method: 'POST',
                        body: JSON.stringify({
                            source: 'product',
                            product_id: Number(actionsEl.dataset.productId),
                            variant_id: sel.variantId,
                            size: sel.size,
                        }),
                    });
                    waUrl = result.whatsapp_url;
                    if (popup) popup.location.href = waUrl;
                    else window.location.href = waUrl;
                } catch (error) {
                    if (popup) popup.close();
                    showToast(error.message, true);
                }
                return;
            }
            const opened = window.open(waUrl, '_blank', 'noopener,noreferrer');
            if (!opened || opened.closed || typeof opened.closed === 'undefined') {
                window.location.href = waUrl;
            }
            return;
        }

        // Add to Cart button
        const addBtn = e.target.closest('[data-add-to-cart]');
        if (addBtn) {
            if (!requireCustomer()) return;
            const actionsEl = addBtn.closest('[data-product-actions]');
            if (!actionsEl) return;

            const hasVariants = actionsEl.dataset.hasVariants === '1';
            const sel = getSelectionFromContext(actionsEl);
            const hint = actionsEl.parentElement?.querySelector('[data-product-action-hint]');

            const isDetailPage = !!document.querySelector('[data-product-detail]');

            if (hasVariants && !sel.variantId) {
                if (!isDetailPage && actionsEl.dataset.productUrl) {
                    window.location.href = actionsEl.dataset.productUrl;
                    return;
                }
                const msg = 'Lütfen renk seçin.';
                if (hint) { hint.hidden = false; hint.textContent = msg; }
                showToast(msg, true);
                document.querySelector('[data-color-options]')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            if (hasVariants && !sel.size) {
                if (!isDetailPage && actionsEl.dataset.productUrl) {
                    window.location.href = actionsEl.dataset.productUrl;
                    return;
                }
                const msg = 'Lütfen beden seçin.';
                if (hint) { hint.hidden = false; hint.textContent = msg; }
                showToast(msg, true);
                document.querySelector('[data-size-pills]')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            if (hint) hint.hidden = true;

            try {
                const data = await apiRequest(cfg.routes.cartStore, {
                    method: 'POST',
                    body: JSON.stringify({
                        product_id: Number(actionsEl.dataset.productId),
                        variant_id: sel.variantId,
                        size: sel.size,
                        qty: 1,
                    }),
                });
                renderCartDrawer(data.cart);
                showToast(data.message || 'Sepete eklendi.');
                openCartDrawer();
            } catch (err) {
                showToast(err.message, true);
            }
            return;
        }

        // Store Hold button
        const holdBtn = e.target.closest('[data-store-hold]');
        if (holdBtn) {
            if (!requireCustomer()) return;
            const actionsEl = holdBtn.closest('[data-product-actions]');
            if (!actionsEl) return;

            const hasVariants = actionsEl.dataset.hasVariants === '1';
            const isDetailPage = !!document.querySelector('[data-product-detail]');
            const sel = getSelectionFromContext(actionsEl);

            if (hasVariants && !isDetailPage) {
                if (actionsEl.dataset.productUrl) {
                    window.location.href = actionsEl.dataset.productUrl;
                }
                return;
            }

            if (hasVariants && isDetailPage) {
                const detail = actionsEl.closest('[data-product-detail]') || document.querySelector('[data-product-detail]');
                const hasColorOptions = !!detail?.querySelector('[data-color-options] [data-variant-id]');
                const hasSizeOptions = !!detail?.querySelector('[data-size-pills] [data-size]');

                if (hasColorOptions && !sel.variantId) {
                    showToast('Lütfen mağazada ayırmak için renk seçin.', true);
                    detail?.querySelector('[data-color-options]')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }

                if (hasSizeOptions && !sel.size) {
                    showToast('Lütfen mağazada ayırmak için beden seçin.', true);
                    detail?.querySelector('[data-size-pills]')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }
            }
            const holdModal = document.querySelector('[data-hold-modal]');
            if (holdModal?.dataset.customerSignedIn === '1' && holdModal.dataset.customerReady === '1') {
                const form = openHoldModal(actionsEl, false);
                if (form) submitHoldForm(form, cfg);
            } else {
                openHoldModal(actionsEl);
            }
            return;
        }
    });

    // Populate initial WhatsApp links (only for variantless products; products with variants require selection)
    document.querySelectorAll('[data-product-actions]').forEach((actionsEl) => {
        const wa = actionsEl.querySelector('[data-whatsapp-order]');
        if (wa) {
            if (actionsEl.dataset.hasVariants !== '1') {
                wa.href = buildWhatsAppProductUrl(actionsEl);
            } else {
                wa.href = '#';
            }
        }
    });

    document.querySelector('[data-hold-form]')?.addEventListener('submit', (e) => {
        e.preventDefault();
        submitHoldForm(e.currentTarget, cfg);
    });
}

async function submitHoldForm(form, cfg) {
        if (form.dataset.pending === '1') return;
        form.dataset.pending = '1';
        const submitButton = form.querySelector('[type="submit"]');
        if (submitButton) submitButton.disabled = true;
        const err = document.querySelector('[data-hold-error]');
        const payload = Object.fromEntries(new FormData(form).entries());
        payload.product_id = Number(payload.product_id);
        if (payload.variant_id) payload.variant_id = Number(payload.variant_id);
        else delete payload.variant_id;

        try {
            const data = await apiRequest(cfg.routes.storeHold, {
                method: 'POST',
                body: JSON.stringify(payload),
            });
            form.reset();
            closeHoldModal();
            showToast(data.message || 'Talebiniz alındı.');
        } catch (ex) {
            if (err) {
                err.hidden = false;
                err.textContent = ex.message;
            }
            showToast(ex.message, true);
        } finally {
            delete form.dataset.pending;
            if (submitButton) submitButton.disabled = false;
        }
}

/**
 * Initializes category product carousels (4 products per row on desktop, 2 on mobile).
 * No automatic scrolling; navigation is strictly click-driven (< / > buttons and dots)
 * plus native touch swipe with scroll snap on mobile.
 */
function initCategoryCarousels() {
    const carousels = document.querySelectorAll('[data-category-carousel]');
    if (!carousels.length) return;

    carousels.forEach((carousel) => {
        const viewport = carousel.querySelector('[data-carousel-viewport]');
        const track = carousel.querySelector('[data-carousel-track]');
        const items = track ? Array.from(track.querySelectorAll('[data-carousel-item]')) : [];
        if (!viewport || !track || !items.length) return;

        const prevBtns = carousel.querySelectorAll('[data-carousel-prev]');
        const nextBtns = carousel.querySelectorAll('[data-carousel-next]');
        const pagePill = carousel.querySelector('[data-carousel-pages]');
        const dotsWrap = carousel.querySelector('[data-carousel-dots]');

        const getVisibleCount = () => {
            if (window.innerWidth <= 767) return 2;
            if (window.innerWidth <= 1024) return 3;
            return 4;
        };

        const getTotalPages = () => {
            const visible = getVisibleCount();
            return Math.max(1, Math.ceil(items.length / visible));
        };

        const getCurrentPage = () => {
            const visible = getVisibleCount();
            const total = getTotalPages();
            if (total <= 1) return 0;

            const scrollLeft = viewport.scrollLeft;
            let closestItemIndex = 0;
            let minDiff = Infinity;

            items.forEach((item, idx) => {
                const diff = Math.abs(item.offsetLeft - track.offsetLeft - scrollLeft);
                if (diff < minDiff) {
                    minDiff = diff;
                    closestItemIndex = idx;
                }
            });

            return Math.min(total - 1, Math.floor(closestItemIndex / visible));
        };

        const goToPage = (pageIndex) => {
            const visible = getVisibleCount();
            const total = getTotalPages();
            const targetPage = Math.max(0, Math.min(pageIndex, total - 1));
            const targetItemIndex = targetPage * visible;
            const targetItem = items[targetItemIndex];

            if (targetItem) {
                viewport.scrollTo({
                    left: targetItem.offsetLeft - track.offsetLeft,
                    behavior: 'smooth'
                });
            }
        };

        // Render Dots dynamically based on screen width / total pages
        const renderDots = () => {
            if (!dotsWrap) return;
            const total = getTotalPages();
            dotsWrap.innerHTML = '';

            if (total <= 1) {
                dotsWrap.style.display = 'none';
                return;
            }
            dotsWrap.style.display = 'flex';

            const curr = getCurrentPage();
            for (let i = 0; i < total; i++) {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.className = `category-carousel__dot ${i === curr ? 'is-active' : ''}`;
                dot.setAttribute('aria-label', `Sayfa ${i + 1}`);
                dot.setAttribute('data-page', i);
                dot.addEventListener('click', (e) => {
                    e.preventDefault();
                    goToPage(i);
                });
                dotsWrap.appendChild(dot);
            }
        };

        const updateUI = () => {
            const curr = getCurrentPage();
            const total = getTotalPages();

            if (pagePill) {
                pagePill.textContent = `${curr + 1} / ${total}`;
            }

            if (dotsWrap) {
                const dots = dotsWrap.querySelectorAll('.category-carousel__dot');
                dots.forEach((dot, idx) => {
                    dot.classList.toggle('is-active', idx === curr);
                });
            }
        };

        // Click on Next (">")
        nextBtns.forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const curr = getCurrentPage();
                const total = getTotalPages();
                if (curr >= total - 1) {
                    goToPage(0); // loop back to first page
                } else {
                    goToPage(curr + 1);
                }
            });
        });

        // Click on Prev ("<")
        prevBtns.forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const curr = getCurrentPage();
                const total = getTotalPages();
                if (curr <= 0) {
                    goToPage(total - 1); // loop to last page
                } else {
                    goToPage(curr - 1);
                }
            });
        });

        // Update UI when scrolling (touch swipe on mobile or smooth scroll finish)
        let scrollTimer = null;
        viewport.addEventListener('scroll', () => {
            if (scrollTimer) clearTimeout(scrollTimer);
            scrollTimer = setTimeout(updateUI, 60);
        }, { passive: true });

        // Resize handler
        window.addEventListener('resize', () => {
            renderDots();
            updateUI();
        }, { passive: true });

        // Initial setup
        renderDots();
        updateUI();
    });
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

/**
 * Initializes smooth scroll-to-top button at the bottom-right corner.
 * Shows when scrolled down past 280px.
 */
function initScrollTop() {
    const btn = document.querySelector('[data-scroll-top]');
    if (!btn) return;

    let isVisible = false;
    let ticking = false;

    const checkScroll = () => {
        const y = window.scrollY || window.pageYOffset || 0;
        const shouldShow = y > 280;

        if (shouldShow !== isVisible) {
            isVisible = shouldShow;
            btn.classList.toggle('is-visible', isVisible);
        }
        ticking = false;
    };

    window.addEventListener('scroll', () => {
        if (!ticking) {
            requestAnimationFrame(checkScroll);
            ticking = true;
        }
    }, { passive: true });

    btn.addEventListener('click', (e) => {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    checkScroll();
}
