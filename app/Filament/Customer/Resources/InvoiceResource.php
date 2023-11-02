<?php

namespace App\Filament\Customer\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Invoice;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Invoiceable;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Customer\Resources\InvoiceResource\Pages;
use App\Filament\Customer\Resources\InvoiceResource\RelationManagers;

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
                Section::make()->schema([
                    Select::make('apartment_id')
                        ->relationship(name: 'apartment', titleAttribute: 'name')
                        ->label('Căn hộ'),
                    DateTimePicker::make('date')
                        ->required()
                        ->native(false)
                        ->minutesStep(30)
                        ->displayFormat('d/m/Y H:i')
                        ->label('Ngày đăng ký'),
                ])->columnSpan(2),

                Section::make()
                    ->schema([
                        TextInput::make('amount')
                            ->label('Phí đăng ký (đ)'),
                        TextInput::make('surcharge')
                            ->label('Phụ thu (đ)'),
                        TextInput::make('total_amount')
                            ->label('Tổng tiền (đ)'),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Invoice::query()->where('customer_id', Auth::id()))
            ->columns([
                TextColumn::make('apartment.name')->label('Tên căn hộ'),
                TextColumn::make('date')->dateTime('d/m/Y H:i:s')->label('Ngày đăng ký'),
                TextColumn::make('amount')->money('VND')->label('Phí đăng ký'),
                TextColumn::make('surcharge')->money('VND')->label('Phụ thu'),
                TextColumn::make('total_amount')->money('VND')->label('Tổng tiền'),
                IconColumn::make('paid')->boolean()->label('Thanh toán'),
                IconColumn::make('active')->boolean()->label('Hoạt động'),
            ])
            ->filters([
                //
            ])
            ->actions([
                //Tables\Actions\ViewAction::make(),
                //Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\InvoiceablesRelationManager::class
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
