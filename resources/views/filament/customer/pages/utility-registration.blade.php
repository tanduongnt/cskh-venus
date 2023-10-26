<x-filament-panels::page>
    <form wire:submit="store" wire:submit.prevent="$refresh">
        <div>
            {{ $this->form }}
        </div>
        @if ($utility_id)
            <div class="shadow-md bg-white rounded-lg p-3 mt-2 pl-7 text-sm cursor-pointer">
                    Tổng tiền : {{ $totalPriceBlocks }} VNĐ
            </div>
        @endif
        <div class="shadow-md bg-white rounded-lg p-6 mt-2">
            <div class="grid grid-cols-4 lg:grid-cols-6 gap-4">
                @forelse ($blocks as $index => $block)
                    @if ($block['enable'])
                        @if ($block['registered'])
                            <div class="text-center text-sm text-white rounded-lg p-3 bg-red-700">
                                {{ $block['start']?->format('H:i') }} - {{ $block['end']?->format('H:i') }}
                            </div>
                        @else
                            <div @class([
                                'text-center text-sm text-white rounded-lg p-3 cursor-pointer',
                                'bg-green-700' => $block['enable'] && !$block['selected'],
                                'bg-sky-700' => $block['enable'] && $block['selected'],
                            ]) wire:click="selectBlock('{{ $index }}')">
                                {{ $block['start']?->format('H:i') }} - {{ $block['end']?->format('H:i') }}
                            </div>
                        @endif
                    @else
                        <div class="text-center text-sm text-white rounded-lg p-3 bg-gray-500">
                            {{ $block['start']?->format('H:i') }} - {{ $block['end']?->format('H:i') }}
                        </div>
                    @endif
                @empty
                    <div class="rounded-lg p-3 mt-2 text-center text-sm cursor-pointer">
                        Chưa chọn tiện ích
                    </div>
                @endforelse
            </div>
        </div>
        <div>
            <x-filament::button class="mt-2" type="submit">
                Hoàn tất
            </x-filament::button>
        </div>
    </form>


</x-filament-panels::page>
