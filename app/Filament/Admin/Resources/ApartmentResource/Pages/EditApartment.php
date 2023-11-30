<?php

namespace App\Filament\Admin\Resources\ApartmentResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Extend\Filament\ResourcePages\NestedPage;
use App\Filament\Admin\Resources\ApartmentResource;

class EditApartment extends EditRecord
{
    use NestedPage;

    protected static string $resource = ApartmentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
