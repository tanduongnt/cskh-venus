<?php

namespace App\Filament\Resources\Admin\BuildingResource\Pages;

use App\Filament\Resources\Admin\BuildingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBuildings extends ListRecords
{
    protected static string $resource = BuildingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Thêm mới'),
        ];
    }
}
