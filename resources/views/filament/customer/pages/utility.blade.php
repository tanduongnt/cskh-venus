<x-filament-panels::page>
    <form wire:submit="create" wire:submit.prevent="$refresh">
        {{ $this->form }}

        <div class="shadow-md bg-white rounded-lg p-6">
            <div class="grid grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach ($blocks as $block)
                    <div @class([
                        'text-center text-sm text-white rounded-lg p-3',
                        'bg-green-700' => $block['enable'],
                        'bg-gray-500' => !$block['enable'],
                    ]) wire:click="selectBlock('{{ $block['start']?->format('H:i') }}', '{{ $block['end']?->format('H:i') }}')">
                        {{ $block['start']?->format('H:i') }} - {{ $block['end']?->format('H:i') }}
                        <br>
                        Giá: {{ $block['price'] }}
                    </div>
                @endforeach
            </div>
        </div>

        <button type="submit">
            Submit
        </button>
    </form>


</x-filament-panels::page>
