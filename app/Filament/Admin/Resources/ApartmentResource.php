<?php

namespace App\Filament\Admin\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Customer;
use Filament\Forms\Form;
use App\Models\Apartment;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Enums\ApartmentCustomerRole;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use App\Extend\Filament\NestedResource;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Admin\Resources\BuildingResource;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Admin\Resources\ApartmentResource\Pages;
use App\Filament\Admin\Resources\ApartmentResource\RelationManagers;
use Filament\Forms\Components\Group;

class ApartmentResource extends NestedResource
{
    protected static ?string $model = Apartment::class;

    protected static ?string $recordTitleAttribute = 'name';

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
                    TextInput::make('code')
                        ->required()
                        ->unique()
                        ->label('Mã căn hộ'),
                    TextInput::make('name')
                        ->required()
                        ->label('Tên căn hộ')
                        ->columnSpan(2),
                    TextInput::make('sort')
                        ->nullable()
                        ->numeric()
                        ->label('Sắp xếp'),
                    RichEditor::make('description')
                        ->nullable()
                        ->label('Mô tả')
                        ->columnSpan('full'),
                    Toggle::make('active')->label('Theo dõi'),
                ])->columns(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Mã căn hộ')->sortable()->searchable(),
                TextColumn::make('name')->label('Tên căn hộ')->sortable()->searchable(),
                TextColumn::make('owners.name')->label('Chủ hộ')->sortable(),
                TextColumn::make('customers_count')->counts('customers')->label('Nhân khẩu')->sortable(),
                IconColumn::make('active')->boolean()->label('Hoạt động'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CustomersRelationManager::class
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
