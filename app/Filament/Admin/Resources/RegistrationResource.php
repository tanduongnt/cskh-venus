<?php

namespace App\Filament\Admin\Resources;

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
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use Filament\Resources\RelationManagers\RelationGroup;
use App\Filament\Admin\Resources\RegistrationResource\Pages;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use App\Filament\Admin\Resources\RegistrationResource\RelationManagers;
use App\Models\UtilityType;

class RegistrationResource extends Resource
{
    protected static ?string $model = Registration::class;

    protected static ?string $recordTitleAttribute = 'mo_ta';

    protected static ?string $slug = 'registration';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $breadcrumb = 'Phiếu thu tiện ích';

    protected static ?int $navigationSort = 4;

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
                    Select::make('customers')
                        ->multiple()
                        ->relationship(name: 'members', titleAttribute: 'ho_va_ten')
                        ->label('Thành viên'),
                    DateTimePicker::make('thoi_gian_dang_ky')
                        ->required()
                        ->native(false)
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
                TextColumn::make('tong_tien')->formatStateUsing(fn (string $state): string => moneyFormat($state) . 'đ')->label('Tổng tiền'),
                IconColumn::make('da_thanh_toan')->boolean()->label('Đã thanh toán'),
            ])->defaultSort('created_at', 'DESC')
            ->filters([
                DateRangeFilter::make('thoi_gian_dang_ky')->label('Thời gian đăng ký')->withIndicator(),
                SelectFilter::make('apartment_id')
                    ->relationship('apartment', 'ma_can_ho')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('Mã căn hộ'),
                SelectFilter::make('utilityTypes')
                    ->query(function (Builder $query, array $data) {
                        if ($data['value']) {
                            $query->whereHas('utilityTypes', function ($query) use ($data) {
                                $query->where('utility_types.id', $data['value']);
                            });
                        }
                    })
                    ->getSearchResultsUsing(function (string $search) {
                        return UtilityType::where('ten_loai_tien_ich', 'LIKE', "%{$search}%")->limit(10)->pluck('ten_loai_tien_ich', 'id');
                    })
                    ->getOptionLabelUsing(fn ($value): ?string => UtilityType::find($value)?->ten_loai_tien_ich)
                    ->searchable()
                    ->preload()
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['value']) {
                            return null;
                        }
                        $utility_type = UtilityType::find($data['value'])?->ten_loai_tien_ich;
                        return 'Loại tiện ích: ' . $utility_type;
                    })
                    ->label('Loại tiện ích'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                //Tables\Actions\EditAction::make()->hidden(fn (): bool => !can('registration.edit')),
                Tables\Actions\DeleteAction::make()->hidden(fn (): bool => !can('registration.delete')),
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
