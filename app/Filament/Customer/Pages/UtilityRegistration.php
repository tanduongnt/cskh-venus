<?php

namespace App\Filament\Customer\Pages;

use App\Models\Utility;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Building;
use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Models\Apartment;
use App\Models\UtilityType;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\View;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Illuminate\Support\Facades\Blade;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Concerns\InteractsWithForms;

class UtilityRegistration extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.customer.pages.utility';

    protected static ?string $title = 'Đăng ký tiện ích';

    protected static ?string $slug = 'utilities';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }


    public ?array $data = [];

    public $customer_id;
    public ?string $building_id = null;
    public ?string $apartment_id = null;
    public ?string $utility_type_id = null;
    public ?string $utility_id = null;
    public ?string $start_time = null;
    public ?string $end_time = null;
    public ?Collection $blocks;

    public function mount()
    {
        $this->blocks = collect();
        $this->customer_id = Auth::id();
        $this->form->fill([
            'customer_id' => $this->customer_id,
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
                                ->options(Building::whereHas('apartments', function ($query) {
                                    $query->whereHas('customers', function ($query) {
                                        $query->where('customer_id', Auth::id());
                                    });
                                })->pluck('name', 'id'))
                                ->searchable()
                                ->required()
                                ->searchPrompt('Tìm theo tên chung cư')
                                ->label('Tên chung cư')
                                ->columnSpan(1),

                            Select::make('apartment_id')
                                ->options(fn (Get $get): Collection => Apartment::whereHas('customers', function ($query) {
                                    $query->where('customer_id', Auth::id());
                                })->where('building_id', $get('building_id'))->pluck('name', 'id'))
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
                                ->getSearchResultsUsing(function (string $search, Get $get) {
                                    return UtilityType::whereHas('utilities', function ($query) use ($get) {
                                        $query->whereHas('building', function ($query) use ($get) {
                                            $query->where('building_id', $get('building_id'));
                                            $query->whereHas('apartments', function ($query) {
                                                $query->whereHas('customers', function ($query) {
                                                    $query->where('customer_id', Auth::id());
                                                });
                                            });
                                        });
                                    })->where('name', 'LIKE', "%{$search}%")->pluck('name', 'id');
                                })
                                ->getOptionLabelUsing(fn ($value): ?string => UtilityType::find($value)?->name)
                                ->searchable()
                                ->required()
                                ->searchPrompt('Tìm theo loại tiện ích')
                                ->label('Loại tiện ích')
                                ->columnSpan(1),

                            Select::make('utility_id')
                                ->getSearchResultsUsing(function (string $search, Get $get) {
                                    return Utility::whereHas('building', function ($query) use ($get) {
                                        $query->where('building_id', $get('building_id'));
                                        $query->whereHas('apartments', function ($query) {
                                            $query->whereHas('customers', function ($query) {
                                                $query->where('customer_id', Auth::id());
                                            });
                                        });
                                    })->where('utility_type_id', $get('utility_type_id'))->where('name', 'LIKE', "%{$search}%")->pluck('name', 'id');
                                })
                                ->getOptionLabelUsing(fn ($value): ?string => Utility::find($value)?->name)
                                ->searchable()
                                ->required()
                                ->searchPrompt('Tìm theo tên tiện ích')
                                ->label('Tiện ích')
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {
                                    if (($get('utility_id') ?? '')) {
                                        return;
                                    }
                                })
                        ])
                        ->label('Tiện ích')
                        ->columns(2),
                ]),

                Grid::make()
                    ->schema([
                        TextInput::make('customer_id'),
                        TextInput::make('utility_id'),
                        DatePicker::make('date'),
                    ])
            ]);
        //->statePath('data');
    }

    public function updatedUtilityId()
    {
        $this->generateUtilityBlocks($this->utility_id);
    }

    public function generateUtilityBlocks($utility_id)
    {
        $utility = Utility::find($utility_id);
        $this->start_time = $utility->start_time;
        $this->end_time = $utility->end_time;
        $this->blocks = collect();
        //dd($utility);
        if ($utility) {
            $blockCount = 1440 / $utility->block;
            for ($i = 0; $i < $blockCount; $i++) {
                $minutes =  $i * $utility->block;
                $start_time = Carbon::today()->addMinutes($minutes)->format('H:i');
                $end_time = Carbon::today()->addMinutes($minutes + $utility->block)->format('H:i');
                $this->blocks->push([
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                ]);
            }
            //dd($this->blocks);
        }
    }


    public function create(): void
    {
        dd($this->form->getState());
    }
}
