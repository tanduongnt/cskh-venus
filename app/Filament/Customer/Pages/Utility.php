<?php

namespace App\Filament\Customer\Pages;

use Filament\Forms\Get;
use App\Models\Building;
use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Models\Apartment;
use App\Models\UtilityType;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Illuminate\Support\Facades\Blade;
use App\Models\Utility as ModelsUtility;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Columns\TextColumn;

class Utility extends Page
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
    public ?string $utility_id = null;

    public function mount()
    {
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
                            TextInput::make('customer_id'),
                            Select::make('building_id')
                                ->getSearchResultsUsing(function (string $search) {
                                    return Building::whereHas('apartments', function ($query) {
                                        $query->whereHas('customers', function ($query) {
                                            $query->where('customer_id', Auth::id());
                                        });
                                    })->where('name', 'LIKE', "%{$search}%")->pluck('name', 'id');
                                })
                                ->getOptionLabelUsing(fn ($value): ?string => Building::find($value)?->name)
                                ->searchable()
                                ->searchPrompt('Điền tên chung cư')
                                ->label('Tên chung cư')
                                ->hidden(fn (Building $buildings): bool =>  ($buildings->whereHas('apartments', function ($query) {
                                    $query->whereHas('customers', function ($query) {
                                        $query->where('customer_id', Auth::id());
                                    });
                                })->count()) <= 1),
                        ])->label('Chung cư'),
                    Wizard\Step::make('Apartment')
                        ->schema([
                            Select::make('apartment_id')
                                ->getSearchResultsUsing(function (string $search, Get $get) {
                                    return Apartment::whereHas('customers', function ($query) {
                                        $query->where('customer_id', Auth::id());
                                    })->where('building_id', $get('building_id'))->where('name', 'LIKE', "%{$search}%")->pluck('name', 'id');
                                })
                                ->getOptionLabelUsing(fn ($value): ?string => Apartment::find($value)?->name)
                                ->searchable()
                                ->searchPrompt('Điền tên căn hộ')
                                ->label('Tên căn hộ'),
                        ])->label('Căn hộ'),
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
                                ->searchPrompt('Điền loại tiện ích')
                                ->label('Loại tiện ích'),
                        ])->label('Loại tiện ích'),
                    Wizard\Step::make('Utilities')
                        ->schema([
                            Select::make('utility_id')
                                ->getSearchResultsUsing(function (string $search, Get $get) {
                                    return ModelsUtility::whereHas('building', function ($query) use ($get) {
                                        $query->where('building_id', $get('building_id'));
                                        $query->whereHas('apartments', function ($query) {
                                            $query->whereHas('customers', function ($query) {
                                                $query->where('customer_id', Auth::id());
                                            });
                                        });
                                    })->where('utility_type_id', $get('utility_type_id'))->where('name', 'LIKE', "%{$search}%")->pluck('name', 'id');
                                })
                                ->getOptionLabelUsing(fn ($value): ?string => ModelsUtility::find($value)?->name)
                                ->searchable()
                                ->searchPrompt('Điền tiện ích')
                                ->label('Tiện ích')
                                ->afterStateHydrated(function (Select $component, string $state) {
                                    dd($state);
                                    $component->state(ucwords($state));
                                }),
                        ])->label('Tiện ích'),
                ])
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        dd($this->form->getState());
    }
}
