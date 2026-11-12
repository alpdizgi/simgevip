<x-filament::page>
    <form wire:submit.prevent="submit" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center justify-start pt-2">
            <x-filament::button type="submit" size="md">
                Değişiklikleri Kaydet
            </x-filament::button>
        </div>
    </form>
</x-filament::page>
