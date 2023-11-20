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
use Illuminate\Support\Str;
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
    public float $remainingTimes = 0;
    public float $amount = 0;

    public $dates;
    public $week;
    public $selectedSurcharges = [];
    public $groupDatesByMonth = [];
    public $registrationUtilityItem = [];

    public ?Collection $buildings;
    public ?Collection $apartments;
    public ?Collection $customers;
    public ?Collection $utilities;
    public ?Collection $utility_types;
    public ?Utility $utility;
    public ?Collection $blocks;
    public ?Collection $invoiceables;
    public ?Collection $selectedBlocks;
    public ?Collection $surchargeList;

    public function mount()
    {
        $this->blocks = collect();
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
                            ->default(['1', '2', '3', '4', '5', '6', '0'])
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

    public function resetUtility()
    {
        $this->selectedSurcharges = [];
        $this->registrationUtilityItem = [];
        $this->blocks = collect();
        $this->invoiceables = collect();
        $this->loadUtility();
        $this->loadRemainingTimes();
        $this->generateUtilityBlocks();
        $this->loadSurchargeList();
    }

    public function loadUtility()
    {
        $this->utility = Utility::with(['surcharges'])->find($this->utility_id);
        //dd($start);
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
        if ($this->utility->surcharges) {
            $this->surchargeList = $this->utility->surcharges->where('active', true);
            $this->surchargeList->transform(function ($item, $key) {
                $item['selected'] = $item['bat_buoc'];
                return $item;
            });
        }
    }

    public function selectBlock($index)
    {
        $block = $this->blocks[$index];
        $block['selected'] = !$block['selected'];
        if ($block['chargeable']) {
            $price = ($block['selected']) ? $block['price'] : -$block['price'];
            //$this->totalBlockAmount += $price;
        }
        $this->blocks->put($index, $block);
        $this->detailInvoices();
        $this->loadSurchargeList();
    }

    public function detailInvoices()
    {
        // Lấy block theo selected
        $selectedBlocks = $this->blocks->filter(function ($value, $key) {
            return $value['selected'];
        });
        // Lấy danh sách phụ thu được chọn
        $selectedSurchargeList = $this->surchargeList->whereIn('id', $this->selectedSurcharges);
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
                    // tìm ngày trong tuần
                    $selectedDayOfWeek = Carbon::parse($date)->dayOfWeek;
                    if (in_array($selectedDayOfWeek, $this->week)) {
                        $keyInvoiceableByblock = Str::slug("{$date}");
                        $itemList->put($keyInvoiceableByblock, [
                            'key' => $keyInvoiceableByblock,
                            'ngay' => Carbon::parse($date),
                            'mo_ta' =>  "Phí sử dụng tiện ích",
                            'so_luong' => $selectedBlocks->count(),
                            'muc_thu' =>  $selectedBlocks->sum('price'),
                            'thanh_tien' => $selectedBlocks->sum('price'),
                            'co_dinh' => true,
                            'bat_buoc' => false,
                            'loai' => 'Utility',
                        ]);
                        foreach ($selectedSurchargeList as $keySurcharge => $surcharge) {
                            $keyInvoiceableBySurcharge = Str::slug("{$date}-{$surcharge->id}");
                            $itemList->put($keyInvoiceableBySurcharge, [
                                'key'  => $keyInvoiceableBySurcharge,
                                'ngay' => Carbon::parse($date),
                                'mo_ta' => $surcharge->ten_phu_thu,
                                'so_luong' => $surcharge->so_luong,
                                'muc_thu' => $surcharge->muc_thu,
                                'thanh_tien' => $surcharge->tong_tien,
                                'co_dinh' => $surcharge->co_dinh,
                                'bat_buoc' => $surcharge->mac_dinh,
                                'loai' => 'Surcharge'
                            ]);
                        }
                    }
                }
                $this->invoiceables->put($month, $itemList);
            }
            //dd($this->invoiceables, $itemList, $this->registrationUtilityItem);
            $this->setDefaultRegistration();
        }
    }

    public function setDefaultRegistration()
    {
        $defaultRegistration = collect();
        // lấy danh sách đăng ký mặc định
        foreach ($this->invoiceables as $month => $itemList) {
            $keys = $itemList->pluck('key');
            $defaultRegistration = $defaultRegistration->merge($keys);
        }
        $this->registrationUtilityItem = $defaultRegistration->toArray();
        //dd($defaultRegistration, $this->registrationUtilityItem);
        $this->amountByMonth();
    }

    public function amountByMonth()
    {
        $registration = collect();
    }

    public function store()
    {
        $registration = $this->invoiceables->filter(function ($value, int $key) {
            return in_array($key, $this->registrationUtilityItem);
        });
        dd($registration, $this->registrationUtilityItem);
        //dd($this->invoiceables, $this->registrationUtilityItem);
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
