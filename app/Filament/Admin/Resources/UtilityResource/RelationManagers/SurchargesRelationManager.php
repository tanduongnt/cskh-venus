<?php

namespace App\Filament\Admin\Resources\UtilityResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SurchargesRelationManager extends RelationManager
{
    protected static string $relationship = 'surcharges';

    protected static ?string $title = 'Phụ thu';

    protected static ?string $pluralModelLabel = 'phụ thu';

    protected static ?string $modelLabel = 'phụ thu';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Tên'),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->maxLength(255)
                    ->label('Mức thu')
                    ->hint('Số tiền cố định hoặc phần trăm (%)'),
                Forms\Components\Toggle::make('fixed')
                    ->default(1)
                    ->label('Cố định'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Tên'),
                Tables\Columns\TextColumn::make('price')->label('Mức thu'),
                Tables\Columns\IconColumn::make('fixed')->boolean()->label('Cố định'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Thêm mới'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
