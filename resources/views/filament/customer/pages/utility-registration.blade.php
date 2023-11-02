<x-filament-panels::page>
    <form wire:submit="store">
        <div>
            {{ $this->form }}
        </div>
        <div class="shadow-md bg-white rounded-lg p-6 mt-2">
            @if ($utility_id && ($remainingTimes > 0 || $this->utility->max_times == 0))
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
                @if (!$utility_id)
                    <div class="text-center text-sm col-span-6">
                        Chưa chọn tiện ích
                    </div>
                @else
                    <div class="text-center text-sm col-span-6">
                        Hết lượt đăng ký tiện ích {{ $utility->name }} trong tháng
                    </div>
                @endif
            @endif
        </div>

        @if ($surchargeList && $surchargeList->count() > 0)
            <div class="shadow-md bg-white rounded-lg p-3 mt-2 pl-7 cursor-pointer">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Chọn</th>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Tên phụ thu</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Mức thu</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($surchargeList->sortByDesc('default') as $surcharge)
                            <tr>
                                <td class="relative w-12 px-6 sm:w-16 sm:px-8">
                                    <input type="checkbox" wire:model.live="selectedSurcharges" value="{{ $surcharge->id }}"
                                    @class([
                                        'absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 sm:left-6',
                                        'text-indigo-600' => !$surcharge->default,
                                        'text-gray-300' => $surcharge->default,
                                    ]) {{ $surcharge->default ? 'disabled' : '' }}>
                                </td>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $surcharge->name }}</td>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ number_format($surcharge->price) }}{{ $surcharge->fixed ? 'đ' : '%' }}</td>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ number_format($surcharge->amount) }}đ</td>
                            </tr>
                        @endforeach
                        @foreach ($selectedSurcharges as $selectedSurcharge)
                            {{$selectedSurcharge}} <br>
                        @endforeach
                    </tbody>
                </table>
            </div>

        @endif

        @if ($utility_id)
            <div class="shadow-md bg-white rounded-lg p-3 mt-2 pl-7 cursor-pointer">
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-left">
                        @if ($this->utility->max_times > 0)
                            Số lần đăng ký còn lại trong tháng <span class='text-sky-700 text-lg'>{{ $remainingTimes }}</span>
                        <br>
                        @endif
                        Phí đăng ký: <span class="text-sky-700">{{ number_format($totalBlockAmount) }}đ</span>
                        <br>
                        Phí phụ thu: <span class="text-sky-700">{{ number_format($totalSurchargeAmount) }}đ</span>
                        <br>
                        Tổng tiền: <span class="text-sky-700">{{ number_format($totalBlockAmount + $totalSurchargeAmount) }}đ</span>
                    </div>
                </div>
            </div>
        @endif
        <div>
            <x-filament::button class="mt-2" type="submit">
                Hoàn tất
            </x-filament::button>
        </div>
    </form>


</x-filament-panels::page>
