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
use App\Models\Registration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Actions\Action;
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
    public float $totalSurchargeAmount = 0;
    public float $totalBlockAmount = 0;
    public float $totalSurchargeAmountByMonth = 0;
    public float $totalBlockAmountByMonth = 0;
    public float $remainingTimes = 0;
    public float $amount = 0;
    public float $quantity = 1;

    public $dates;
    public $week;
    public $selectedSurcharges = [];
    public $groupDatesByMonth = [];
    public $keyInvoice = [];

    public ?Collection $buildings;
    public ?Collection $apartments;
    public ?Collection $customers;
    public ?Collection $utilities;
    public ?Collection $utility_types;
    public ?Utility $utility;
    public ?Collection $blocks;
    public ?Collection $invoices;
    public ?Collection $invoiceables;
    public ?Collection $selectedBlocks;
    public ?Collection $surchargeList;

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
                Wizard::make([
                    Wizard\Step::make('Building')
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
                        ])
                        ->label('Căn hộ')
                        ->columns(2),
                    Wizard\Step::make('UtilityType')
                        ->schema([
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
                                ->live()
                        ])
                        ->label('Tiện ích')
                        ->columns(2),
                ])
                    ->nextAction(
                        fn (Action $action) => $action->label('Tiếp'),
                    )
                    ->startOnStep($this->step),

                Fieldset::make('date')
                    ->label('')
                    ->schema([
                        TextInput::make('customer_id'),
                        TextInput::make('utility_id'),
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
                            ->live()
                            ->native(false)
                            ->label('Ngày trong tuần'),
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

    public function updatedWeek()
    {
        if ($this->week) {
            $this->resetUtility();
        }
    }

    public function updatedSelectedSurcharges()
    {
        $this->loadSurchargeList();
        $this->detailInvoices();
    }

    public function resetUtility()
    {
        $this->totalSurchargeAmount = 0;
        $this->totalBlockAmount = 0;
        $this->totalSurchargeAmountByMonth = 0;
        $this->totalBlockAmountByMonth = 0;
        $this->selectedSurcharges = [];
        $this->keyInvoice = [];
        $this->blocks = collect();
        $this->invoices = collect();
        $this->invoiceables = collect();
        $this->loadUtility();
        $this->loadRemainingTimes();
        $this->generateUtilityBlocks();
        $this->loadSurchargeList();
        //$this->detailInvoices();
    }

    public function loadUtility()
    {
        $this->utility = Utility::with(['surcharges'])->find($this->utility_id);
        //dd($start);
        if ($this->utility->surcharges) {
            $this->surchargeList = $this->utility->surcharges->where('active', true);
        }
    }

    public function loadRemainingTimes()
    {
        $this->remainingTimes = 0;
        if ($this->utility->gioi_han > 0) {
            $registrationCount = Registration::where('apartment_id', $this->apartment_id)->count();
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

    public function loadSurchargeList()
    {
        $this->totalSurchargeAmount = 0;
        // Lấy danh sách phụ thu theo tiện ích
        if ($this->surchargeList->count() > 0) {
            // Đếm số block được chọn
            $selectedBlocks = $this->blocks->filter(function ($value, $key) {
                return $value['selected'];
            });
            $selectedBlockCount = $selectedBlocks->count();
            $selectedBlockAmount = $selectedBlocks->sum('price');
            $this->surchargeList->transform(function ($item, $key) use ($selectedBlockCount, $selectedBlockAmount) {
                $quantity = $item['thu_theo_block'] ? $selectedBlockCount : 1;
                $price = $item['muc_thu'];
                if (!$item['co_dinh']) {
                    $price = $selectedBlockAmount * $item['muc_thu'] / 100;
                }
                $amount = $quantity * $price;
                $item['so_luong'] = $quantity;
                $item['tong_tien'] = $amount;
                $item['selected'] = $item['mac_dinh'];
                return $item;
            });
            $this->setDefaultSurcharge();
        }
    }

    public function setDefaultSurcharge()
    {
        // lấy danh sách phụ thu mặc định
        $defaultSurcharge = $this->surchargeList->filter(function ($value, $key) {
            return $value['mac_dinh'];
        })->pluck('id')->toArray();
        $this->selectedSurcharges = array_unique(array_merge($this->selectedSurcharges, $defaultSurcharge));
        // Lấy danh sách phụ thu được chọn theo id
        $selectedSurchargeList = $this->surchargeList->whereIn('id', $this->selectedSurcharges);
        // Tính tổng tiền phụ thu được chọn
        if ($selectedSurchargeList->count() > 0) {
            $this->totalSurchargeAmount = $selectedSurchargeList->sum('tong_tien');
        }
    }

    public function selectBlock($index)
    {
        $block = $this->blocks[$index];
        $block['selected'] = !$block['selected'];
        if ($block['chargeable']) {
            $price = ($block['selected']) ? $block['price'] : -$block['price'];
            $this->totalBlockAmount += $price;
        }
        $this->blocks->put($index, $block);
        $this->loadSurchargeList();
        $this->detailInvoices();
    }

    public function countBlockRegisted($start, $end)
    {
        if ($this->dates) {
            $dates = preg_split('/\s*-\s*/', trim($this->dates));
            $startDate = Carbon::createFromFormat('d/m/Y', $dates[0])->toDateString();
            $endDate = Carbon::createFromFormat('d/m/Y', $dates[1])->toDateString();
            return Registration::where('apartment_id', $this->apartment_id)->whereHas('utilities', function ($query) use ($startDate, $endDate, $start, $end) {
                $query->where('utilities.id', $this->utility_id);
                $query->whereBetween('thoi_gian', [$startDate, $endDate]);
                $query->where('thoi_gian_bat_dau', $start);
                $query->where('thoi_gian_ket_thuc', $end);
            })->count();
        }
    }

    public function detailInvoices()
    {
        $this->loadSurchargeList();
        $selectedBlocks = $this->blocks->filter(function ($value, $key) {
            return $value['selected'];
        });
        if ($this->dates) {
            $dayOfWeek = collect();
            $invoiceableByBlock = collect();
            $invoiceableBycharge = collect();
            $invoices = [];
            $invoiceables = collect();
            $dateName = collect();
            $dates = preg_split('/\s*-\s*/', trim($this->dates));
            if (count($dates) > 1) {
                $startDate = Carbon::createFromFormat('d/m/Y', $dates[0])->toDateString();
                $endDate = Carbon::createFromFormat('d/m/Y', $dates[1])->toDateString();
                $groupDatesByMonth = groupDatesByMonth($startDate, $endDate);
                foreach ($groupDatesByMonth as $month => $dateOfMonth) {
                    $groupDatesByKey = $groupDatesByMonth[$month];
                    foreach ($groupDatesByKey as $date) {
                        $dateOfWeek = Carbon::parse($date)->dayOfWeek;
                        $dayOfWeek->push([
                            'date' => $date,
                            'dateOfWeek' => $dateOfWeek,
                        ]);
                    }
                    $registration_dates = $dayOfWeek->whereIn('dateOfWeek', $this->week);
                    $this->invoices->put($month, [
                        'month'       => $month,
                        'dateOfMonth' => $registration_dates,
                    ]);
                    $selectedSurchargeList = $this->surchargeList->whereIn('id', $this->selectedSurcharges);
                    $nextKey = 0;
                    foreach ($selectedBlocks as $index => $block) {
                        foreach ($registration_dates as $key => $registration_date) {
                            if (Carbon::parse($registration_date['date'])->month == $month) {
                                $keyInvoiceableByBlock = "{$registration_date['date']} {từ {$block['start']->format('H:i')} dến {$block['end']->format('H:i')}";
                                $invoiceableByBlock->put($keyInvoiceableByBlock, [
                                    'key' => $keyInvoiceableByBlock,
                                    'thoi_gian' => Carbon::parse($registration_date['date']),
                                    'ten' =>  "từ {$block['start']->format('H:i')} dến {$block['end']->format('H:i')}",
                                    'so_luong' => 1,
                                    'muc_thu' => $block['price'],
                                    'thanh_tien' => $block['price'],
                                    'mac_dinh' => true,
                                ]);
                            }

                            foreach ($selectedSurchargeList as $keySurcharge => $surcharge) {
                                if (Carbon::parse($registration_date['date'])->month == $month) {
                                    $keyInvoiceableBycharge = "{$registration_date['date']} {$surcharge->ten_phu_thu}";
                                    $invoiceableBycharge->put($keyInvoiceableBycharge, [
                                        'key' => $keyInvoiceableBycharge,
                                        'thoi_gian' => Carbon::parse($registration_date['date']),
                                        'ten' => $surcharge->ten_phu_thu,
                                        'so_luong' => $surcharge->so_luong,
                                        'muc_thu' => $surcharge->muc_thu,
                                        'thanh_tien' => $surcharge->tong_tien,
                                        'mac_dinh' => $surcharge->mac_dinh,
                                    ]);
                                }
                            }
                        }
                    }
                    $this->invoiceables = $invoiceables->concat($invoiceableByBlock)->concat($invoiceableBycharge);
                    // if ($$this->invoiceables->count() > 0) {
                    $this->totalSurchargeAmountByMonth = $this->invoiceables->sum('phu_thu');
                    $this->totalBlockAmountByMonth = $this->invoiceables->sum('phi_dang_ky');
                    // }
                }
                //dd($selectedSurchargeList->count() * $registration_dates->count());
                //dd($dateName, $invoiceableByBlock, $selectedBlocks, $invoiceableBycharge, $this->invoiceables, $registration_dates);
            }
        }
        //dd($this->surchargeList, $this->invoiceables);
    }

    public function store()
    {
        $this->loadSurchargeList();
        $this->detailInvoices();
        $selectedBlocks = $this->blocks->filter(function ($value, $key) {
            return $value['selected'];
        });
        if ($selectedBlocks && $selectedBlocks->count() > 0) {
            foreach ($selectedBlocks as $key => $block) {
                $start = $block['start'];
                $end = $block['end'];
                // $registedInvoiceable = $this->countBlockRegisted($start, $end);
                // if ($registedInvoiceable > 0) {
                //     $this->resetUtility();
                //     Notification::make()->title('Đã có người đăng ký thời gian này')->danger()->send();
                //     break;
                // } else {
                // foreach ($registration_dates as $key => $registration_date) {
                //     $registrationUtility[] = [
                //         'thoi_gian' => $registration_date['date'],
                //         'thoi_gian_bat_dau' => $start,
                //         'thoi_gian_ket_thuc' => $end,
                //         'so_luong' => 1,
                //         'muc_thu' => $block['price'],
                //         'thanh_tien' => $block['price'],
                //     ];
                // }
                // }
            }

            // foreach ($groupDatesByMonth as $key => $dateOfMount) {
            //     dd($dateOfMount);
            //     foreach ($dateOfMount as $date) {
            //         $dateOfWeek = Carbon::parse($date)->dayOfWeek;
            //         $dayOfWeek->push([
            //             'date' => $date,
            //             'dateOfWeek' => $dateOfWeek,
            //         ]);
            //     }
            //     $registration = Registration::create([
            //         'apartment_id' => $this->apartment_id,
            //         'thoi_gian_dang_ky' => now(),
            //         'mo_ta' => ucfirst(strtolower("Đăng ký tiện ích {$this->utility->utilityType->ten_loai_tien_ich} ({$this->utility->ten_tien_ich})")),
            //         'nguoi_dang_ky' => $this->customer_id,
            //         'phi_dang_ky' => $this->totalSurchargeAmount,
            //         'phu_thu' => $this->totalBlockAmount,
            //         'tong_tien' => $this->totalSurchargeAmount + $this->totalBlockAmount,
            //     ]);
            //     $registration_dates = $dayOfWeek->whereIn('dateOfWeek', $this->week);
            //     $registration->utilities()->attach($this->utility_id, $value);
            // }


            //dd($registration);
        } else {
            Notification::make()->title('Chọn thời gian')->danger()->send();
        }
    }
}
