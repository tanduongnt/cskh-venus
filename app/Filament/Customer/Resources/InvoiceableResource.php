<?php

namespace App\Filament\Customer\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Invoiceable;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use App\Extend\Filament\NestedResource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Customer\Resources\InvoiceableResource\Pages;
use App\Filament\Customer\Resources\InvoiceableResource\RelationManagers;

class InvoiceableResource extends NestedResource
{
    protected static ?string $model = Invoiceable::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'invoiceables';

    protected static ?int $navigationSort = 3;


    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }


    public static function getParent(): string
    {
        return InvoiceResource::class;
    }

    public static function getPluralModelLabel(): string
    {
        return 'Chi tiết phiếu thu';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('registration_date')->dateTime('d/m/Y')->label('Ngày sử dụng'),
                TextColumn::make('invoiceable.name')->label('Tên'),
                TextColumn::make('start')->dateTime('H:i:s')->label('Bắt đầu'),
                TextColumn::make('end')->dateTime('H:i:s')->label('Kết thúc'),
                TextColumn::make('price')->money('VND')->label('Tiền (VNĐ)'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->hidden(),
                //Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListInvoiceables::route('/'),
            'create' => Pages\CreateInvoiceable::route('/create'),
            'view' => Pages\ViewInvoiceable::route('/{record}'),
            'edit' => Pages\EditInvoiceable::route('/{record}/edit'),
        ];
    }
}
