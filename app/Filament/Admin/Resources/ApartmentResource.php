<?php

namespace App\Filament\Admin\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Customer;
use Filament\Forms\Form;
use App\Models\Apartment;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use App\Enums\ApartmentCustomerRole;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use App\Extend\Filament\NestedResource;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Validation\Rules\Unique;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Admin\Resources\BuildingResource;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Admin\Resources\ApartmentResource\Pages;
use App\Filament\Admin\Resources\ApartmentResource\RelationManagers;

class ApartmentResource extends NestedResource
{
    protected static ?string $model = Apartment::class;

    protected static ?string $recordTitleAttribute = 'ma_can_ho';

    protected static ?string $slug = 'apartments';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getParent(): string
    {
        return BuildingResource::class;
    }

    public static function getPluralModelLabel(): string
    {
        return 'Căn hộ';
    }

    public static function form(Form $form): Form
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
                    Toggle::make('active')
                        ->default(true)
                        ->label('Theo dõi')
                        ->columnSpan('full'),
                ])->columns(['md' => 3]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ma_can_ho')->label('Mã căn hộ')->sortable()->searchable(),
                TextColumn::make('dien_tich')->label('Diện tích')->sortable()->searchable(),
                TextColumn::make('owners.ho_va_ten')->label('Chủ hộ')->sortable(),
                TextColumn::make('customers_count')->counts('customers')->label('Nhân khẩu')->sortable(),
                IconColumn::make('active')->boolean()->label('Hoạt động'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->hidden(fn (): bool => !can('apartment.view')),
                Tables\Actions\EditAction::make()->hidden(fn (): bool => !can('apartment.edit')),
                Tables\Actions\DeleteAction::make()->hidden(fn (): bool => !can('apartment.delete')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //RelationManagers\CustomersRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApartments::route('/'),
            'create' => Pages\CreateApartment::route('/create'),
            'view' => Pages\ViewApartment::route('/{record}'),
            'edit' => Pages\EditApartment::route('/{record}/edit'),
        ];
    }
}
