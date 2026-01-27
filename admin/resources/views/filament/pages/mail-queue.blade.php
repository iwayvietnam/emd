<x-filament-panels::page>
    <x-filament-panels::form wire:submit="listMailQueue">
        {{ $this->form }}
        <x-filament-panels::components.actions
            :actions="$this->getFormActions()"
        />
    </x-filament-panels::form>
    <div class="flex flex-col gap-y-6">
        {{ $this->table }}
    </div>
</x-filament-panels::page>
