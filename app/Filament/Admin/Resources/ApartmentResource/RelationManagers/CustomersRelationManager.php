<?php

namespace App\Filament\Admin\Resources\ApartmentResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\ApartmentCustomerRole;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\AttachAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class CustomersRelationManager extends RelationManager
{
    protected static string $relationship = 'customers';

    protected static ?string $title = 'Thành viên';

    protected static ?string $pluralModelLabel = 'Thành viên';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('customer_id')
                    ->getSearchResultsUsing(function (string $search) {
                        return Customer::where('ho_va_ten', 'LIKE', "%{$search}%")->limit(10)->pluck('ho_va_ten', 'id');
                    })
                    ->getOptionLabelUsing(fn ($value): ?string => Customer::find($value)?->ho_va_ten)
                    ->required()
                    ->label('Tên')
                    ->searchable(),
                Select::make('vai_tro')
                    ->options(ApartmentCustomerRole::class)
                    ->required()
                    ->label('vai trò')
                    ->native(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('ho_va_ten')
            ->columns([
                Tables\Columns\TextColumn::make('ho_va_ten')->label('Tên'),
                Tables\Columns\TextColumn::make('vai_tro')->formatStateUsing(fn (string $state): string => __($state))->label('Vai trò'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()->form(fn (AttachAction $action): array => [
                    $action->getRecordSelect(),
                    Forms\Components\Select::make('role')
                        ->options(ApartmentCustomerRole::class)
                        ->required()
                        ->native(false),
                ])
                    ->label('Thêm mới')
                    ->modalHeading('Thêm mới thành viên')
                    ->modalSubmitActionLabel('Thêm mới')
                    ->successNotificationTitle('Thêm mới dữ liệu thành công')
                    ->attachAnother(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make()
                ->label('Xóa')
                ->modalHeading('Dữ liệu đã xóa không thể khôi phục')
                ->successNotificationTitle('Xóa dữ liệu thành công'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
