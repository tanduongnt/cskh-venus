<?php

namespace App\Filament\Resources\Admin\UtilityResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\Admin\UtilityResource;
use App\Extend\Filament\ResourcePages\NestedPage;

class ListUtilities extends ListRecords
{
    use NestedPage;

    protected static string $resource = UtilityResource::class;

    protected static ?string $title = 'Danh sách';

    protected static ?string $breadcrumb = 'Danh sách';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('thêm mới'),
        ];
    }
}
