<?php

namespace App\Filament\Resources\Admin\UtilityTypeResource\Pages;

use App\Filament\Resources\Admin\UtilityTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUtilityTypes extends ListRecords
{
    protected static string $resource = UtilityTypeResource::class;

    protected static ?string $title = 'Danh sách ';

    protected static ?string $breadcrumb = 'Danh sách';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Thêm mới'),
        ];
    }
}
