<?php

namespace App\Filament\Admin\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Admin\Resources\CustomerResource\Pages;
use App\Filament\Admin\Resources\CustomerResource\RelationManagers;
use App\Models\Building;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'customers';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 3;


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
                    TextInput::make('name')
                        ->required()
                        ->label('Tên')
                        ->columnSpan('full'),
                    TextInput::make('email')
                        ->required()
                        ->label('Email')
                        ->columnSpan('full'),
                    TextInput::make('phone')
                        ->required()
                        ->label('Điện thoại'),
                    Toggle::make('active')->label('Theo dõi'),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Tên khách hàng')->sortable()->searchable(),
                TextColumn::make('email')->label('Email')->sortable(),
                TextColumn::make('apartments.building.name')->label('Chung cư')->sortable(),
                TextColumn::make('phone')->label('Điện thoại')->sortable(),
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
                        return Building::where('name', 'LIKE', "%{$search}%")->limit(10)->pluck('name', 'id');
                    })
                    ->getOptionLabelUsing(fn ($value): ?string => Building::find($value)?->name)
                    ->searchable(),
            ])
            ->actions([
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\ViewCustomer::route('/{record}'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
