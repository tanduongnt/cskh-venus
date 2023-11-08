<?php

namespace App\Filament\Admin\Resources\ApartmentResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Admin\Resources\ApartmentResource;
use App\Extend\Filament\ResourcePages\NestedPage;

class ListApartments extends ListRecords
{
    use NestedPage;

    protected static string $resource = ApartmentResource::class;

    protected static ?string $title = 'Danh sách';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Thêm mới'),
        ];
    }
}
