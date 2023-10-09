<?php

namespace App\Filament\Admin\Resources\ApartmentResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Admin\Resources\ApartmentResource;
use App\Extend\Filament\ResourcePages\NestedPage;

class CreateApartment extends CreateRecord
{
    use NestedPage;

    protected static string $resource = ApartmentResource::class;
}
