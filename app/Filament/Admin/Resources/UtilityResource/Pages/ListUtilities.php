<?php

namespace App\Filament\Admin\Resources\UtilityResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Admin\Resources\UtilityResource;
use App\Extend\Filament\ResourcePages\NestedPage;

class ListUtilities extends ListRecords
{
    use NestedPage;

    protected static string $resource = UtilityResource::class;

    protected static ?string $title = 'Tiện ích';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->createAnother(false)->label('Thêm mới'),
        ];
    }
}
