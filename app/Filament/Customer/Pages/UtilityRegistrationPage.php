<?php

namespace App\Filament\Customer\Pages;

use Carbon\Carbon;
use App\Models\Utility;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Building;
use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Models\Apartment;
use App\Models\UtilityType;
use Illuminate\Support\Str;
use App\Models\RegistrationForm;
use App\Models\UtilityRegistration;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\View;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Expr\Cast\Double;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Illuminate\Support\Facades\Blade;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\CheckboxList;
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
    public ?string $date;
    public ?string $registrationDate;
    public ?string $totalPriceBlocks;

    public ?Collection $buildings;
    public ?Collection $apartments;
    public ?Collection $utilities;
    public ?Collection $utility_types;
    public ?Utility $utility;
    public ?Collection $blocks;
    public ?Collection $selectedBlocks;
    public ?Collection $registrationBlocks;

    public function mount()
    {
        $this->blocks = collect();
        $this->customer_id = Auth::id();
        $this->date = now()->toDateString();
        $this->registrationDate = now()->toDateString();
        $this->totalPriceBlocks = 0;

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

        $this->form->fill([
            'customer_id' => $this->customer_id,
            'building_id' => $this->buildings[0]->id,
            'apartment_id' => $this->buildings[0]->apartments[0]?->id,
            'date'         => $this->date,
            'registration_date' => $this->registrationDate,
        ]);
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

                Grid::make()
                    ->schema([
                        TextInput::make('customer_id'),
                        TextInput::make('utility_type_id'),
                        TextInput::make('utility_id'),
                        DatePicker::make('date')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->label('Ngày sử dụng'),
                        DatePicker::make('registration_date')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->label('Ngày đăng ký')
                            ->hidden('customer_id'),
                    ])
            ]);
    }

    public function updatedUtilityId()
    {
        if ($this->utility_id) {
            $this->generateUtilityBlocks($this->utility_id, $this->apartment_id);
        }
    }


    public function generateUtilityBlocks($utility_id, $apartment_id)
    {
        $this->utility = Utility::find($utility_id);
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
                $chargeable = $this->utility->charge_by_block && $charge_start_time->lte($block_start) && $charge_end_time->gte($block_end);
                $price = ($enable && $chargeable) ? $block_price : 0;
                $this->blocks->push([
                    'enable'        => $enable,
                    'start'         => $block_start,
                    'end'           => $block_end,
                    'chargeable'    => $chargeable,
                    'price'         => $price,
                    'selected'      => false,
                ]);
            }
        }
    }

    public function selectBlock($index)
    {
        $block = $this->blocks[$index];
        $block['selected'] = !$block['selected'];
        $this->totalPriceBlocks += $block['chargeable'] ? $block['price'] : 0;
        $this->blocks->put($index, $block);
    }

    public function store(): void
    {
        if ($this->utility->block) {
            $selectedBlocks = collect();
            $selected = $this->blocks->filter(function ($value, $key) {
                return $value['selected'];
            });
            $previousKey = 0;
            foreach ($selected as $key => $block) {
                if ($key - $previousKey == 1 && $selectedBlocks->count() > 0) {
                    $previousBlock = $selectedBlocks[$selectedBlocks->count() - 1];
                    $previousBlock['end'] = $block['end'];
                    $previousBlock['price'] += $block['chargeable'] ? $block['price'] : 0;
                    $selectedBlocks->put($selectedBlocks->count() - 1, $previousBlock);
                } else {
                    $selectedBlocks->push($block);
                }
                $previousKey = $key;
            }

            dd($this->totalPriceBlocks, $selectedBlocks);
            foreach ($selectedBlocks as $key => $selectedBlock) {
                $utilityRegistration = UtilityRegistration::create([
                    'customer_id' => $this->customer_id,
                    'apartment_id' => $this->apartment_id,
                    'registration_date'  => $this->registrationDate,
                    'total_price'   => $this->totalPriceBlocks,
                    'paid'      => false,
                ]);
                //dd($utilityRegistration);
            }
        } else {
        }
    }
}
