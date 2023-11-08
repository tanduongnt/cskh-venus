<?php

namespace App\Filament\Admin\Resources\BuildingResource\Pages;

use App\Filament\Admin\Resources\BuildingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBuilding extends ViewRecord
{
    protected static string $resource = BuildingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
