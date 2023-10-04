<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Building;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use App\Extend\Filament\Columns\ChildResourceLink;
use App\Filament\Resources\BuildingResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\BuildingResource\RelationManagers;

class BuildingResource extends Resource
{
    protected static ?string $model = Building::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 1;

    public static function getPluralModelLabel(): string
    {
        return 'Chung cư';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    TextInput::make('name')
                        ->required()
                        ->label('Tên chung cư')
                        ->columnSpan(3),
                    TextInput::make('sort')
                        ->nullable()
                        ->numeric()
                        ->label('Sắp xếp'),
                    TextInput::make('address')
                        ->nullable()
                        ->label('Địa chỉ')
                        ->columnSpan(3),
                    TextInput::make('area')
                        ->nullable()
                        ->label('Diện tích (m²)'),
                    TextInput::make('floor')
                        ->nullable()
                        ->label('Tầng')
                        ->columnSpan(2),
                    TextInput::make('apartment')
                        ->nullable()
                        ->label('Căn hộ')
                        ->columnSpan(2),
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
                TextColumn::make('name')->label('Tên chung cư')->sortable()->searchable(),
                TextColumn::make('floor')->label('tầng')->sortable(),
                TextColumn::make('apartment')->label('căn hộ')->sortable(),
                ChildResourceLink::make(ApartmentResource::class)->label('Căn hộ đang hoạt động'),
                IconColumn::make('active')->boolean()->label('Vận hành'),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBuildings::route('/'),
            'create' => Pages\CreateBuilding::route('/create'),
            'view' => Pages\ViewBuilding::route('/{record}'),
            'edit' => Pages\EditBuilding::route('/{record}/edit'),
        ];
    }
}
