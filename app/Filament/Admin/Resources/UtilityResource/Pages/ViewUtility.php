<?php

namespace App\Filament\Admin\Resources\UtilityResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Admin\Resources\UtilityResource;
use App\Extend\Filament\ResourcePages\NestedPage;

class ViewUtility extends ViewRecord
{
    use NestedPage;

    protected static string $resource = UtilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\EditAction::make(),
        ];
    }
}
