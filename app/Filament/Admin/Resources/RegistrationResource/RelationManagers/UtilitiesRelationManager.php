<?php

namespace App\Filament\Admin\Resources\RegistrationResource\RelationManagers;

use App\Models\Registration;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class UtilitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'utilities';

    protected static ?string $title = 'Chi tiết đăng ký';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('ten_tien_ich')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            //->recordTitleAttribute('ten_tien_ich')
            ->columns([
                TextColumn::make('thoi_gian')->dateTime('d/m/Y')->label('Thời gian'),
                TextColumn::make('mo_ta')->description(function ($record) {
                    //dd($record);
                    return "Từ {$record->thoi_gian_bat_dau} đến {$record->thoi_gian_ket_thuc}";
                })->label('Mô tả'),
                TextColumn::make('so_luong')->label('Số lượng'),
                TextColumn::make('muc_thu')->formatStateUsing(fn (string $state): string => moneyFormat($state).'đ')->label('Mức thu'),
                TextColumn::make('thanh_tien')->formatStateUsing(fn (string $state): string => moneyFormat($state).'đ')->label('Thành tiền'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
