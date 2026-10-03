@props([
    'breadcrumbs' => [],
])

<header
    {{
        $attributes->class([
            'filament-main-topbar sticky top-0 z-30 flex h-20 w-full shrink-0 items-center border-b border-gray-200/50 bg-white/70 backdrop-blur-xl shadow-[0_4px_20px_-10px_rgba(0,0,0,0.05)] transition-all',
            'dark:border-gray-800/60 dark:bg-gray-900/70 dark:shadow-[0_4px_20px_-10px_rgba(0,0,0,0.4)]' => config('filament.dark_mode'),
        ])
    }}
>
    <div class="flex w-full items-center px-4 sm:px-6 md:px-8">
        <button
            x-cloak
            x-data="{}"
            x-bind:aria-label="
                $store.sidebar.isOpen
                    ? '{{ __('filament::layout.buttons.sidebar.collapse.label') }}'
                    : '{{ __('filament::layout.buttons.sidebar.expand.label') }}'
            "
            x-on:click="$store.sidebar.isOpen ? $store.sidebar.close() : $store.sidebar.open()"
            @class([
                'filament-sidebar-open-button flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-gray-600 outline-none hover:bg-gray-100 hover:text-primary-600 focus:bg-primary-50 focus:text-primary-600 dark:text-gray-400 dark:hover:bg-gray-800 dark:focus:bg-gray-800 transition-all active:scale-95',
                'lg:mr-5 rtl:lg:ml-5 rtl:lg:mr-0' => config('filament.layout.sidebar.is_collapsible_on_desktop'),
                'lg:hidden' => ! (config('filament.layout.sidebar.is_collapsible_on_desktop') && (config('filament.layout.sidebar.collapsed_width') === 0)),
            ])
        >
            <svg
                class="h-6 w-6"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
                />
            </svg>
        </button>

        <div class="flex flex-1 items-center justify-between gap-4">
            <div class="flex-1">
                <x-filament::layouts.app.topbar.breadcrumbs
                    :breadcrumbs="$breadcrumbs"
                />
            </div>

            <div class="flex items-center gap-3 sm:gap-5">
                <a href="{{ url('/') }}" target="_blank" class="sv-topbar-site-link hidden sm:inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold text-primary-600 bg-primary-50 hover:bg-primary-100 hover:-translate-y-0.5 shadow-sm hover:shadow dark:text-primary-400 dark:bg-primary-500/10 dark:hover:bg-primary-500/20 active:scale-95 active:translate-y-0 transition-all duration-200" title="Siteyi Canlı Görüntüle">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                    <span>Siteyi Gör</span>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="ml-1 opacity-70"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                </a>

                @livewire('filament.core.global-search')

                @livewire('filament.core.notifications')

                @if(app()->environment('local'))
                    @php($deployEnabled = (bool) config('deploy.enabled'))
                    <form method="POST" action="{{ route('admin.github.deploy') }}" onsubmit="@if($deployEnabled) if(!confirm('Yalnızca canlı için gerekli değişmiş dosyalar (kod, stil, görseller ve katalog içeriği) GitHub üzerinden canlıya yazılacak. Müşteri, sipariş ve destek kayıtları canlıda kalır. Devam edilsin mi?')) return false; var button=this.querySelector('button'); button.disabled=true; button.querySelector('span').textContent='Gönderiliyor...'; @else if(!confirm('Aktarım henüz kapalı. Yalnızca canlı paket listesindeki değişmiş dosyalar önizlenecek, GitHub\'a gönderilmeyecek. Devam edilsin mi?')) return false; var button=this.querySelector('button'); button.disabled=true; button.querySelector('span').textContent='Önizleniyor...'; @endif">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-bold {{ $deployEnabled ? 'text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' : 'text-amber-800 bg-amber-50 hover:bg-amber-100 dark:bg-amber-500/10 dark:text-amber-300 dark:hover:bg-amber-500/20' }} hover:-translate-y-0.5 shadow-sm hover:shadow active:scale-95 transition-all duration-200 disabled:opacity-60" title="{{ $deployEnabled ? 'Yalnızca canlıya zorunlu değişmiş dosyaları GitHub üzerinden aktar' : 'Altyapı hazır. DEPLOY_ENABLED=true olunca gerçek gönderim açılır; şimdilik önizleme yapar' }}">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                            <span>{{ $deployEnabled ? "GitHub'a Gönder" : 'GitHub Önizleme' }}</span>
                        </button>
                    </form>
                @endif

                <x-filament::layouts.app.topbar.user-menu />
            </div>
        </div>
    </div>
</header>
