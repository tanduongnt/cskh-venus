<?php

namespace App\Filament\Admin\Resources\UtilityTypeResource\Pages;

use App\Filament\Admin\Resources\UtilityTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUtilityTypes extends ListRecords
{
    protected static string $resource = UtilityTypeResource::class;

    protected static ?string $title = 'Loại tiện ích';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Thêm mới'),
        ];
    }
}
