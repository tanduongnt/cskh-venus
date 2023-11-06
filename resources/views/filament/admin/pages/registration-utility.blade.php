<x-filament-panels::page>
    <form wire:submit="create">
        {{ $this->form }}

        <div>
            <x-filament::button class="mt-2" type="submit">
                Hoàn tất
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
