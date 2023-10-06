<?php

namespace App\Filament\Resources\Admin\UtilityResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\Admin\UtilityResource;
use App\Extend\Filament\ResourcePages\NestedPage;

class ViewUtility extends ViewRecord
{
    use NestedPage;

    protected static string $resource = UtilityResource::class;

    protected static ?string $title = 'Chi tiết';

    protected static ?string $breadcrumb = 'Chi tiết';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
