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

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('customer_id')
                    ->getSearchResultsUsing(function (string $search) {
                        return Customer::where('name', 'LIKE', "%{$search}%")->limit(10)->pluck('name', 'id');
                    })
                    ->getOptionLabelUsing(fn ($value): ?string => Customer::find($value)?->name)
                    ->required()
                    ->label('Tên')
                    ->searchable(),
                Select::make('role')
                    ->options(ApartmentCustomerRole::class)
                    ->required()
                    ->label('vai trò')
                    ->native(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Tên'),
                Tables\Columns\TextColumn::make('role')->formatStateUsing(fn (string $state): string => __($state))->label('Vai trò'),
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
                    ->attachAnother(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make()->label('Xóa')->modalHeading('Dữ liệu đã xóa không thể khôi phục'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
