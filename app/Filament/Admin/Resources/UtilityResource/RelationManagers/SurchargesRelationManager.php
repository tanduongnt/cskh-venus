<?php

namespace App\Filament\Admin\Resources\UtilityResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

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
                Forms\Components\TextInput::make('ten_phu_thu')
                    ->required()
                    ->maxLength(255)
                    ->label('Tên phụ thu'),
                Forms\Components\TextInput::make('muc_thu')
                    ->required()
                    ->maxLength(255)
                    ->label('Mức thu')
                    ->hint('Số tiền cố định hoặc phần trăm (%)'),
                Forms\Components\Toggle::make('bat_buoc')
                    ->default(1)
                    ->label('Bắt buộc')
                    ->hint('Phụ thu bắt buộc khi đăng ký tiện ích'),
                Forms\Components\Toggle::make('co_dinh')
                    ->default(1)
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state) {
                        if (!$state) {
                            $set('thu_theo_block', false);
                        }
                    })
                    ->label('Cố định'),
                Forms\Components\Toggle::make('thu_theo_block')
                    ->default(1)
                    ->label('Tính theo block')
                    ->hint('Phụ thu được tính theo số lượng block đăng ký')
                    ->hidden(fn (Get $get): bool => !$get('co_dinh')),
                Hidden::make('thu_theo_block'),
                Forms\Components\Toggle::make('active')
                    ->default(1)
                    ->label('Hoạt động'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('ten_phu_thu')
            ->columns([
                Tables\Columns\IconColumn::make('bat_buoc')->boolean()->label('Bắt buộc'),
                Tables\Columns\TextColumn::make('ten_phu_thu')->label('Tên phụ thu'),
                Tables\Columns\TextColumn::make('muc_thu')->label('Mức thu'),
                Tables\Columns\IconColumn::make('co_dinh')->boolean()->label('Cố định'),
                Tables\Columns\IconColumn::make('thu_theo_block')->boolean()->label('Tính theo block'),
                Tables\Columns\IconColumn::make('active')->boolean()->label('Hoạt động'),
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
