<?php

namespace App\Filament\Admin\Resources\RegistrationResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Surcharge;
use Filament\Tables\Table;
use App\Models\Registration;
use App\Models\SurchargeRegistration;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class SurchargesRelationManager extends RelationManager
{
    protected static string $relationship = 'surcharges';

    protected static ?string $title = 'Chi tiết phụ thu';

    protected static ?string $pluralModelLabel = 'Chi tiết phụ thu';

    protected static ?string $modelLabel = 'Chi tiết phụ thu';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('ten_phu_thu')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            //->recordTitleAttribute('ten_phu_thu')
            ->columns([
                TextColumn::make('thoi_gian')->dateTime('d/m/Y')->label('Thời gian'),
                TextColumn::make('mo_ta')->label('Mô tả'),
                TextColumn::make('so_luong')->label('Số lượng'),
                TextColumn::make('muc_thu')->formatStateUsing(function ($record, string $state) {
                    $state = ($record->co_dinh) ? moneyFormat($state).'đ' : "{$state}%";
                    return $state;
                })->label('Mức thu'),
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
