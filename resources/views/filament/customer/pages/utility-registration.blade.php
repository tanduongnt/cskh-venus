<x-filament-panels::page>
    <form wire:submit="store" wire:submit.prevent="$refresh">
        <div>
            {{ $this->form }}
        </div>
        <div class="shadow-md bg-white rounded-lg p-3 mt-2 text-center text-sm cursor-pointer">
            Tổng tiền : {{$totalPriceBlocks}} VNĐ {{ Carbon\Carbon::parse($registration_date)?->format('d/m/Y') }}
            <br>
            @foreach ($invoiceables as $invoiceable)

                {{ $invoiceables}}
            @endforeach
        </div>
        <div class="shadow-md bg-white rounded-lg p-6 mt-2">
            <div class="grid grid-cols-4 lg:grid-cols-6 gap-4">
                @if ($utility_id)
                    @foreach ($blocks as $index => $block)
                        @if ($block['enable'])
                            <div @class([
                                'text-center text-sm text-white rounded-lg p-3 cursor-pointer',
                                'bg-green-700' => $block['enable'] && !$block['selected'],
                                'bg-sky-700' => $block['enable'] && $block['selected'],
                            ]) wire:click="selectBlock('{{ $index }}')">
                                {{ $block['start']?->format('H:i') }} - {{ $block['end']?->format('H:i') }}
                                <br>
                                {{ $block['registration']}}
                            </div>
                        @else
                            <div class="text-center text-sm text-white rounded-lg p-3 bg-gray-500">
                                {{ $block['start']?->format('H:i') }} - {{ $block['end']?->format('H:i') }}
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
        <div>
            <x-filament::button class="m-2" type="submit">
                Hoàn tất
            </x-filament::button>
        </div>
    </form>


</x-filament-panels::page>
