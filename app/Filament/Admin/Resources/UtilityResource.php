<?php

namespace App\Filament\Admin\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Utility;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use App\Extend\Filament\NestedResource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TimePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Admin\Resources\UtilityResource\Pages;
use App\Filament\Admin\Resources\UtilityResource\RelationManagers;

class UtilityResource extends NestedResource
{
    protected static ?string $model = Utility::class;

    protected static ?string $recordTitleAttribute = 'ten_tien_ich';

    protected static ?string $slug = 'utilities';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 3;

    public static function getPluralModelLabel(): string
    {
        return 'Tiện ích';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getParent(): string
    {
        return BuildingResource::class;
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    Hidden::make('building_id'),
                    Select::make('utility_type_id')
                        ->relationship(name: 'utilityType', titleAttribute: 'ten_loai_tien_ich')
                        ->required()
                        ->label('Loại tiện ích')
                        ->columnSpan(['md' => 'full'])
                        ->native(false),
                    TextInput::make('ten_tien_ich')
                        ->required()
                        ->label('Tên tiện ích')
                        ->columnSpan(['md' => 2]),
                    TextInput::make('sap_xep')
                        ->nullable()
                        ->numeric()
                        ->label('Sắp xếp'),
                    Toggle::make('cho_phep_dang_ky')
                        ->default(true)
                        ->label('Đăng ký để sử dụng')
                        ->live()
                        ->columnSpan('full'),
                    Section::make()->schema([
                        TimePicker::make('gio_bat_dau')
                            ->default('00:00')
                            ->required()
                            ->native(false)
                            ->seconds(false)
                            ->minutesStep(30)
                            ->displayFormat('H:i')
                            ->label('Bắt đầu'),
                        TimePicker::make('gio_ket_thuc')
                            ->after('gio_bat_dau')
                            ->default('23:59')
                            ->required()
                            ->seconds(false)
                            ->native(false)
                            ->displayFormat('H:i')
                            ->label('Kết thúc'),
                        TextInput::make('block')
                            ->numeric()
                            ->default(60)
                            ->label('Thời gian mỗi block (phút)'),
                        TimePicker::make('gio_bat_dau_tinh_tien')
                            ->afterOrEqual('gio_bat_dau')
                            ->native(false)
                            ->seconds(false)
                            ->minutesStep(30)
                            ->displayFormat('H:i')
                            ->label('Bắt đầu tính tiền'),
                        TimePicker::make('gio_ket_thuc_tinh_tien')
                            ->beforeOrEqual('gio_ket_thuc')
                            ->after('gio_bat_dau_tinh_tien')
                            ->native(false)
                            ->seconds(false)
                            ->minutesStep(30)
                            ->displayFormat('H:i')
                            ->label('Kết thúc tính tiền'),
                        TextInput::make('don_gia')
                            ->numeric()
                            ->default(0)
                            ->label('Giá tiền mỗi block'),
                        TextInput::make('gioi_han')
                            ->default(0)
                            ->numeric()
                            ->label('Giới hạn'),
                    ])
                        ->hidden(fn (Get $get): bool => !$get('cho_phep_dang_ky'))
                        ->columns(['md' => 3]),
                    RichEditor::make('mo_ta')
                        ->nullable()
                        ->label('Mô tả')
                        ->columnSpan(['md' => 'full']),
                    Toggle::make('active')
                        ->default(true)
                        ->label('Theo dõi'),
                ])->columns(['md' => 3]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ten_tien_ich')->label('Tên tiện ích')->sortable()->searchable(),
                TextColumn::make('gio_bat_dau')->label('Giờ bắt đầu')->sortable(),
                TextColumn::make('gio_ket_thuc')->label('Giờ kết thúc')->sortable(),
                IconColumn::make('active')->boolean()->label('hoạt động'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->hidden(fn (): bool => !can('utility.view')),
                Tables\Actions\EditAction::make()->hidden(fn (): bool => !can('utility.edit')),
                Tables\Actions\DeleteAction::make()->hidden(fn (): bool => !can('utility.delete')),
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
            RelationManagers\SurchargesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUtilities::route('/'),
            'create' => Pages\CreateUtility::route('/create'),
            'view' => Pages\ViewUtility::route('/{record}'),
            'edit' => Pages\EditUtility::route('/{record}/edit'),
        ];
    }
}
