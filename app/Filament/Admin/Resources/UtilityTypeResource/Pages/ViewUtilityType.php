<?php

namespace App\Filament\Admin\Resources\UtilityTypeResource\Pages;

use App\Filament\Admin\Resources\UtilityTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUtilityType extends ViewRecord
{
    protected static string $resource = UtilityTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\EditAction::make(),
        ];
    }
}
