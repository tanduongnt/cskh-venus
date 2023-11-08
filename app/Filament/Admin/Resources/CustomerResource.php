<?php

namespace App\Filament\Admin\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Set;
use App\Models\Building;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Admin\Resources\CustomerResource\Pages;
use App\Filament\Admin\Resources\CustomerResource\RelationManagers;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $slug = 'customers';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'ho_va_ten';


    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getPluralModelLabel(): string
    {
        return 'Khách hàng';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    TextInput::make('ho_va_ten')
                        ->required()
                        ->label('Tên'),
                    TextInput::make('email')
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set, $state) {
                            $set('password', bcrypt('12345678'));
                        })
                        ->label('Email'),
                    Hidden::make('password'),
                    TextInput::make('so_dien_thoai')
                        ->required()
                        ->label('Điện thoại'),
                    Toggle::make('active')
                        ->default(true)
                        ->label('Theo dõi'),
                ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ho_va_ten')->label('Tên khách hàng')->sortable(),
                TextColumn::make('email')->label('Email')->sortable(),
                TextColumn::make('buildings.ten_toa_nha')->label('Chung cư')->hidden(),
                TextColumn::make('so_dien_thoai')->label('Số điện thoại')->sortable(),
                IconColumn::make('email_verified_at')->boolean()->label('Xác thực'),
                IconColumn::make('active')->boolean()->label('Hoạt động'),
            ])
            ->filters([
                SelectFilter::make('buildings')
                    ->query(function (Builder $query, array $data) {
                        if ($data['value']) {
                            $query->whereHas('buildings', function ($query) use ($data) {
                                $query->where('buildings.id', $data['value']);
                            });
                        }
                    })
                    ->getSearchResultsUsing(function (string $search) {
                        return Building::where('ten_toa_nha', 'LIKE', "%{$search}%")->limit(10)->pluck('ten_toa_nha', 'id');
                    })
                    ->getOptionLabelUsing(fn ($value): ?string => Building::find($value)?->ten_toa_nha)
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->hidden(fn (): bool => !can('customer.view')),
                Tables\Actions\EditAction::make()->hidden(fn (): bool => !can('customer.edit')),
                Tables\Actions\DeleteAction::make()->hidden(fn (): bool => !can('customer.delete')),
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'view' => Pages\ViewCustomer::route('/{record}'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
