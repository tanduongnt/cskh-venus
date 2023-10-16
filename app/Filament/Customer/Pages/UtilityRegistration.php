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

    public int $step = 1;
    public ?string $customer_id;
    public ?string $building_id;
    public ?string $apartment_id;
    public ?string $utility_type_id;
    public ?string $utility_id;
    public ?string $date;
    public ?string $start_time;
    public ?string $end_time;
    public ?string $price;
    public ?Double $total_amount;

    public ?Collection $buildings;
    public ?Collection $apartments;
    public ?Utility $utility;
    public ?Collection $blocks;

    public function mount()
    {
        $this->blocks = collect();
        $this->customer_id = Auth::id();
        $this->date = now()->toDateString();
        //dd($this->date);

        $this->buildings = Building::withWhereHas('apartments', function ($query) {
            $query->whereHas('customers', function ($query) {
                $query->where('customer_id', Auth::id());
            });
        })->get();

        if ($this->buildings->count() == 1 && $this->buildings[0]->apartments->count() == 1) {
            $this->step = 2;
        }

        $this->form->fill([
            'customer_id' => $this->customer_id,
            'building_id' => $this->buildings[0]->id,
            'apartment_id' => $this->buildings[0]->apartments[0]->id,
            'date'         => $this->date,
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
                                ->options(fn (Get $get): Collection => UtilityType::whereHas('utilities', function ($query) use ($get) {
                                    $query->whereHas('building', function ($query) use ($get) {
                                        $query->where('building_id', $get('building_id'));
                                        $query->whereHas('apartments', function ($query) {
                                            $query->whereHas('customers', function ($query) {
                                                $query->where('customer_id', Auth::id());
                                            });
                                        });
                                    });
                                })->pluck('name', 'id'))
                                // ->getSearchResultsUsing(function (string $search, Get $get) {
                                //     return UtilityType::whereHas('utilities', function ($query) use ($get) {
                                //         $query->whereHas('building', function ($query) use ($get) {
                                //             $query->where('building_id', $get('building_id'));
                                //             $query->whereHas('apartments', function ($query) {
                                //                 $query->whereHas('customers', function ($query) {
                                //                     $query->where('customer_id', Auth::id());
                                //                 });
                                //             });
                                //         });
                                //     })->where('name', 'LIKE', "%{$search}%")->pluck('name', 'id');
                                // })
                                //->getOptionLabelUsing(fn ($value): ?string => UtilityType::find($value)?->name)
                                ->searchable()
                                ->required()
                                ->searchPrompt('Tìm theo loại tiện ích')
                                ->label('Loại tiện ích')
                                ->columnSpan(1),

                            Select::make('utility_id')
                                ->options(fn (Get $get): Collection => Utility::whereHas('building', function ($query) use ($get) {
                                    $query->where('building_id', $get('building_id'));
                                    $query->whereHas('apartments', function ($query) {
                                        $query->whereHas('customers', function ($query) {
                                            $query->where('customer_id', Auth::id());
                                        });
                                    });
                                })->where('registrable', true)->where('block', '>', 0)->where('utility_type_id', $get('utility_type_id'))->pluck('name', 'id'))
                                // ->getSearchResultsUsing(function (string $search, Get $get) {
                                //     return Utility::whereHas('building', function ($query) use ($get) {
                                //         $query->where('building_id', $get('building_id'));
                                //         $query->whereHas('apartments', function ($query) {
                                //             $query->whereHas('customers', function ($query) {
                                //                 $query->where('customer_id', Auth::id());
                                //             });
                                //         });
                                //     })->where('block', '>', 0)->where('utility_type_id', $get('utility_type_id'))->where('name', 'LIKE', "%{$search}%")->pluck('name', 'id');
                                // })
                                // ->getOptionLabelUsing(fn ($value): ?string => Utility::find($value)?->name)
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
                ])
                    ->nextAction(
                        fn (Action $action) => $action->label('Tiếp'),
                    )
                    ->startOnStep($this->step),

                Grid::make()
                    ->schema([
                        TextInput::make('customer_id'),
                        TextInput::make('utility_id'),
                        DatePicker::make('date'),
                    ])
            ]);
    }

    public function updatedUtilityId()
    {
        if ($this->utility_id) {
            $this->generateUtilityBlocks($this->utility_id);
        }
    }

    public function generateUtilityBlocks($utility_id)
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
                $charge_enable = $this->utility->charge_by_block = true && $charge_start_time->lte($block_start) && $charge_end_time->gte($block_end);
                $this->price = $enable && $charge_enable ? $block_price : 0;
                $this->blocks->push([
                    'enable' => $enable,
                    'start' => $block_start,
                    'end' => $block_end,
                    'price' => $this->price,
                ]);
            }
        }
    }

    public function selectBlock($block_start, $block_end)
    {
        $this->start_time = $block_start;
        $this->end_time = $block_end;
        //dd($this->start_time, $this->end_time);
    }

    public function create(): void
    {
        dd($this->form->getState(), $this->start_time, $this->end_time);
        $registrationForm = new RegistrationForm();
        $registrationForm->utility_id = $this->utility_id;
        $registrationForm->customer_id = $this->customer_id;
        $registrationForm->date = $this->date;
        $registrationForm->start_time = $this->start_time;
        $registrationForm->end_time = $this->end_time;
        $registrationForm->total_amount = $this->price;
        $registrationForm->save();
    }
}
