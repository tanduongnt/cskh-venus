<?php

namespace App\Filament\Resources\ApartmentResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\ApartmentResource;
use App\Extend\Filament\ResourcePages\NestedPage;

class ViewApartment extends ViewRecord
{
    use NestedPage;

    protected static string $resource = ApartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}