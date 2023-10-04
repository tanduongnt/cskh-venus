<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Apartment;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use App\Extend\Filament\NestedResource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ApartmentResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ApartmentResource\RelationManagers;

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
                    Hidden::make('project_id'),
                    TextInput::make('name')
                        ->required()
                        ->label('Tên căn hộ')
                        ->columnSpan(2),
                    TextInput::make('sort')
                        ->nullable()
                        ->numeric()
                        ->label('Sắp xếp'),
                    TextInput::make('location')
                        ->required()
                        ->label('Tầng')
                        ->columnSpan(2),
                    TextInput::make('area')
                        ->nullable()
                        ->numeric()
                        ->label('Diện tích (m²)')
                        ->columnSpan(2),
                    RichEditor::make('description')
                        ->nullable()
                        ->label('Mô tả')
                        ->columnSpan('full'),
                    Toggle::make('active')->label('Theo dõi'),
                ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Tên căn hộ')->sortable()->searchable(),
                TextColumn::make('location')->label('vị trí')->sortable(),
                TextColumn::make('apartment')->label('căn hộ')->sortable(),
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
            //
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
