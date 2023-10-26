<?php

namespace App\Filament\Customer\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Invoice;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Customer\Resources\InvoiceResource\Pages;
use App\Filament\Customer\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoiceable;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'invoices';

    protected static ?int $navigationSort = 3;


    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getPluralModelLabel(): string
    {
        return 'Phiếu thu';
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
            ->query(Invoice::query()->where('customer_id', Auth::id()))
            ->columns([
                TextColumn::make('apartment.name')->label('Tên căn hộ'),
                TextColumn::make('date')->dateTime('d/m/Y H:i:s')->label('Ngày đăng ký'),
                TextColumn::make('total_amount')->money('VND')->label('Tổng tiền (VNĐ)'),
                IconColumn::make('paid')->boolean()->label('Thanh toán'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('invoiceables')
                    ->url(fn (Invoice $record): string => InvoiceableResource::getUrl('index', ['invoice' => $record->id]))
                    ->openUrlInNewTab()
                    ->label('Chi tiết phiếu thu')
                    ->icon('heroicon-s-clipboard')
                    ->color('success'),
                //Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
