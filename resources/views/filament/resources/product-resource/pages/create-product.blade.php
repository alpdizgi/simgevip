<x-filament::page class="filament-resources-create-record-page filament-resources-products">
    {{-- Let Livewire validate fields in inactive tabs and reveal their errors. --}}
    <x-filament::form wire:submit.prevent="create" novalidate>
        {{ $this->form }}

        <x-filament::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament::form>
</x-filament::page>
