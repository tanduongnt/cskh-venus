<?php

namespace App\Filament\Customer\Pages;

use Carbon\Carbon;
use App\Models\Invoice;
use App\Models\Utility;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Building;
use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Models\Apartment;
use App\Models\Invoiceable;
use App\Models\UtilityType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;

class UtilityRegistrationPage extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.customer.pages.utility-registration';

    protected static ?string $title = 'Đăng ký tiện ích';

    protected static ?string $slug = 'utilities';

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
    public float $remainingTimes = 0;
    public float $priceNotFixed = 0;


    public $selectedSurcharges = [];

    public ?Collection $buildings;
    public ?Collection $apartments;
    public ?Collection $utilities;
    public ?Collection $utility_types;
    public ?Utility $utility;
    public ?Collection $blocks;
    public ?Collection $selectedBlocks;
    public ?Collection $surchargeList;

    public function mount()
    {
        $this->blocks = collect();
        $this->customer_id = Auth::id();
        $this->registration_date = now()->toDateString();
        $this->buildings = Building::withWhereHas('apartments', function ($query) {
            $query->whereHas('customers', function ($query) {
                $query->where('customer_id', Auth::id());
            });
        })->get();

        if ($this->buildings->count() > 0) {
            $building_id = $this->buildings[0]->id;
            $this->apartments = $this->buildings[0]->apartments;
            $this->utility_types = UtilityType::whereHas('utilities', function ($query) use ($building_id) {
                $query->whereHas('building', function ($query) use ($building_id) {
                    $query->where('id', $building_id);
                    $query->whereHas('apartments', function ($query) {
                        $query->whereHas('customers', function ($query) {
                            $query->where('customer_id', Auth::id());
                        });
                    });
                });
            })->get();
            $this->utilities = $this->utility_types[0]->utilities;
            if ($this->buildings->count() == 1 && $this->apartments->count() == 1) {
                $this->step = 2;
            }
        }
        if ($this->buildings->count() > 0) {
            $this->form->fill([
                'customer_id' => $this->customer_id,
                'building_id' => $this->buildings[0]->id,
                'apartment_id' => $this->buildings[0]->apartments[0]?->id,
                'registration_date' => $this->registration_date,
            ]);
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
                                ->options($this->buildings->pluck('name', 'id'))
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {
                                    $this->apartments = collect();
                                    $this->utility_types = collect();
                                    if ($get('building_id')) {
                                        $this->apartments = Apartment::whereHas('customers', function ($query) {
                                            $query->where('customer_id', Auth::id());
                                        })->where('building_id', $get('building_id'))->get();

                                        $this->utility_types = UtilityType::whereHas('utilities', function ($query) use ($get) {
                                            $query->whereHas('building', function ($query) use ($get) {
                                                $query->where('id', $get('building_id'));
                                                $query->whereHas('apartments', function ($query) {
                                                    $query->whereHas('customers', function ($query) {
                                                        $query->where('customer_id', Auth::id());
                                                    });
                                                });
                                            });
                                        })->get();

                                        if ($this->apartments->count() > 0) {
                                            $set('apartment_id', $this->apartments[0]->id);
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
                                ->options(fn (Get $get): Collection => $this->apartments->where('building_id', $get('building_id'))->pluck('name', 'id'))
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {
                                    $this->utility_types = collect();
                                    if ($get('building_id')) {
                                        $this->utility_types = UtilityType::whereHas('utilities', function ($query) use ($get) {
                                            $query->whereHas('building', function ($query) use ($get) {
                                                $query->where('id', $get('building_id'));
                                                $query->whereHas('apartments', function ($query) {
                                                    $query->whereHas('customers', function ($query) {
                                                        $query->where('customer_id', Auth::id());
                                                    });
                                                });
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
                                ->searchPrompt('Tìm theo tên hoặc mã căn hộ')
                                ->label('Tên căn hộ'),
                        ])
                        ->label('Căn hộ')
                        ->columns(2),
                    Wizard\Step::make('UtilityType')
                        ->schema([
                            Select::make('utility_type_id')
                                ->options(fn (Get $get): Collection => $this->utility_types->pluck('name', 'id'))
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {
                                    $this->utilities = collect();
                                    if ($get('utility_type_id')) {
                                        $this->utilities = Utility::withWhereHas('building', function ($query) use ($get) {
                                            $query->where('id', $get('building_id'));
                                            $query->whereHas('apartments', function ($query) {
                                                $query->whereHas('customers', function ($query) {
                                                    $query->where('customer_id', Auth::id());
                                                });
                                            });
                                        })->where('registrable', true)->where('active', true)->where('utility_type_id', $get('utility_type_id'))->get();
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
                                ->options(fn (Get $get): Collection => $this->utilities->where('building_id', $get('building_id'))->where('utility_type_id', $get('utility_type_id'))->pluck('name', 'id'))
                                ->searchable()
                                ->required()
                                ->searchPrompt('Tìm theo tên tiện ích')
                                ->label('Tiện ích')
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {
                                    if ($get('utility_id')) {
                                        return;
                                    }
                                })
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
                        TextInput::make('customer_id')->hidden(),
                        TextInput::make('utility_type_id')->hidden(),
                        TextInput::make('utility_id')->hidden(),
                        DatePicker::make('registration_date')
                            ->closeOnDateSelection()
                            ->native(false)
                            ->live()
                            ->displayFormat('d/m/Y')
                            ->label('Ngày sử dụng')
                            ->columnSpan('full')
                    ])
                    ->hidden(fn (Get $get): bool => !$get('utility_id')),
            ]);
    }

    public function updatedUtilityId()
    {
        if ($this->utility_id && $this->registration_date) {
            $this->resetUtility();
        }
    }

    public function updatedRegistrationDate()
    {
        if ($this->registration_date && $this->utility_id) {
            $this->resetUtility();
        }
    }

    public function updatedSelectedSurcharges()
    {
        $this->loadSurchargeList();
    }

    public function resetUtility()
    {
        $this->loadUtility();
        $this->loadRemainingTimes();
        $this->generateUtilityBlocks();
        $this->loadSurchargeList();
    }

    public function loadUtility()
    {
        $this->utility = Utility::with(['surcharges'])->find($this->utility_id);
        if ($this->utility->surcharges) {
            $this->surchargeList = $this->utility->surcharges->where('active', true);
        }
    }

    public function loadRemainingTimes()
    {
        $this->remainingTimes = 0;
        if ($this->utility->max_times > 0) {
            $registrationCount = Invoice::where('apartment_id', $this->apartment_id)
                ->withWhereHas('invoiceables', function ($query) {
                    $query->whereHasMorph(
                        'invoiceable',
                        Utility::class,
                        function (Builder $query) {
                            $query->where('id', $this->utility_id);
                        }
                    );
                })->whereBetween('date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->count();
            $this->remainingTimes = $this->utility->max_times - $registrationCount;
        }
    }

    public function generateUtilityBlocks()
    {
        $this->totalSurchargeAmount = 0;
        $this->totalBlockAmount = 0;
        $this->priceNotFixed = 0;
        $this->selectedSurcharges = [];
        $this->blocks = collect();
        if ($this->utility->block) {
            $block_price = $this->utility->price;
            $blockCount = floor(1440 / $this->utility->block);
            $start_time = Carbon::parse($this->utility->start_time);
            $end_time = Carbon::parse($this->utility->end_time);
            $charge_start_time = Carbon::parse($this->utility->charge_start_time);
            $charge_end_time = Carbon::parse($this->utility->charge_end_time);
            for ($i = 0; $i < $blockCount; $i++) {
                $minutes =  $i * $this->utility->block;
                $block_start = Carbon::today()->addMinutes($minutes);
                $block_end = Carbon::today()->addMinutes($minutes + $this->utility->block);
                $enable =  $start_time->lte($block_start) && $end_time->gte($block_end);
                $chargeableBlock = $charge_start_time->lte($block_start) && $charge_end_time->gte($block_end);
                $price = ($enable && $chargeableBlock) ? $block_price : 0;
                $registered = $this->countBlockRegisted($block_start, $block_end) > 0;
                $this->blocks->push([
                    'enable'        => $enable,
                    'start'         => $block_start,
                    'end'           => $block_end,
                    'chargeable'    => $chargeableBlock,
                    'price'         => $price,
                    'selected'      => $selected ?? false,
                    'registered'    => $registered,
                ]);
            }
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
                $quantity = $item['by_block'] ? $selectedBlockCount : 1;
                $price = $item['price'];
                if (!$item['fixed']) {
                    $price = $selectedBlockAmount * $item['price'] / 100;
                }
                $amount = $quantity * $price;
                $item['quantity'] = $quantity;
                $item['amount'] = $amount;
                $item['selected'] = $item['default'];
                return $item;
            });
            // // lấy danh sách phụ thu mặc định
            // $defaultSurcharges = $this->surchargeList->filter(function ($value, $key) {
            //     return $value['default'];
            // })->pluck('id')->toArray();
            // // Cộng danh sách được chọn và danh sách mặc định.
            // $this->selectedSurcharges = array_unique(array_merge($this->selectedSurcharges, $defaultSurcharges));
            //dd($this->selectedSurcharges, $defaultSurcharges);

            // Lấy danh sách phụ thu được chọn theo id
            $selectedSurchargeList = $this->surchargeList->whereIn('id', $this->selectedSurcharges);
            // Tính tổng tiền phụ thu được chọn
            if ($selectedSurchargeList->count() > 0) {
                $this->totalSurchargeAmount = $selectedSurchargeList->sum('amount');
            }
            //dd($selectedSurchargeList, $this->surchargeList);
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
    }


    public function countBlockRegisted($start, $end)
    {
        return Invoiceable::whereHasMorph(
            'invoiceable',
            Utility::class,
            function (Builder $query) {
                $query->where('id', $this->utility_id);
            }
        )
            ->withWhereHas('invoice', function ($query) {
                $query->where('active', true);
            })
            ->whereDate('registration_date', Carbon::parse($this->registration_date)->toDateString())
            ->where('start', $start)
            ->where('end', $end)
            ->count();
    }

    public function store()
    {
        $invoiceables = collect();
        $selectedBlocks = $this->blocks->filter(function ($value, $key) {
            return $value['selected'];
        });
        foreach ($selectedBlocks as $key => $block) {
            $start = $block['start'];
            $end = $block['end'];
            $registedInvoiceable = $this->countBlockRegisted($start, $end);
            if ($registedInvoiceable > 0) {
                $this->generateUtilityBlocks();
                Notification::make()->title('Đã có người đăng ký thời gian này')->danger()->send();
                break;
            } else {
                $invoiceables->push([
                    'registration_date' => $this->registration_date,
                    'start' => $start,
                    'end' => $end,
                    'price' => $block['price'],
                ]);
            }
        }
        if ($this->remainingTimes > 0 || $this->utility->max_times == 0) {
            $invoice = Invoice::create([
                'customer_id' => $this->customer_id,
                'apartment_id' => $this->apartment_id,
                'date'  => now(),
                'surcharge' => $this->totalSurchargeAmount,
                'amount'   => $this->totalBlockAmount,
                'total_amount'   => $this->totalSurchargeAmount + $this->totalBlockAmount,
                'paid'      => false,
            ]);
            $invoiceables->transform(function ($item, $key) use ($invoice) {
                $item['invoice_id'] = $invoice->id;
                return $item;
            });
            $this->utility->invoiceable()->createMany($invoiceables);
            foreach ($this->surchargeList as $key => $surcharge) {
                $surcharge->invoiceable()->create([
                    'invoice_id' => $invoice->id,
                    'registration_date' => $this->registration_date,
                    'price' => $surcharge['price'],
                ]);
            }
            $this->resetUtility();
            Notification::make()
                ->title('Đăng kí thành công')
                ->success()
                ->send();
        } else {
            $this->generateUtilityBlocks();
            Notification::make()->title('Đã hết lượt đăng ký')->danger()->send();
        }
    }
}
