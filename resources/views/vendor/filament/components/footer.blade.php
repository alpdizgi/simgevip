{{ \Filament\Facades\Filament::renderHook('footer.before') }}

<div class="filament-footer flex w-full items-center justify-between text-xs text-gray-500 dark:text-gray-400">
    {{ \Filament\Facades\Filament::renderHook('footer.start') }}

    <div class="flex items-center gap-1.5 font-normal tracking-wide text-gray-600 dark:text-gray-300">
        <span>&copy; {{ date('Y') }}</span>
        <span class="font-semibold text-gray-900 dark:text-gray-100">SimgeVIP</span>
        <span class="text-gray-300 dark:text-gray-600">·</span>
        <span>Kurumsal Yönetim Paneli</span>
        <span class="hidden sm:inline text-gray-300 dark:text-gray-600">·</span>
        <span class="hidden sm:inline text-gray-400 dark:text-gray-500">Tüm Hakları Saklıdır</span>
    </div>

    <div class="flex items-center gap-2 font-mono">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-600 border border-gray-200/80 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            <span>{{ config('app.version', 'v1.0.0') }}</span>
        </span>
    </div>

    {{ \Filament\Facades\Filament::renderHook('footer.end') }}
</div>

{{ \Filament\Facades\Filament::renderHook('footer.after') }}

