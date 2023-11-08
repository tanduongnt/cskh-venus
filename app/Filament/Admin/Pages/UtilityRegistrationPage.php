<?php

namespace App\Filament\Admin\Pages;

use App\Models\Utility;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Building;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Models\Apartment;
use App\Models\UtilityType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;

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
    public float $remainingTimes = 0;
    public float $amount = 0;
    public float $quantity = 1;


    public $selectedSurcharges = [];
    public $defaultSurcharge = [];

    public ?Collection $buildings;
    public ?Collection $apartments;
    public ?Collection $customers;
    public ?Collection $utilities;
    public ?Collection $utility_types;
    public ?Utility $utility;
    public ?Collection $blocks;
    public ?Collection $selectedBlocks;
    public ?Collection $surchargeList;

    public function mount()
    {
        $this->buildings = Building::all();
        $this->apartments = collect();
        $this->customers = collect();
        $this->utility_types = collect();
        $this->utilities = collect();
        //dd($this->buildings);
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
                        TextInput::make('utility_type_id'),
                        TextInput::make('utility_id'),
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

    public function store()
    {
    }
}
