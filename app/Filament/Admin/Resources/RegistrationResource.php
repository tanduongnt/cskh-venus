<?php

namespace App\Filament\Admin\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Registration;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationGroup;
use App\Filament\Admin\Resources\RegistrationResource\Pages;
use App\Filament\Admin\Resources\RegistrationResource\RelationManagers;

class RegistrationResource extends Resource
{
    protected static ?string $model = Registration::class;

    protected static ?string $recordTitleAttribute = '';

    protected static ?string $slug = 'registration';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 4;

    public static function getPluralModelLabel(): string
    {
        return 'Phiếu thu tiện ích';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    Select::make('apartment_id')
                        ->relationship(name: 'apartment', titleAttribute: 'ma_can_ho')
                        ->label('mã căn hộ'),
                    Select::make('customer_id')
                        ->relationship(name: 'customer', titleAttribute: 'ho_va_ten')
                        ->label('Người đăng ký'),
                    DateTimePicker::make('thoi_gian_dang_ky')
                        ->required()
                        ->native(false)
                        ->minutesStep(30)
                        ->displayFormat('d/m/Y H:i')
                        ->label('Ngày đăng ký'),
                ])->columnSpan(2),

                Section::make()
                    ->schema([
                        TextInput::make('phi_dang_ky')
                            ->label('Phí đăng ký (đ)'),
                        TextInput::make('phu_thu')
                            ->label('Phụ thu (đ)'),
                        TextInput::make('tong_tien')
                            ->label('Tổng tiền (đ)'),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('thoi_gian_dang_ky')->date('d/m/Y')->label('Ngày đăng ký')->sortable()->searchable(),
                TextColumn::make('apartment.ma_can_ho')->label('Mã căn hộ'),
                TextColumn::make('customer.ho_va_ten')->label('Người đăng ký'),
                TextColumn::make('mo_ta')->label('Mô tả'),
                TextColumn::make('tong_tien')->label('Tổng tiền'),
                IconColumn::make('da_thanh_toan')->boolean()->label('Đã thanh toán'),
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
            RelationGroup::make('invoiceables', [
                RelationManagers\UtilitiesRelationManager::class,
                RelationManagers\SurchargesRelationManager::class,
            ]),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegistrations::route('/'),
            'create' => Pages\CreateRegistration::route('/create'),
            'view' => Pages\ViewRegistration::route('/{record}'),
            'edit' => Pages\EditRegistration::route('/{record}/edit'),
        ];
    }
}
