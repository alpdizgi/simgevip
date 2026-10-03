<x-filament::page>
    <x-filament::form wire:submit.prevent="create">
        {{ $this->form }}

        <x-filament::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()" />
    </x-filament::form>

    <div class="mt-8 space-y-4">
        <div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Mevcut kategoriler / menü ağacı</h3>
            <p class="text-sm text-gray-500">Üst satırlar ana menü, girintili satırlar alt kategoridir.</p>
        </div>

        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 shadow-sm">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Görsel</th>
                        <th class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Seviye</th>
                        <th class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Menü yapısı</th>
                        <th class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Sıra</th>
                        <th class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Navbar</th>
                        <th class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Alt</th>
                        <th class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Ürün</th>
                        <th class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300 text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($categories as $category)
                    @php
                    $isChild = filled($category->parent_id);
                    @endphp
                    <tr class="{{ $isChild ? 'bg-gray-50/50 dark:bg-gray-800/50' : 'bg-white dark:bg-gray-800' }}">
                        <td class="px-4 py-3">
                            @if ($category->image_path)
                                <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="w-10 h-14 object-cover rounded shadow-sm">
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $isChild ? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' : 'bg-primary-100 text-primary-700 dark:bg-primary-500/20 dark:text-primary-400' }}">
                                {{ $isChild ? 'Alt' : 'Ana' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center space-x-3 {{ $isChild ? 'pl-4' : '' }}">
                                @if ($isChild)
                                    <div class="flex-shrink-0 w-4 h-4 border-l-2 border-b-2 border-gray-300 rounded-bl-sm" aria-hidden="true" style="margin-top: -12px;"></div>
                                    <div class="flex flex-col">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $category->name }}</div>
                                        <div class="text-xs text-gray-500">Alt kategori · {{ $category->parent?->name ?? '—' }}</div>
                                    </div>
                                @else
                                    <div class="flex flex-col">
                                        <div class="font-bold text-gray-900 dark:text-gray-100">{{ $category->name }}</div>
                                        <div class="text-xs text-gray-500">
                                            @if ($category->children_count > 0)
                                            Ana kategori · {{ $category->children_count }} alt menü
                                            @else
                                            Ana kategori · alt menü yok
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $category->sort_order }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $category->show_in_nav ? 'Evet' : 'Hayır' }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $isChild ? '—' : $category->children_count }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $category->products_count }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap space-x-3">
                            <a href="{{ \App\Filament\Resources\CategoryResource::getUrl('edit', ['record' => $category]) }}" class="text-primary-600 hover:underline font-medium">Düzenle</a>
                            <button type="button" wire:click="deleteCategory({{ $category->id }})" onclick="confirm('{{ addslashes($category->name) }} kategorisini silmek istediğinize emin misiniz?') || event.stopImmediatePropagation()" class="text-danger-600 hover:underline font-medium">Sil</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td class="px-4 py-6 text-center text-gray-500" colspan="8">Henüz kategori eklenmemiş.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament::page>
