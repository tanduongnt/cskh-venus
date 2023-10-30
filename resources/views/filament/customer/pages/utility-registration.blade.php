<x-filament-panels::page>
    <form wire:submit="store" wire:submit.prevent="$refresh">
        <div>
            {{ $this->form }}
        </div>
        @if ($utility_id)
            <div class="shadow-md bg-white rounded-lg p-3 mt-2 pl-7 cursor-pointer">
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-left text-sm">
                        {{ $utility->chargeable ? 'Tổng tiền' : 'Tiền phụ thu' }} : {{ $totalPriceBlocks }} VNĐ.
                    </div>
                    <div class="text-right text-sm">
                        Số lần đăng ký dịch vụ còn lại trong tháng : {{ $quantity }}.
                    </div>
                </div>
            </div>
        @endif
        <div class="shadow-md bg-white rounded-lg p-6 mt-2">
            @if ($utility_id)
                @if ($quantity > 0)
                    <div class="grid grid-cols-4 lg:grid-cols-6 gap-4">
                        @foreach ($blocks as $index => $block)
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
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-sm col-span-6">
                        Hết lượt đăng ký tiện ích {{ $utility->name }} trong tháng
                    </div>
                @endif
            @else
                <div class="text-center text-sm col-span-6">
                    Chưa chọn tiện ích
                </div>
            @endif
        </div>
        <div>
            <x-filament::button class="mt-2" type="submit">
                Hoàn tất
            </x-filament::button>
        </div>
    </form>


</x-filament-panels::page>
