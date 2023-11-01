<?php

namespace App\Filament\Customer\Resources\InvoiceResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Invoiceable;
use App\Enums\InvoiceableType;
use App\Enums\InvoiceableTypeEnum;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class InvoiceablesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoiceables';

    protected static ?string $title = 'Chi tiết phiếu thu';

    protected static ?string $pluralModelLabel = 'Chi tiết phiếu thu';

    protected static ?string $modelLabel = 'Chi tiết phiếu thu';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('registration_date')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('invoiceables')
            // ->groups([
            //     Group::make('invoiceable_type')
            //         ->label('Loại')
            //         ->getTitleFromRecordUsing(fn (Invoiceable $record): string => $record->invoiceable_type),
            // ])->defaultGroup('invoiceable_type')
            ->columns([
                TextColumn::make('registration_date')->dateTime('d/m/Y')->label('Ngày sử dụng'),
                TextColumn::make('invoiceable.name')->label('Tên')->description(fn (Invoiceable $record): string => ($record->invoiceable_type == 'App\Models\Utility') ? "Từ {$record->start} đến {$record->end}" : ""),
                TextColumn::make('price')->money('VND')->label('Tiền (VNĐ)'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
