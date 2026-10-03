<div class="flex items-center space-x-3">
    @if ($getRecord()->parent_id)
        <div class="flex-shrink-0 w-6 h-6 border-l-2 border-b-2 border-gray-300 rounded-bl-md" aria-hidden="true" style="margin-top: -12px;"></div>
        <div class="flex flex-col">
            <div class="font-medium text-gray-900">{{ $getRecord()->name }}</div>
            <div class="text-xs text-gray-500">
                Alt kategori &middot; {{ $getRecord()->parent?->name ?? '—' }}
            </div>
        </div>
    @else
        <div class="flex flex-col">
            <div class="font-bold text-gray-900">{{ $getRecord()->name }}</div>
            <div class="text-xs text-gray-500">
                @if (($getRecord()->children_count ?? $getRecord()->children->count()) > 0)
                    Ana kategori &middot; {{ $getRecord()->children_count ?? $getRecord()->children->count() }} alt menü
                @else
                    Ana kategori &middot; alt menü yok
                @endif
            </div>
        </div>
    @endif
</div>
