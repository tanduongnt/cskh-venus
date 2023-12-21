<?php

namespace App\Filament\Admin\Resources\BuildingResource\Pages;

use App\Filament\Admin\Resources\BuildingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBuildings extends ListRecords
{
    protected static string $resource = BuildingResource::class;


    protected static ?string $title = 'Chung cư';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Thêm mới'),
        ];
    }
}
