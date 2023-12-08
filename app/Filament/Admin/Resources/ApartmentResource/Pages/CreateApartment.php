<?php

namespace App\Filament\Admin\Resources\ApartmentResource\Pages;

use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Illuminate\Validation\Rules\Unique;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use App\Extend\Filament\ResourcePages\NestedPage;
use App\Filament\Admin\Resources\ApartmentResource;

class CreateApartment extends CreateRecord
{
    use NestedPage;

    protected static string $resource = ApartmentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    // form tạo apartment có thêm chủ hộ
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    Hidden::make('building_id'),
                    TextInput::make('ma_can_ho')
                        ->required()
                        ->unique(modifyRuleUsing: function (Unique $rule, Get $get) {
                            $building_id = $get('building_id');
                            return $rule->where('building_id', $building_id);
                        }, ignoreRecord: true)
                        ->label('Mã căn hộ'),
                    TextInput::make('sap_xep')
                        ->nullable()
                        ->numeric()
                        ->label('Sắp xếp'),
                    TextInput::make('dien_tich')
                        ->nullable()
                        ->numeric()
                        ->label('Diện tích m²'),
                    Select::make('customer_id')
                        ->relationship(name: 'customers', titleAttribute: 'ho_va_ten')
                        ->multiple()
                        ->minItems(1)
                        ->maxItems(1)
                        ->searchable()
                        ->label('Chủ hộ'),
                    Toggle::make('active')->default(true)->label('Theo dõi')->columnSpan('full'),
                ])->columns(4),
            ]);
    }
}
