<?php

namespace App\Filament\Admin\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Utility;
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

    protected static ?string $recordTitleAttribute = 'name';

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
                        ->relationship(name: 'utilityType', titleAttribute: 'name')
                        ->required()
                        ->label('Loại tiện ích')
                        ->columnSpan('full'),
                    TextInput::make('name')
                        ->required()
                        ->label('Tên tiện ích')
                        ->columnSpan('full'),
                    TimePicker::make('start_time')
                        ->required()
                        ->native(false)
                        ->displayFormat('H:i:s'),
                    TimePicker::make('end_time')
                        ->required()
                        ->native(false)
                        ->displayFormat('H:i:s'),
                    TextInput::make('block')
                        ->numeric()
                        ->required()
                        ->label('Block (phút)'),
                    TextInput::make('sort')
                        ->nullable()
                        ->numeric()
                        ->label('Sắp xếp'),
                    RichEditor::make('description')
                        ->nullable()
                        ->label('Mô tả')
                        ->columnSpan('full'),
                    Toggle::make('active')->label('Theo dõi'),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Tên tiện ích')->sortable()->searchable(),
                TextColumn::make('start_time')->label('Giờ hoạt động')->sortable(),
                TextColumn::make('end_time')->label('Giờ kết thúc')->sortable(),
                IconColumn::make('active')->boolean()->label('hoạt động'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('blocks')
                    ->url(fn (Utility $record): string => UtilityResource::getUrl('block', ['record' => $record->id]))
                    ->openUrlInNewTab()
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
            'index' => Pages\ListUtilities::route('/'),
            'create' => Pages\CreateUtility::route('/create'),
            'view' => Pages\ViewUtility::route('/{record}'),
            'edit' => Pages\EditUtility::route('/{record}/edit'),
        ];
    }
}
