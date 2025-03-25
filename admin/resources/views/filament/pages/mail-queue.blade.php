<x-filament-panels::page>
    <x-filament-panels::form wire:submit="listMailQueue">
        {{ $this->form }}
        <x-filament-panels::form.actions
            :actions="$this->getFormActions()"
        />
    </x-filament-panels::form>
    <x-filament::table>
        {{ $this->table }}
    </x-filament::table>
</x-filament-panels::page>
