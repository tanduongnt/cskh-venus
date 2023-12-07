<?php

namespace App\Filament\Admin\Pages;

use Carbon\Carbon;
use App\Models\Utility;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Building;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Models\Apartment;
use App\Models\UtilityType;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\Registration;
use Illuminate\Support\Collection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class UtilityRegistrationPage extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.admin.pages.registration-utility';

    protected static ?string $title = 'Đăng ký tiện ích';

    protected static ?string $slug = 'registration-utility';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public int $step = 1;
    public ?string $customer_id;
    public ?string $building_id;
    public ?string $apartment_id;
    public ?string $utility_type_id;
    public ?string $utility_id;
    public ?string $registration_date;
    public float $remainingTimes = 0;

    public $dates;
    public $week;
    public $selectedCustomerId;
    public $selectedSurcharges = [];
    public $surchargeList = [];
    public $selectedItems = [];

    public ?Collection $buildings;
    public ?Collection $apartments;
    public ?Collection $customers;
    public ?Collection $utilities;
    public ?Collection $utility_types;
    public ?Utility $utility;
    public ?Collection $blocks;
    public ?Collection $invoiceables;
    public ?Collection $invoices;

    public function mount()
    {
        $this->blocks = collect();
        $this->invoices = collect();
        $this->invoiceables = collect();
        $this->buildings = Building::all();
        $this->apartments = collect();
        $this->customers = collect();
        $this->utility_types = collect();
        $this->utilities = collect();
        $this->week = ['1', '2', '3', '4', '5', '6', '0'];
        if ($this->buildings->count() > 0) {
            $this->form->fill([]);
        } else {
            abort(403);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('building_id')
                            ->options($this->buildings->pluck('ten_toa_nha', 'id'))
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {
                                $this->apartments = collect();
                                $this->utility_types = collect();
                                if ($get('building_id')) {
                                    $this->apartments = Apartment::where('building_id', $get('building_id'))->get();

                                    $this->utility_types = UtilityType::whereHas('utilities', function ($query) use ($get) {
                                        $query->whereHas('building', function ($query) use ($get) {
                                            $query->where('id', $get('building_id'));
                                        });
                                    })->get();
                                    if ($this->apartments->count() > 0) {
                                        $set('apartment_id', null);
                                    }
                                    if ($this->utility_types->count() > 0) {
                                        $set('utility_type_id', null);
                                        $set('utility_id', null);
                                    }
                                }
                            })
                            ->searchable()
                            ->required()
                            ->searchPrompt('Tìm theo tên chung cư')
                            ->label('Tên chung cư')
                            ->columnSpan(1),

                        Select::make('apartment_id')
                            ->options(fn (Get $get): Collection =>  $this->apartments->where('building_id', $get('building_id'))->pluck('ma_can_ho', 'id'))
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {
                                $this->customers = collect();
                                $this->utility_types = collect();
                                if ($get('building_id')) {
                                    $this->customers = Customer::whereHas('owns', function ($query) use ($get) {
                                        $query->where('apartments.id', $get('apartment_id'));
                                    })->orWhereHas('authorizedPersons', function ($query) use ($get) {
                                        $query->where('apartments.id', $get('apartment_id'));
                                    })->get();
                                    $this->utility_types = UtilityType::whereHas('utilities', function ($query) use ($get) {
                                        $query->whereHas('building', function ($query) use ($get) {
                                            $query->where('id', $get('building_id'));
                                        });
                                    })->get();
                                    if ($this->customers->count() > 0) {
                                        $set('customer_id', null);
                                    }
                                    if ($this->utility_types->count() > 0) {
                                        $set('utility_type_id', null);
                                        $set('utility_id', null);
                                    }
                                }
                            })
                            ->searchable()
                            ->required()
                            ->searchPrompt('Tìm theo tên hoặc mã căn hộ')
                            ->label('Tên căn hộ')
                            ->columnSpan(1),

                        Select::make('customer_id')
                            ->options(fn (Get $get): Collection => $this->customers->pluck('ho_va_ten', 'id'))
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {
                                $this->utility_types = collect();
                                if ($get('building_id')) {
                                    $this->utility_types = UtilityType::whereHas('utilities', function ($query) use ($get) {
                                        $query->whereHas('building', function ($query) use ($get) {
                                            $query->where('id', $get('building_id'));
                                        });
                                    })->get();
                                    if ($this->utility_types->count() > 0) {
                                        $set('utility_type_id', null);
                                        $set('utility_id', null);
                                    }
                                }
                            })
                            ->searchable()
                            ->required()
                            ->searchPrompt('Tìm theo tên chủ hộ hoặc người được ủy quyền')
                            ->label('Người đăng ký')
                            ->columnSpan(1),
                        Select::make('selectedCustomerId')
                            ->searchable()
                            ->multiple()
                            ->getSearchResultsUsing(fn (string $search): array => Customer::where('ho_va_ten', 'like', "%{$search}%")->limit(50)->pluck('ho_va_ten', 'id')->toArray())
                            ->getOptionLabelsUsing(fn (array $values): array => Customer::whereIn('id', $values)->pluck('ho_va_ten', 'id')->toArray())
                            ->label('Thành viên')
                            ->columnSpan(1),
                        Select::make('utility_type_id')
                            ->options(fn (Get $get): Collection => $this->utility_types->pluck('ten_loai_tien_ich', 'id'))
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $this->utilities = collect();
                                if ($get('utility_type_id')) {
                                    $this->utilities = Utility::withWhereHas('building', function ($query) use ($get) {
                                        $query->where('id', $get('building_id'));
                                        $query->whereHas('apartments', function ($query) {
                                        });
                                    })->where('cho_phep_dang_ky', true)->where('active', true)->where('utility_type_id', $get('utility_type_id'))->get();
                                }

                                if ($this->utilities->count() > 0) {
                                    $set('utility_id', null);
                                }
                            })
                            ->searchable()
                            ->required()
                            ->searchPrompt('Tìm theo loại tiện ích')
                            ->label('Loại tiện ích')
                            ->columnSpan(1),

                        Select::make('utility_id')
                            ->options(fn (Get $get): Collection => $this->utilities->where('building_id', $get('building_id'))->where('utility_type_id', $get('utility_type_id'))->pluck('ten_tien_ich', 'id'))
                            ->searchable()
                            ->required()
                            ->searchPrompt('Tìm theo tên tiện ích')
                            ->label('Tiện ích')
                            ->live()
                            ->columnSpan(1),
                        DateRangePicker::make('dates')
                            ->required()
                            ->setAutoApplyOption(true)
                            ->format('d/m/Y')
                            ->hidden(fn (Get $get): bool => !$get('utility_id')),
                        Select::make('week')
                            ->options([
                                '1' => 'Thứ 2',
                                '2' => 'Thứ 3',
                                '3' => 'Thứ 4',
                                '4' => 'Thứ 5',
                                '5' => 'Thứ 6',
                                '6' => 'Thứ 7',
                                '0' => 'Chủ nhật',
                            ])
                            ->required()
                            ->multiple()
                            ->live()
                            ->native(false)
                            ->label('Ngày trong tuần')
                            ->hidden(fn (Get $get): bool => !$get('utility_id')),
                    ])
                    ->label('Đăng ký tiện ích')->columns(2),
            ]);
    }

    public function updatedUtilityId()
    {
        if ($this->utility_id) {
            $this->resetUtility();
        }
    }

    public function updatedDates()
    {
        if ($this->utility_id) {
            $this->resetUtility();
        }
    }

    public function updatedWeek()
    {
        if ($this->utility_id) {
            $this->resetUtility();
        }
    }

    public function resetUtility()
    {
        $this->blocks = collect();
        $this->invoices = collect();
        $this->invoiceables = collect();
        $this->selectedSurcharges = [];
        // array cho wire.model.live để sử dụng checked
        $this->selectedItems = [];
        $this->loadUtility();
        $this->loadRemainingTimes();
        $this->generateUtilityBlocks();
        $this->layDanhSachPhuThuBatBuoc();
    }

    public function loadUtility()
    {
        $this->utility = Utility::with(['surcharges' => function ($query) {
            $query->where('active', true);
        }])->find($this->utility_id);
    }

    public function loadRemainingTimes()
    {
        $this->remainingTimes = 0;
        if ($this->utility->gioi_han > 0) {
            $registrationCount = Registration::where('apartment_id', $this->apartment_id)->whereBetween('thoi_gian_dang_ky', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->pluck('thoi_gian_dang_ky')->unique()->count();
            $this->remainingTimes = $this->utility->gioi_han - $registrationCount;
        }
    }

    public function generateUtilityBlocks()
    {
        if ($this->utility->block) {
            $block_price = $this->utility->don_gia;
            $blockCount = floor(1440 / $this->utility->block);
            $start_time = Carbon::parse($this->utility->gio_bat_dau);
            $end_time = Carbon::parse($this->utility->gio_ket_thuc);
            $charge_start_time = Carbon::parse($this->utility->gio_bat_dau_tinh_tien);
            $charge_end_time = Carbon::parse($this->utility->gio_ket_thuc_tinh_tien);
            for ($i = 0; $i < $blockCount; $i++) {
                $minutes =  $i * $this->utility->block;
                $block_start = Carbon::today()->addMinutes($minutes);
                $block_end = Carbon::today()->addMinutes($minutes + $this->utility->block);
                $enable =  $start_time->lte($block_start) && $end_time->gte($block_end);
                $chargeableBlock = $charge_start_time->lte($block_start) && $charge_end_time->gte($block_end);
                $price = ($enable && $chargeableBlock) ? $block_price : 0;
                $this->blocks->push([
                    'enable'        => $enable,
                    'start'         => $block_start,
                    'end'           => $block_end,
                    'chargeable'    => $chargeableBlock,
                    'price'         => $price,
                    'selected'      => $selected ?? false,
                ]);
            }
        }
    }

    public function layDanhSachPhuThuBatBuoc()
    {
        if ($this->utility?->surcharges) {
            // Lấy danh sách phụ thu bắt buộc
            $surchargeList = $this->utility->surcharges->transform(function ($item, $key) {
                $item['selected'] = $item['bat_buoc'];
                return $item;
            });
            // Lấy danh sách phụ thu được chọn (dùng cho wire.model)
            $this->selectedSurcharges = $surchargeList->filter(function ($item, $key) {
                return $item['selected'];
            })->pluck('id')->toArray();
            // Lấy danh sách phụ thu theo key là id
            $this->surchargeList = Arr::keyBy($surchargeList->toArray(), 'id');
        }
    }

    public function selectBlock($index)
    {
        $block = $this->blocks[$index];
        $block['selected'] = !$block['selected'];
        $this->blocks->put($index, $block);
        $selectedBlocks = $this->blocks->filter(function ($value, $key) {
            return $value['selected'];
        });
        if ($selectedBlocks->count() > 0) {
            if ($selectedBlocks->count() > 1) {
                // Cập nhật phiếu thu
                $this->capNhatPhieuThuNhieuBlock($block, $selectedBlocks->count());
            } else {
                // Khởi tạo phiếu thu
                $this->khoiTaoPhieuThu();
            }
        } else {
            $this->invoiceables = collect();
        }
    }

    public function chonPhuThuKhongBatBuoc($surchargeId)
    {
        $surcharge = Arr::first($this->surchargeList, function ($value, $key) use ($surchargeId) {
            return $key === $surchargeId;
        });
        $surcharge['selected'] = !$surcharge['selected'];
        $this->surchargeList[$surchargeId] = $surcharge;
        $this->xuLyPhuThuKhongBatBuoc($surcharge);
    }

    public function khoiTaoPhieuThu()
    {
        // Lấy danh sách phụ thu bắt buộc
        $surchargeId = Arr::pluck(Arr::where($this->surchargeList, function ($value, $key) {
            return $value['selected'];
        }), 'id');
        $phuThuBatBuoc = $this->utility->surcharges->filter(function ($item, $key) use ($surchargeId) {
            return in_array($item['id'], $surchargeId);
        });
        // Lấy block được chọn
        $block = $this->blocks->firstWhere(function ($value, $key) {
            return $value['selected'];
        });
        if ($this->dates) {
            $dates = preg_split('/\s*-\s*/', trim($this->dates));
            // Ngày bắt đầu và ngày kết thúc đăng ký
            $startDate = Carbon::createFromFormat('d/m/Y', $dates[0])->toDateString();
            $endDate = Carbon::createFromFormat('d/m/Y', $dates[1])->toDateString();
            // Ngày trong từng tháng phiếu thu
            $groupDatesByMonth = groupDatesByMonth($startDate, $endDate);
            foreach ($groupDatesByMonth as $month => $datesOfMonth) {
                $itemList = collect();
                foreach ($datesOfMonth as $date) {
                    if (in_array(Carbon::parse($date)->dayOfWeek, $this->week)) {
                        // Lấy những ngày đã đăng ký
                        $registered = $this->ngayDaDangKy($date, $block['start'], $block['end']) > 0;
                        $key = Str::random();
                        if (!$registered) {
                            // Tạo mảng danh sách items (dùng wire.model cho selected)
                            $this->selectedItems[$month][] = $key;
                        }
                        // Tạo itemList tiện ích
                        $itemList->put($key, [
                            'id' => $this->utility_id,
                            'ngay' => Carbon::parse($date),
                            'mo_ta' =>  "Từ {$block['start']->toTimeString()} đến {$block['end']->toTimeString()}",
                            'bat_dau' => $block['start']->toTimeString(),
                            'ket_thuc' => $block['end']->toTimeString(),
                            'so_luong' => 1,
                            'muc_thu' =>  $this->utility->don_gia,
                            'thanh_tien' => $block['price'],
                            'co_dinh' => true,
                            'thu_theo_block' => true,
                            'bat_buoc' => false,
                            'loai' => 'Utility',
                            'selected' => !$registered,
                            'disabled' => $registered,
                            'registered' => $registered,
                        ]);
                        foreach ($phuThuBatBuoc as $surcharge) {
                            $key = Str::random();
                            if (!$registered) {
                                $this->selectedItems[$month][] = $key;
                            }
                            $price = $surcharge->muc_thu;
                            if (!$surcharge->co_dinh) {
                                $price = $block['price'] * $surcharge->muc_thu / 100;
                            }
                            // Tạo itemList phụ thu
                            $itemList->put($key, [
                                'id' => $surcharge->id,
                                'ngay' => Carbon::parse($date),
                                'mo_ta' => $surcharge->ten_phu_thu,
                                'bat_dau' => '',
                                'ket_thuc' => '',
                                'so_luong' => 1,
                                'muc_thu' => $surcharge->muc_thu,
                                'thanh_tien' => $price,
                                'co_dinh' => $surcharge->co_dinh,
                                'thu_theo_block' => $surcharge->thu_theo_block,
                                'bat_buoc' => $surcharge->bat_buoc,
                                'loai' => 'Surcharge',
                                'selected' => !$registered,
                                'disabled' => $registered,
                                'registered' => $registered,
                            ]);
                        }
                    }
                }
                $this->invoiceables->put($month, $itemList);
            }
        }
        $this->tinhTien();
    }

    public function capNhatPhieuThuNhieuBlock($block, $count)
    {
        foreach ($this->invoiceables as $month => $itemList) {
            $dates = $itemList->pluck('ngay')->unique();
            foreach ($dates as $key => $date) {
                $registered = $this->ngayDaDangKy($date, $block['start'], $block['end']) > 0;
                $key = Str::random();
                if (!$registered) {
                    $this->selectedItems[$month][] = $key;
                }
                if ($block['selected']) {
                    // tạo $itemList theo block đã chọn
                    $itemList->put($key, [
                        'id' => $this->utility_id,
                        'ngay' => Carbon::parse($date),
                        'mo_ta' => "Từ {$block['start']->toTimeString()} đến {$block['end']->toTimeString()}",
                        'bat_dau' => $block['start']->toTimeString(),
                        'ket_thuc' => $block['end']->toTimeString(),
                        'so_luong' => 1,
                        'muc_thu' => $this->utility->don_gia,
                        'thanh_tien' => $block['price'],
                        'co_dinh' => true,
                        'thu_theo_block' => true,
                        'bat_buoc' => false,
                        'loai' => 'Utility',
                        'selected' => !$registered,
                        'disabled' => $registered,
                        'registered' => $registered,
                    ]);
                } else {
                    // Loại bỏ $itemList theo block không chọn nữa
                    $itemList = $itemList->reject(function ($value, $key) use ($block) {
                        return $value['bat_dau'] === $block['start']->toTimeString() && $value['ket_thuc'] === $block['end']->toTimeString();
                    });
                    $this->invoiceables->put($month, $itemList);
                }
                // Chọn itemList theo loại tiện ích nhóm theo ngày
                $itemListTheoLoaiTienIchNhomTheoNgay = $itemList->filter(function ($item, $key) {
                    return $item['loai'] === 'Utility' && $item['selected'];
                })->groupBy(function ($item, $key) {
                    return $item['ngay']->format('d-m-Y');
                });
                // Lấy tổng số lượng và tổng tiền của itemList theo loại tiện ích nhóm theo ngày
                $itemListTheoLoaiTienIchNhomTheoNgay->transform(function ($item) {
                    $item['tong_so_luong'] = $item->sum('so_luong');
                    $item['tong_tien'] = $item->sum('thanh_tien');
                    return $item;
                });
            }
            // Ngày đăng ký
            $ngayDangKy = $this->invoiceables[$month]->filter(function ($item, $key) {
                return $item['loai'] === 'Utility' && $item['selected'];
            })->pluck('ngay')->unique();
            // các itemlist thuộc loại phụ thu theo block được chọn
            $selected = $this->invoiceables[$month]->whereIn('ngay', $ngayDangKy);
            $selected->transform(function ($item, $key) use ($month, $count, $itemListTheoLoaiTienIchNhomTheoNgay, $block) {
                if ($item['loai'] === 'Surcharge') {
                    $this->selectedItems[$month][] = $key;
                    $item['selected'] = true;
                    $item['disabled'] = false;
                    $item['registered'] = false;
                    $quantity = $item['thu_theo_block'] ? $itemListTheoLoaiTienIchNhomTheoNgay[$item['ngay']->format('d-m-Y')]['tong_so_luong'] : 1;
                    $price = $item['muc_thu'];
                    if (!$item['co_dinh']) {
                        $price = $itemListTheoLoaiTienIchNhomTheoNgay[$item['ngay']->format('d-m-Y')]['tong_tien'] * $item['muc_thu'] / 100;
                    }
                    $amount = $quantity * $price;
                    $item['so_luong'] = $quantity;
                    $item['thanh_tien'] = $amount;
                }
                return $item;
            });
            $this->invoiceables[$month] = $this->invoiceables[$month]->merge($selected);
        }
        $this->tinhTien();
    }

    public function xuLyPhuThuKhongBatBuoc($surcharge)
    {
        // lấy danh sách đăng ký theo tháng
        $utility = [];
        foreach ($this->invoiceables as $month => $itemList) {
            $dates = $itemList->pluck('ngay')->unique();
            if ($surcharge['selected']) {
                foreach ($dates as $key => $date) {
                    // Lấy ra itemList  loại tiện ích được chọn theo ngày
                    $utility = $itemList->firstWhere(function ($item, $key) use ($date) {
                        return $item['selected'] && $item['loai'] === 'Utility' && $item['ngay']->isSameDay(Carbon::parse($date));
                    });
                    // lấy ra itemList phụ thu bắt buộc theo ngày
                    $surchargeList = $itemList->firstWhere(function ($item, $key) use ($date) {
                        return $item['loai'] === 'Surcharge' && $item['bat_buoc'] && $item['ngay']->isSameDay(Carbon::parse($date));
                    });
                    // Tổng tiền loại tiện ích được chọn theo ngày
                    $tongTien = $itemList->filter(function ($item, $key) use ($date) {
                        return $item['selected'] && $item['loai'] === 'Utility' && $item['ngay']->isSameDay(Carbon::parse($date));
                    })->sum('thanh_tien');
                    $key = Str::random();
                    if ($utility != null && !$utility['registered']) {
                        $this->selectedItems[$month][] = $key;
                    }
                    $quantity = $surcharge['thu_theo_block'] ? $surchargeList['so_luong'] : 1;
                    $price = $surcharge['muc_thu'];
                    if (!$surcharge['co_dinh']) {
                        $price = $tongTien * $surcharge['muc_thu'] / 100;
                    }
                    $amount = $quantity * $price;
                    $itemList->put($key, [
                        'id' => $surcharge['id'],
                        'ngay' => Carbon::parse($date),
                        'mo_ta' => $surcharge['ten_phu_thu'],
                        'bat_dau' => '',
                        'ket_thuc' => '',
                        'so_luong' => $quantity,
                        'muc_thu' => $surcharge['muc_thu'],
                        'thanh_tien' => $amount,
                        'co_dinh' => $surcharge['co_dinh'],
                        'thu_theo_block' => $surcharge['thu_theo_block'],
                        'bat_buoc' => $surcharge['bat_buoc'],
                        'loai' => 'Surcharge',
                        'selected' => $surchargeList['selected'],
                        'disabled' => $surchargeList['disabled'],
                        'registered' => $surchargeList['registered'],
                    ]);
                }
            } else {
                $itemList = $itemList->reject(function ($value, $key) use ($surcharge) {
                    return $value['id'] === $surcharge['id'];
                });
                $this->invoiceables->put($month, $itemList);
            }
        }
        $this->tinhTien();
    }

    public function xuLyDangKyTienIch($month, $itemKey)
    {
        $item = $this->invoiceables[$month][$itemKey];
        $ngayDangKy = $item['ngay'];
        $trangThai = !$item['selected'];
        // Đếm số lượng phiếu thu tiện ích được chọn
        $count = $this->invoiceables[$month]->filter(function ($value, $key) use ($ngayDangKy) {
            return $value['selected'] === true && !$value['registered'] && $value['ngay']->isSameDay($ngayDangKy) && $value['loai'] === 'Utility';
        })->count();
        if ($item['loai'] === 'Utility') {
            $this->invoiceables[$month]->transform(function ($value, $key) use ($ngayDangKy, $trangThai, $count, $itemKey) {
                // Nếu số lượng phiếu thu lớn hơn 1
                if ($count > 1) {
                    // Chỉ đổi selected của item được chọn
                    if ($key === $itemKey) {
                        $value['selected'] = $trangThai;
                    }
                } else {
                    if ($value['ngay']->isSameDay($ngayDangKy)) {
                        if ($value['loai'] === 'Surcharge') {
                            $value['selected'] = !$value['registered'] ? $trangThai : !$value['registered'];
                            // đổi disable cho phụ thu không bắt buộc
                            if (!$value['bat_buoc']) {
                                $value['disabled'] = !$value['selected'];
                            }
                        } else {
                            $value['selected'] = $value['selected'];
                            if ($key === $itemKey) {
                                $value['selected'] = $trangThai;
                            }
                        }
                    }
                }
                return $value;
            });
            $this->xuLyTinhTienPhuThuTheoTienIch($month, $ngayDangKy);
        } else {
            $this->invoiceables[$month]->transform(function ($value, $key) use ($itemKey, $trangThai) {
                if ($key === $itemKey) {
                    $value['selected'] = $trangThai;
                }
                return $value;
            });
        }
        $selectedItems = $this->invoiceables[$month]->filter(function ($value, $key) {
            return $value['selected'] === true && $value['registered'] === false;
        })->keys()->all();
        $this->selectedItems[$month] = $selectedItems;
        $this->tinhTien();
    }

    public function xuLyTinhTienPhuThuTheoTienIch($month, $ngayDangKy)
    {
        $blockDaChon = $this->blocks->filter(function ($value, $key) {
            return $value['selected'];
        });
        $xuLyPhuThuCuaTienIch = $this->invoiceables[$month]->filter(function ($value, $key) use ($ngayDangKy) {
            return $value['selected'] === true && !$value['registered'] && $value['ngay']->isSameDay($ngayDangKy) && $value['loai'] === 'Utility';
        });
        $count = $xuLyPhuThuCuaTienIch->count();
        $amount = $xuLyPhuThuCuaTienIch->sum('thanh_tien');
        $this->invoiceables[$month]->transform(function ($item, $key) use ($ngayDangKy, $count, $amount, $blockDaChon) {
            if ($count > 0) {
                $soLuong = $count;
                $tongTien = $amount;
            } else {
                $soLuong = $blockDaChon->count();
                $tongTien = $blockDaChon->sum('price');
            }
            if ($item['ngay']->isSameDay($ngayDangKy) && $item['loai'] === 'Surcharge') {
                $quantity = $item['thu_theo_block'] ? $soLuong : 1;
                $price = $item['muc_thu'];
                if (!$item['co_dinh']) {
                    $price = $tongTien * $item['muc_thu'] / 100;
                }
                $amount = $quantity * $price;
                $item['so_luong'] = $quantity;
                $item['thanh_tien'] = $amount;
            }
            return $item;
        });
    }

    public function tinhTien()
    {
        foreach ($this->invoiceables as $month => $itemList) {
            $phiDangKy = $itemList->filter(function ($value, $key) {
                return $value['loai'] === 'Utility' && $value['selected'] && !$value['registered'];
            })->sum('thanh_tien');
            $phiPhuThu = $itemList->filter(function ($value, $key) {
                return $value['loai'] === 'Surcharge' && $value['selected'] && !$value['registered'];
            })->sum('thanh_tien');
            $tongTien = $phiDangKy + $phiPhuThu;
            $this->invoices->put($month, [
                'phi_dang_ky' => $phiDangKy,
                'phi_phu_thu' => $phiPhuThu,
                'tong_tien' => $tongTien,
            ]);
        }
    }

    public function ngayDaDangKy($date, $start, $end)
    {
        return Registration::withWhereHas('utilities', function ($query) use ($date, $start, $end) {
            $query->whereDate('thoi_gian', Carbon::parse($date)->toDateString())->where('thoi_gian_bat_dau', $start)->where('thoi_gian_ket_thuc', $end);
        })->count();
    }

    public function store()
    {
        if (($this->remainingTimes > 0 || $this->utility->max_times == 0)) {
            $registedInvoiceable = 0;
            foreach ($this->invoiceables as $month => $itemList) {
                $utilities = [];
                $danhSachTienIch = $itemList->filter(function ($value, $key) {
                    return $value['loai'] === 'Utility' && $value['selected'] && !$value['registered'];
                });
                $danhSachPhuThu = $itemList->filter(function ($value, $key) {
                    return $value['selected'] && !$value['registered'] && $value['loai'] === 'Surcharge';
                });
                $invoices = $this->invoices->filter(function ($value, $key) use ($month) {
                    return $key == $month;
                });
                if ($danhSachTienIch && $danhSachTienIch->count() > 0 && $invoices) {
                    foreach ($danhSachTienIch as $utility) {
                        $registedInvoiceable = $this->ngayDaDangKy($utility['ngay'], $utility['bat_dau'], $utility['ket_thuc']);
                        if ($registedInvoiceable > 0) {
                            $this->resetUtility();
                            Notification::make()->title('Đã có người đăng ký thời gian này')->danger()->send();
                            break 2;
                        } else {
                            $utilities[] = [
                                'thoi_gian' => $utility['ngay'],
                                'mo_ta' => $utility['mo_ta'],
                                'thoi_gian_bat_dau' => $utility['bat_dau'],
                                'thoi_gian_ket_thuc' => $utility['ket_thuc'],
                                'so_luong' => 1,
                                'muc_thu' => $this->utility->don_gia,
                                'thanh_tien' => $utility['thanh_tien'],
                            ];
                        }
                    }
                    if ($utilities && count($utilities) > 0 && $invoices) {
                        $registration = Registration::create([
                            'apartment_id' => $this->apartment_id,
                            'customer_id' => $this->customer_id,
                            'thoi_gian_dang_ky' => Carbon::parse(now())->format('Y-m-d H:i'),
                            'mo_ta' => ucfirst(strtolower("Đăng ký tiện ích {$this->utility->utilityType->ten_loai_tien_ich} ({$this->utility->ten_tien_ich})")),
                            'phi_dang_ky' => $invoices[$month]['phi_dang_ky'],
                            'phu_thu' => $invoices[$month]['phi_phu_thu'],
                            'tong_tien' => $invoices[$month]['tong_tien'],
                            'da_thanh_toan' => false,
                        ]);
                        for ($i = 0; $i < count($utilities); $i++) {
                            $registration->utilities()->attach($this->utility_id, $utilities[$i]);
                        }
                        if ($danhSachPhuThu) {
                            foreach ($danhSachPhuThu as $surcharge) {
                                $registration->surcharges()->attach($surcharge['id'], [
                                    'thoi_gian' => $surcharge['ngay'],
                                    'mo_ta' => $surcharge['mo_ta'],
                                    'so_luong' => $surcharge['so_luong'],
                                    'muc_thu' => $surcharge['muc_thu'],
                                    'thanh_tien' => $surcharge['thanh_tien'],
                                ]);
                            }
                        }
                        if ($this->selectedCustomerId) {
                            foreach ($this->selectedCustomerId as $customerId) {
                                $registration->members()->attach($customerId);
                            }
                        }
                    }
                }
            }
            if ($registedInvoiceable == 0) {
                $this->resetUtility();
                Notification::make()
                    ->title('Đăng kí thành công')
                    ->success()
                    ->send();
            }
        } else {
            $this->resetUtility();
            Notification::make()->title('Đã hết lượt đăng ký')->danger()->send();
        }
    }
}
