<x-filament-panels::page>
    <div>
        {{ $this->form }}
    </div>
    <div class="shadow-md bg-white rounded-lg p-6 mt-2">
        @if ($utility_id && ($remainingTimes > 0 || $this->utility->gioi_han == 0))
            <div class="grid grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach ($blocks as $index => $block)
                    @if ($block['enable'])
                        {{--  @if ($block['registered'])
                            <div class="text-center text-sm text-white rounded-lg p-3 bg-red-700">
                                {{ $block['start']?->format('H:i') }} - {{ $block['end']?->format('H:i') }}
                            </div>
                        @else  --}}
                        <div @class([
                            'border text-center text-sm text-white rounded-lg p-3 cursor-pointer',
                            'bg-green-700' => $block['enable'] && !$block['selected'],
                            'bg-sky-700' => $block['enable'] && $block['selected'],
                        ]) wire:click="selectBlock('{{ $index }}')">
                            {{ $block['start']?->format('H:i') }} - {{ $block['end']?->format('H:i') }}
                        </div>
                        {{--  @endif  --}}
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

    @if ($utility_id && $surchargeList && $surchargeList->count() > 0)
        <div class="shadow-md bg-white rounded-lg p-3 mt-2 pl-7 cursor-pointer">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Chọn</th>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Tên phụ thu</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Mức thu</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Số lượng</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Thành tiền</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach ($surchargeList->sortByDesc('default') as $surcharge)
                        <tr>
                            <td class="relative w-12 px-6 sm:w-16 sm:px-8">
                                <input type="checkbox" wire:key="{{ $surcharge->id }}" wire:model.live="selectedSurcharges" value="{{ $surcharge->id }}" @class([
                                    'absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 sm:left-6',
                                    'text-indigo-600' => !$surcharge->mac_dinh,
                                    'text-gray-300' => $surcharge->mac_dinh,
                                ]) {{ $surcharge->mac_dinh ? 'disabled' : '' }}>
                            </td>
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $surcharge->ten_phu_thu }}</td>
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ number_format($surcharge->muc_thu) }}{{ $surcharge->co_dinh ? 'đ' : '%' }}</td>
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $surcharge->so_luong }}</td>
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ number_format($surcharge->tong_tien) }}đ</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($utility_id)
        @foreach ($invoices as $month => $invoice)
            <div class="shadow-md bg-white rounded-lg p-3 mt-2 pl-7 cursor-pointer">

                <div class="sm:flex-auto mt-2">
                    <h1 class="text-xl font-semibold text-gray-900">Phiếu thu tháng {{ $month }}</h1>
                </div>
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Chọn</th>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Ngày</th>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Mô tả</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Số lượng</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Múc thu</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($invoiceables->sortBy('thoi_gian') as $key => $invoiceable)
                            @if ($invoiceable['thoi_gian']->month == $month)
                                <tr>
                                    @if ($invoiceable['bat_buoc'])
                                        <td class="relative w-12 px-6 sm:w-16 sm:px-8">
                                            <input type="checkbox" wire:key="{{ $key }}" wire:model="registrationUtilityItem" value="{{ $key }}" class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 sm:left-6 text-gray-300" disabled>
                                        </td>
                                    @else
                                        <td class="relative w-12 px-6 sm:w-16 sm:px-8">
                                            <input type="checkbox" wire:key="{{ $key }}" wire:model.live="registrationUtilityItem" value="{{ $key }}" class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 sm:left-6 text-indigo-600" checked>
                                        </td>
                                    @endif
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $invoiceable['thoi_gian']->format('d/m/Y') }}</td>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $invoiceable['mo_ta'] }}</td>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $invoiceable['so_luong'] }}</td>
                                    @if (in_array($key, $registrationUtilityItem))
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $invoiceable['muc_thu'] }}{{ $invoiceable['co_dinh'] ? 'đ' : '%' }}</td>
                                    @else
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">0</td>
                                    @endif
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{in_array($key, $registrationUtilityItem) ?  $invoiceable['thanh_tien'].'đ' : 0}}</td>

                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6"> Tổng tiền </td>
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6"> </td>
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6"> </td>
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6"> </td>
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6"> </td>
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6"> </td>
                        </tr>
                    </tfoot>
                </table>

            </div>
        @endforeach

        @foreach ($registrationUtilityItem as $item)
            {{ $item }}
        @endforeach


        <div class="shadow-md bg-white rounded-lg p-3 mt-2 pl-7 cursor-pointer">
            <div class="grid grid-cols-2 gap-4">
                <div class="text-left">
                    @if ($this->utility->gioi_han > 0)
                        Số lần đăng ký còn lại trong tháng <span class='text-sky-700 text-lg'>{{ $remainingTimes }}</span>
                        <br>
                    @endif
                    Phí đăng ký: <span class="text-sky-700">{{ number_format($totalBlockAmountByMonth) }}đ</span>
                    <br>
                    Phí phụ thu: <span class="text-sky-700">{{ number_format($totalSurchargeAmountByMonth) }}đ</span>
                    <br>
                    Tổng tiền: <span class="text-sky-700">{{ number_format($totalBlockAmountByMonth + $totalSurchargeAmountByMonth) }}đ</span>
                </div>
            </div>
        </div>

        <div>
            <x-filament::button class="mt-2" type="button" wire:click="store">
                Hoàn tất
            </x-filament::button>
        </div>
    @endif

</x-filament-panels::page>
