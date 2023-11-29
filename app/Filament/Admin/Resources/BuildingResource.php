<?php

namespace App\Filament\Admin\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Building;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\ActionsPosition;
use App\Filament\Admin\Resources\UtilityResource;
use App\Extend\Filament\Columns\ChildResourceLink;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Admin\Resources\BuildingResource\Pages;
use App\Extend\Filament\Table\Actions\LinkToChildrenAction;
use App\Filament\Admin\Resources\BuildingResource\RelationManagers;

class BuildingResource extends Resource
{
    protected static ?string $model = Building::class;

    protected static ?string $recordTitleAttribute = 'ten_toa_nha';

    protected static ?string $slug = 'buildings';

    protected static ?int $navigationSort = 1;


    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getPluralModelLabel(): string
    {
        return 'Chung cư';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    TextInput::make('ten_toa_nha')
                        ->required()
                        ->label('Tên chung cư')
                        ->columnSpan(['md' => 2]),
                    TextInput::make('sap_xep')
                        ->nullable()
                        ->numeric()
                        ->label('Sắp xếp'),
                    TextInput::make('phi_quan_ly')
                        ->nullable()
                        ->numeric()
                        ->label('Phí quản lý')
                        ->helperText('Tính theo mỗi m²'),
                    TextInput::make('thue_vat')
                        ->nullable()
                        ->numeric()
                        ->label('Thuế VAT')
                        ->helperText('Phần trăm khi xuất hóa đơn VAT'),
                    TextInput::make('so_luong_uy_quyen')
                        ->nullable()
                        ->numeric()
                        ->label('Ủy quyền tối đa')
                        ->helperText('Số lượng người được chủ nhà ủy quyền'),
                    Toggle::make('active')
                        ->default(true)
                        ->label('Hoạt động')
                        ->columnSpan('full'),
                ])
                    ->columns(['md' => 3]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ten_toa_nha')->label('Tên tòa nhà')->sortable()->searchable(),
                TextColumn::make('apartments_count')->counts('apartments')->label('Căn hộ')->sortable(),
                TextColumn::make('utilities_count')->counts('utilities')->label('Tiện ích')->sortable(),
                IconColumn::make('active')->boolean()->label('Vận hành'),
                //ChildResourceLink::make(UtilityResource::class)->label('Hệ thống'),
            ])
            ->emptyStateHeading('Chưa có dữ liệu')
            ->filters([
                //
            ])
            ->actions([
                //LinkToChildrenAction::make(UtilityResource::class)->forChildResource(UtilityResource::class)->label('Hệ thống'),
                Action::make('apartments')
                    ->url(fn (Building $record): string => ApartmentResource::getUrl('index', ['building' => $record->id]))
                    ->openUrlInNewTab()
                    ->label('Căn hộ')
                    ->icon('heroicon-s-home')
                    ->color('success'),
                Action::make('utilities')
                    ->url(fn (Building $record): string => UtilityResource::getUrl('index', ['building' => $record]))
                    ->openUrlInNewTab()
                    ->label('Tiện ích')
                    ->icon('bi-columns-gap')
                    ->color('info'),
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()->hidden(fn (): bool => !can('building.view')),
                    Tables\Actions\EditAction::make()->hidden(fn (): bool => !can('building.edit')),
                    Tables\Actions\DeleteAction::make()->hidden(fn (): bool => !can('building.delete')),
                ]),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListBuildings::route('/'),
            'create' => Pages\CreateBuilding::route('/create'),
            'view' => Pages\ViewBuilding::route('/{record}'),
            'edit' => Pages\EditBuilding::route('/{record}/edit'),
        ];
    }
}
