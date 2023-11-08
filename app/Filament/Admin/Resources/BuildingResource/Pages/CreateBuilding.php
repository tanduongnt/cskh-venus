<?php

namespace App\Filament\Admin\Resources\BuildingResource\Pages;

use App\Filament\Admin\Resources\BuildingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBuilding extends CreateRecord
{
    protected static string $resource = BuildingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
