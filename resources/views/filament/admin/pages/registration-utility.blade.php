<x-filament-panels::page>
    <div>
        {{ $this->form }}
    </div>
    <div class="shadow-md bg-white rounded-lg p-6 mt-2">
        @if ($utility_id && $dates && ($remainingTimes > 0 || $this->utility->gioi_han == 0))
            <div class="grid grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach ($blocks as $index => $block)
                    @if ($block['enable'])
                        <div @class([
                            'border text-center text-sm text-white rounded-lg p-3 cursor-pointer',
                            'bg-green-700' => $block['enable'] && !$block['selected'],
                            'bg-sky-700' => $block['enable'] && $block['selected'],
                        ]) wire:click="selectBlock('{{ $index }}')">
                            {{ $block['start']?->format('H:i') }} - {{ $block['end']?->format('H:i') }}
                        </div>
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
            @elseif (!$dates)
                <div class="text-center text-sm col-span-6">
                    Chưa chọn ngày
                </div>
            @else
                <div class="text-center text-sm col-span-6">
                    Hết lượt đăng ký tiện ích {{ $utility->name }} trong tháng
                </div>
            @endif
        @endif
    </div>

    @if ($utility_id && $dates && count($surchargeList) > 0)
        <div class="shadow-md bg-white rounded-lg p-3 mt-2 pl-7 cursor-pointer">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="py-3.5 pr-3 text-left text-sm font-semibold text-gray-900">Chọn</th>
                        <th scope="col" class="py-3.5 pr-3 text-left text-sm font-semibold text-gray-900">Tên phụ thu</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Mức thu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach ($surchargeList as $surcharge)
                        <tr>
                            <td class="relative w-12 px-2">
                                <input type="checkbox" wire:key="{{ $surcharge['id'] }}" wire:model.live="selectedSurcharges" wire:click="chonPhuThuKhongBatBuoc('{{ $surcharge['id'] }}')" value="{{ $surcharge['id'] }}"
                                class = "absolute left-0 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 {{ $surcharge['bat_buoc'] ? 'text-gray-300' : 'text-indigo-600' }}" {{ $surcharge['bat_buoc'] ? 'disabled' : '' }}>
                            </td>
                            <td class="whitespace-nowrap py-4 text-sm font-medium text-gray-900">{{ $surcharge['ten_phu_thu'] }}</td>
                            <td class="whitespace-nowrap py-4 text-sm font-medium text-gray-900">{{ moneyFormat($surcharge['muc_thu']) }}{{ $surcharge['co_dinh'] ? 'đ' : '%' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($utility_id)
        @foreach ($invoiceables as $month => $itemList)
            <div class="shadow-md bg-white rounded-lg p-3 mt-2 pl-7 cursor-pointer">
                <div class="mt-2">
                    <h1 class="text-xl font-semibold text-gray-900">Phiếu thu tháng {{ $month }}</h1>
                </div>
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3.5 pr-3 text-left text-sm font-semibold text-gray-900">Chọn</th>
                            <th scope="col" class="py-3.5 pr-3 text-left text-sm font-semibold text-gray-900">Ngày</th>
                            <th scope="col" class="py-3.5 pr-3 text-left text-sm font-semibold text-gray-900">Mô tả</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Số lượng</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Mức thu</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Thành tiền</th>
                        </tr>
                    </thead>
                    @foreach ($itemList->sortBy('mo_ta')->sortByDesc('loai')->sortBy('ngay') as $itemKey => $item)
                        <tbody class="divide-y divide-gray-200 bg-white {{ $item['registered'] ? 'text-red-500' : ' text-gray-900' }}">
                            <tr>
                                <td class="relative w-12 px-2">
                                    <input type="checkbox" wire:key="{{ $itemKey }}" wire:model.live="selectedItems.{{ $month }}" wire:click="xuLyDangKyTienIch('{{ $month }}', '{{ $itemKey }}')" value="{{ $itemKey }}" class="absolute left-0 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 {{ $item['bat_buoc'] ? 'text-gray-300' : 'text-indigo-600' }}" {{ $item['bat_buoc'] || $item['disabled'] ? 'disabled' : '' }}>
                                </td>
                                <td class="whitespace-nowrap py-4 text-sm font-medium">{{ $item['ngay']->format('d/m/Y') }}</td>
                                <td class="whitespace-nowrap py-4 text-sm font-medium">{{ $item['mo_ta'] }}</td>
                                <td class="whitespace-nowrap py-4 text-sm font-medium">{{ $item['so_luong'] }}</td>
                                <td class="whitespace-nowrap py-4 text-sm font-medium">{{ moneyFormat($item['muc_thu']) }}{{ $item['co_dinh'] ? 'đ' : '%' }}</td>
                                <td class="whitespace-nowrap py-4 text-sm font-medium">{{ moneyFormat($item['thanh_tien']) }}đ</td>
                            </tr>
                        </tbody>
                    @endforeach
                    <tfoot>
                        <tr>
                            <td colspan="4"></td>
                            <td scope="row" class="font-normal text-gray-500">Phí đăng ký</td>
                            <td class="py-2 text-gray-500">{{ moneyFormat($invoices[$month]['phi_dang_ky']) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4"></td>
                            <td scope="row" class="font-normal text-gray-500">Phụ thu</td>
                            <td class="py-2 text-gray-500">{{ moneyFormat($invoices[$month]['phi_phu_thu']) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4"></td>
                            <td scope="row" class="font-normal text-gray-500">Tổng tiền</td>
                            <td class="py-2 text-gray-500">{{ moneyFormat($invoices[$month]['tong_tien']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endforeach

        <div class="shadow-md bg-white rounded-lg p-3 mt-2 pl-7 cursor-pointer">
            <div class="grid grid-cols-2 gap-4">
                <div class="text-left">
                    @if ($this->utility->gioi_han > 0)
                        Số lần đăng ký còn lại trong tháng <span class='text-sky-700 text-lg'>{{ $remainingTimes }}</span>
                        <br>
                    @endif
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
