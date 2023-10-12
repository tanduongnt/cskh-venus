<x-filament-panels::page>
    <form wire:submit="create">
        {{ $this->form }}
    </form>

    <div class="grid grid-cols-4 gap-4">
        @foreach ($blocks as $block)
            <div @class([
                'text-center rounded-lg p-6',
                'bg-green-500' => ($start_time < $block['start_time']),
                'bg-gray-500' => !($start_time < $block['start_time']),
            ])>
                {{ $block['start_time'] }} - {{ $block['end_time'] }}
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
