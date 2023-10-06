<?php

namespace App\Filament\Resources\Admin\ApartmentResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Admin\ApartmentResource;
use App\Extend\Filament\ResourcePages\NestedPage;

class EditApartment extends EditRecord
{
    use NestedPage;

    protected static string $resource = ApartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
