<?php

namespace App\Filament\Admin\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\UtilityType;
use Filament\Resources\Resource;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Admin\Resources\UtilityTypeResource\Pages;
use App\Filament\Admin\Resources\UtilityTypeResource\RelationManagers;

class UtilityTypeResource extends Resource
{
    protected static ?string $model = UtilityType::class;

    protected static ?string $slug = 'utility-types';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'ten_loai_tien_ich';


    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getPluralModelLabel(): string
    {
        return 'Loại tiện ích';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    TextInput::make('ten_loai_tien_ich')
                        ->required()
                        ->label('Tên loại tiện ích')
                        ->columnSpan(['md' => 2]),
                    TextInput::make('sap_xep')
                        ->nullable()
                        ->numeric()
                        ->label('Sắp xếp'),
                    RichEditor::make('mo_ta')
                        ->nullable()
                        ->label('Mô tả')
                        ->columnSpan('full'),
                ])->columns(['md' => 4]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ten_loai_tien_ich')->label('Loại tiện ích')->sortable()->searchable(),
                TextColumn::make('mo_ta')->label('Mô tả')->sortable(),
                TextColumn::make('sap_xep')->label('Sắp xếp')->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->hidden(fn (): bool => !can('utility_type.view')),
                Tables\Actions\EditAction::make()->hidden(fn (): bool => !can('utility_type.edit')),
                Tables\Actions\DeleteAction::make()->hidden(fn (): bool => !can('utility_type.delete')),
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
            'index' => Pages\ListUtilityTypes::route('/'),
            'create' => Pages\CreateUtilityType::route('/create'),
            'view' => Pages\ViewUtilityType::route('/{record}'),
            'edit' => Pages\EditUtilityType::route('/{record}/edit'),
        ];
    }
}
