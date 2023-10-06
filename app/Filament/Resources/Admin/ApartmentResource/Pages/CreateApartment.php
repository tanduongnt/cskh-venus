<?php

namespace App\Filament\Resources\Admin\ApartmentResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Admin\ApartmentResource;
use App\Extend\Filament\ResourcePages\NestedPage;

class CreateApartment extends CreateRecord
{
    use NestedPage;

    protected static string $resource = ApartmentResource::class;
}
