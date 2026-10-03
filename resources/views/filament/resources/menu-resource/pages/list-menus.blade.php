<x-filament::page>
    <div class="menu-manager" id="menu-manager-root">
        <div class="menu-manager__card">
            <div class="menu-manager__card-head">
                <h3>Navbar Menüleri</h3>
                <span class="menu-manager__count">{{ $roots->count() }} ana menü</span>
            </div>

            <div data-sortable-roots>
                @forelse ($roots as $root)
                    <div class="menu-manager__item is-root" data-root-item data-id="{{ $root->id }}">
                        <div class="menu-manager__row">
                            <button type="button" class="menu-manager__drag" data-drag-handle title="Sürükle" aria-label="Sürükle">
                                <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M7 4a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm0 6a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm0 6a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm9-12a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm0 6a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm0 6a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/></svg>
                            </button>

                            <div class="menu-manager__main">
                                <div class="menu-manager__title-line">
                                    <span class="menu-manager__pill">Ana</span>
                                    <strong>{{ $root->name }}</strong>
                                    @if ($root->show_in_nav)
                                        <span class="menu-manager__pill is-live">Navbar’da</span>
                                    @else
                                        <span class="menu-manager__pill">Gizli</span>
                                    @endif
                                </div>
                                <div class="menu-manager__meta">
                                    Sıra {{ $root->sort_order }}
                                    · {{ $root->children_count }} alt menü
                                    · {{ $root->products_count }} ürün
                                </div>
                            </div>

                            <div class="menu-manager__actions">
                                <button type="button" class="menu-manager__icon" wire:click="toggleCategoryNav({{ $root->id }})" title="{{ $root->show_in_nav ? 'Navbar’dan gizle' : 'Navbar’da göster' }}">
                                    @if ($root->show_in_nav)
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    @else
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                    @endif
                                </button>
                                <a href="{{ \App\Filament\Resources\CategoryResource::getUrl('edit', ['record' => $root]) }}" class="menu-manager__icon" title="Düzenle">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                                <button type="button" class="menu-manager__icon is-danger" wire:click="deleteCategory({{ $root->id }})" onclick="confirm('{{ addslashes($root->name) }} menüsünü silmek istiyor musunuz?') || event.stopImmediatePropagation()" title="Sil">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </div>

                        @if ($root->children->isNotEmpty())
                            <div class="menu-manager__children" data-sortable-children data-parent-id="{{ $root->id }}">
                                @foreach ($root->children as $child)
                                    <div class="menu-manager__item is-child" data-child-item data-id="{{ $child->id }}">
                                        <div class="menu-manager__row">
                                            <button type="button" class="menu-manager__drag" data-drag-handle title="Sürükle" aria-label="Sürükle">
                                                <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M7 4a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm0 6a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm0 6a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm9-12a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm0 6a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm0 6a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/></svg>
                                            </button>

                                            <div class="menu-manager__main">
                                                <div class="menu-manager__title-line">
                                                    <span class="menu-manager__pill">Alt</span>
                                                    <strong>{{ $child->name }}</strong>
                                                    @if ($child->show_in_nav)
                                                        <span class="menu-manager__pill is-live">Yayında</span>
                                                    @else
                                                        <span class="menu-manager__pill">Gizli</span>
                                                    @endif
                                                </div>
                                                <div class="menu-manager__meta">
                                                    {{ $root->name }} altında · sıra {{ $child->sort_order }} · {{ $child->products_count }} ürün
                                                </div>
                                            </div>

                                            <div class="menu-manager__actions">
                                                <button type="button" class="menu-manager__icon" wire:click="toggleCategoryNav({{ $child->id }})" title="{{ $child->show_in_nav ? 'Navbar’dan gizle' : 'Navbar’da göster' }}">
                                                    @if ($child->show_in_nav)
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                                    @else
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                                    @endif
                                                </button>
                                                <a href="{{ \App\Filament\Resources\CategoryResource::getUrl('edit', ['record' => $child]) }}" class="menu-manager__icon" title="Düzenle">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                </a>
                                                <button type="button" class="menu-manager__icon is-danger" wire:click="deleteCategory({{ $child->id }})" onclick="confirm('{{ addslashes($child->name) }} menüsünü silmek istiyor musunuz?') || event.stopImmediatePropagation()" title="Sil">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="menu-manager__empty">Henüz menü yok. “Yeni menü / kategori” ile ekleyin.</div>
                @endforelse
            </div>
        </div>

        <div class="menu-manager__card">
            <div class="menu-manager__card-head">
                <h3>Ekstra Sabit Linkler</h3>
                <span class="menu-manager__count">{{ $extraMenus->count() }} link</span>
            </div>
            <p class="menu-manager__hint">Kategori olmayan ek navbar linkleri. Zorunlu değil.</p>

            <div data-sortable-extras>
                @forelse ($extraMenus as $menu)
                    <div class="menu-manager__item is-root" data-extra-item data-id="{{ $menu->id }}">
                        <div class="menu-manager__row">
                            <button type="button" class="menu-manager__drag" data-drag-handle title="Sürükle" aria-label="Sürükle">
                                <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M7 4a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm0 6a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm0 6a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm9-12a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm0 6a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm0 6a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/></svg>
                            </button>

                            <div class="menu-manager__main">
                                <div class="menu-manager__title-line">
                                    <strong>{{ $menu->title }}</strong>
                                    @if ($menu->is_active)
                                        <span class="menu-manager__pill is-live">Yayında</span>
                                    @else
                                        <span class="menu-manager__pill">Pasif</span>
                                    @endif
                                </div>
                                <div class="menu-manager__url">{{ $menu->url ?: '—' }}</div>
                            </div>

                            <div class="menu-manager__actions">
                                <button type="button" class="menu-manager__icon" wire:click="toggleExtraMenu({{ $menu->id }})" title="{{ $menu->is_active ? 'Pasifleştir' : 'Aktifleştir' }}">
                                    @if ($menu->is_active)
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    @else
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                    @endif
                                </button>
                                <a href="{{ \App\Filament\Resources\MenuResource::getUrl('edit', ['record' => $menu]) }}" class="menu-manager__icon" title="Düzenle">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                                <button type="button" class="menu-manager__icon is-danger" wire:click="deleteExtraMenu({{ $menu->id }})" onclick="confirm('Bu linki silmek istiyor musunuz?') || event.stopImmediatePropagation()" title="Sil">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="menu-manager__empty">Ekstra sabit link yok.</div>
                @endforelse
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
        (function () {
            function collectIds(container, itemSelector) {
                return Array.prototype.slice.call(container.querySelectorAll(itemSelector))
                    .filter(function (el) { return el.parentElement === container; })
                    .map(function (el) { return el.getAttribute('data-id'); });
            }

            function getWire() {
                var root = document.getElementById('menu-manager-root');
                if (!root || !window.Livewire) return null;
                var wireEl = root.closest('[wire\\:id]');
                if (!wireEl) return null;
                return Livewire.find(wireEl.getAttribute('wire:id'));
            }

            function bootSortables() {
                if (!window.Sortable) return;

                var roots = document.querySelector('[data-sortable-roots]');
                if (roots && !roots._svSortable) {
                    roots._svSortable = Sortable.create(roots, {
                        animation: 150,
                        handle: '[data-drag-handle]',
                        draggable: '[data-root-item]',
                        ghostClass: 'sortable-ghost',
                        onEnd: function () {
                            var component = getWire();
                            if (component) component.call('reorderRoots', collectIds(roots, '[data-root-item]'));
                        }
                    });
                }

                Array.prototype.slice.call(document.querySelectorAll('[data-sortable-children]')).forEach(function (children) {
                    if (children._svSortable) return;
                    var parentId = parseInt(children.getAttribute('data-parent-id'), 10);
                    children._svSortable = Sortable.create(children, {
                        animation: 150,
                        handle: '[data-drag-handle]',
                        draggable: '[data-child-item]',
                        ghostClass: 'sortable-ghost',
                        onEnd: function () {
                            var component = getWire();
                            if (component) component.call('reorderChildren', parentId, collectIds(children, '[data-child-item]'));
                        }
                    });
                });

                var extras = document.querySelector('[data-sortable-extras]');
                if (extras && !extras._svSortable) {
                    extras._svSortable = Sortable.create(extras, {
                        animation: 150,
                        handle: '[data-drag-handle]',
                        draggable: '[data-extra-item]',
                        ghostClass: 'sortable-ghost',
                        onEnd: function () {
                            var component = getWire();
                            if (component) component.call('reorderExtraMenus', collectIds(extras, '[data-extra-item]'));
                        }
                    });
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bootSortables);
            } else {
                bootSortables();
            }

            document.addEventListener('livewire:load', bootSortables);
            document.addEventListener('livewire:update', bootSortables);
        })();
    </script>
</x-filament::page>
