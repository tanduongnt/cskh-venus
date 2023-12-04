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
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
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
    public $selectedSurcharges = [];

    public ?Collection $buildings;
    public ?Collection $apartments;
    public ?Collection $customers;
    public ?Collection $utilities;
    public ?Collection $utility_types;
    public ?Utility $utility;
    public ?Collection $blocks;
    public ?Collection $invoiceables;
    public ?Collection $invoices;
    public $surchargeList = [];

    public $selectedItems = [];

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
                                    //dd($this->apartments, $this->utility_types);
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
                            ->columnSpan(2),

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
                                    //dd($this->customers);
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
                            ->label('Tên căn hộ'),

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
                            ->label('Người đăng ký'),
                        Select::make('utility_type_id')
                            ->options(fn (Get $get): Collection => $this->utility_types->pluck('ten_loai_tien_ich', 'id'))
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {
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
                            ->live(),
                        DateRangePicker::make('dates')
                            ->setAutoApplyOption(true)
                            ->format('d/m/Y'),
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
                            ->multiple()
                            ->default(['1', '2', '3', '4', '5', '6', '0'])
                            ->native(false)
                            ->label('Ngày trong tuần'),
                    ])
                    ->label('Đăng ký tiện ích'),

                Fieldset::make('date')
                    ->label('')
                    ->schema([
                        TextInput::make('customer_id'),
                        TextInput::make('utility_id'),
                    ]),
                //->hidden(fn (Get $get): bool => !$get('utility_id')),
            ]);
    }

    public function updatedUtilityId()
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
        $this->surchargeList = [];
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
                //$registered = $this->countBlockRegisted($block_start, $block_end) > 0;
                $this->blocks->push([
                    'enable'        => $enable,
                    'start'         => $block_start,
                    'end'           => $block_end,
                    'chargeable'    => $chargeableBlock,
                    'price'         => $price,
                    'selected'      => $selected ?? false,
                    'registered'    => $registered ?? false,
                ]);
            }
            //dd($this->blocks);
        }
    }

    public function layDanhSachPhuThuBatBuoc()
    {
        if ($this->utility?->surcharges) {
            $surchargeList = $this->utility->surcharges->transform(function ($item, $key) {
                $item['selected'] = $item['bat_buoc'];
                return $item;
            })->toArray();
            $this->surchargeList = Arr::keyBy($surchargeList, 'id');
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
                //$this->capNhatPhieuThu($block, $selectedBlocks->count());
                $this->capNhatPhieuThuNhieuBlock($block, $selectedBlocks->count());
                //$this->capNhatNgayDaDangKy();
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
                        $registered = $this->ngayDaDangKy($date, $block['start'], $block['end']) > 0;
                        $key = Str::random();
                        if (!$registered) {
                            $this->selectedItems[$month][] = $key;
                        }
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

    public function capNhatPhieuThu($block, $count)
    {
        // Tổng tiền block được chọn
        $tongTien = $this->blocks->filter(function ($value, $key) {
            return $value['selected'];
        })->sum('price');
        // Cập nhật tiền đăng ký
        foreach ($this->invoiceables as $month => $itemList) {
            $itemList->transform(function ($item, $key) use ($block, $count, $tongTien) {
                $amount = 0;
                if ($item['loai'] === 'Utility') {
                    $item['so_luong'] = $count;
                    $amount = $tongTien;
                } else {
                    $quantity = $item['thu_theo_block'] ? $count : 1;
                    $price = $item['muc_thu'];
                    if (!$item['co_dinh']) {
                        $price = $tongTien * $item['muc_thu'] / 100;
                    }
                    $amount = $quantity * $price;
                    $item['so_luong'] = $quantity;
                }
                $item['thanh_tien'] = $amount;
                return $item;
            });
        }
        $this->capNhatNgayDaDangKy();
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
                    $itemList = $itemList->reject(function ($value, $key) use ($block) {
                        return $value['bat_dau'] === $block['start']->toTimeString() && $value['ket_thuc'] === $block['end']->toTimeString();
                    });
                    $this->invoiceables->put($month, $itemList);
                }
                $itemListTheoLoaiTienIchNhomTheoNgay = $itemList->filter(function ($item, $key) {
                    return $item['loai'] === 'Utility' && $item['selected'];
                })->groupBy(function ($item, $key) {
                    return $item['ngay']->format('d-m-Y');
                });
                $itemListTheoLoaiTienIchNhomTheoNgay->transform(function ($item) {
                    $item['tong_so_luong'] = $item->sum('so_luong');
                    $item['tong_tien'] = $item->sum('thanh_tien');
                    return $item;
                });
            }
            $ngayDangKy = $this->invoiceables[$month]->filter(function ($item, $key) {
                return $item['loai'] === 'Utility' && $item['selected'];
            })->pluck('ngay')->unique();
            $selected = $this->invoiceables[$month]->whereIn('ngay', $ngayDangKy);
            //dd($ngayDangKy, $selected);
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
        // $this->capNhatNgayDaDangKy();
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
                    $utility = $itemList->firstWhere(function ($item, $key) use ($date) {
                        return $item['selected'] && $item['loai'] === 'Utility' && $item['ngay']->isSameDay(Carbon::parse($date));
                    });
                    $surchargeList = $itemList->firstWhere(function ($item, $key) use ($date) {
                        return $item['loai'] === 'Surcharge' && $item['bat_buoc'] && $item['ngay']->isSameDay(Carbon::parse($date));
                    });
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
                //dd($itemList);
            }
        }
        $this->tinhTien();
    }

    public function xuLyDangKyTienIch($month, $itemKey)
    {
        $item = $this->invoiceables[$month][$itemKey];
        $ngayDangKy = $item['ngay'];
        $trangThai = !$item['selected'];
        $count = $this->invoiceables[$month]->filter(function ($value, $key) use ($ngayDangKy) {
            return $value['selected'] === true && !$value['registered'] && $value['ngay']->isSameDay($ngayDangKy) && $value['loai'] === 'Utility';
        })->count();
        //dd($count, $tongTien, $item, $this->invoiceables[$month]);
        if ($item['loai'] === 'Utility') {
            $this->invoiceables[$month]->transform(function ($item, $key) use ($ngayDangKy, $trangThai, $count, $itemKey) {
                if ($count > 1) {
                    if ($key === $itemKey) {
                        $item['selected'] = $trangThai;
                    }
                } else {
                    if ($item['ngay']->isSameDay($ngayDangKy)) {
                        $item['selected'] = !$item['registered'] ? $trangThai : !$item['registered'];
                    }
                }
                return $item;
            });
        } else {
            $this->invoiceables[$month]->transform(function ($item, $key) use ($itemKey, $trangThai) {
                if ($key === $itemKey) {
                    $item['selected'] = $trangThai;
                }
                return $item;
            });
        }
        $selectedItems = $this->invoiceables[$month]->filter(function ($item, $key) {
            return $item['selected'] === true && $item['registered'] === false;
        })->keys()->all();
        $this->selectedItems[$month] = $selectedItems;
        $this->tinhTien();
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

    public function capNhatNgayDaDangKy()
    {
        $blockDaChon = $this->blocks->filter(function ($value, $key) {
            return $value['selected'];
        });
        foreach ($this->invoiceables as $month => $itemList) {
            $registeredList = collect();
            $registrationList = collect();
            foreach ($blockDaChon as $key => $block) {
                $itemList->transform(function ($item, $key) use ($block) {
                    $registered = $this->ngayDaDangKy($item['ngay'], $block['start'], $block['end']) > 0;
                    if ($item['loai'] === 'Utility' && $registered) {
                        $item['registered'] = $registered;
                        $item['disabled'] = $registered;
                    }
                    //$item['selected'] = !$registered;
                    return $item;
                });
                $registeredList = $registeredList->merge($itemList->whereIn('registered', true));
                $registrationList = $registrationList->merge($itemList->whereIn('registered', false));
            }
            $items = $itemList->merge($registeredList);
            $this->invoiceables->put($month, $items);
            $selectedItems = $this->invoiceables[$month]->filter(function ($item, $key) {
                return $item['registered'] === false;
            })->keys();
            $this->selectedItems[$month] = $selectedItems;
        }
        //dd($itemList, $blockDaChon);
    }

    public function store()
    {
        if (($this->remainingTimes > 0 || $this->utility->max_times == 0)) {
            $blockDaChon = $this->blocks->filter(function ($value, $key) {
                return $value['selected'];
            });
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
                            dd($registedInvoiceable, $utility);
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
                            //dd($utilities, $utility);
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
                } else {
                    continue;
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
        //dd($registration);
    }
}
