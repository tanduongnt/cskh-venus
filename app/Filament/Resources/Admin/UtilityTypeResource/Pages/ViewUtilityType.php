<?php

namespace App\Filament\Resources\Admin\UtilityTypeResource\Pages;

use App\Filament\Resources\Admin\UtilityTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUtilityType extends ViewRecord
{
    protected static string $resource = UtilityTypeResource::class;

    protected static ?string $title = 'Chi tiết';

    protected static ?string $breadcrumb = 'Chi tiết';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
