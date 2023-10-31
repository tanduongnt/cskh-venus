<x-filament-panels::page>
    <form wire:submit="store" wire:submit.prevent="$refresh">
        <div>
            {{ $this->form }}
        </div>
        @if ($utility_id)
            <div class="shadow-md bg-white rounded-lg p-3 mt-2 pl-7 cursor-pointer">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Tên phụ thu</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Mức thu</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($utility->surcharges as $surcharge)
                            <tr>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $surcharge->name }}</td>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $surcharge->price }}{{ $surcharge->fixed ? 'đ' : '%' }}</td>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $surcharge->fixed ? $surcharge->price : $priceNotFixed }}đ</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </table>
            </div>

            <div class="shadow-md bg-white rounded-lg p-3 mt-2 pl-7 cursor-pointer">
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-left text-sm">
                        Phí đăng ký: {{ $totalPriceBlocks }}đ.
                        <br>
                        Phí phụ thu: {{ $totalPriceSurcharge }}đ.
                        <br>
                        Tổng tiền: {{ $totalAmount }}đ.
                    </div>
                    <div class="text-right text-sm">
                        Số lần đăng ký dịch vụ còn lại trong tháng : {{ $remainingTimes }}.
                    </div>
                </div>
            </div>
        @endif
        <div class="shadow-md bg-white rounded-lg p-6 mt-2">
            @if ($utility_id)
                @if ($remainingTimes > 0)
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
