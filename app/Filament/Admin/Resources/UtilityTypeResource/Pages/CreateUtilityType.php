<?php

namespace App\Filament\Admin\Resources\UtilityTypeResource\Pages;

use App\Filament\Admin\Resources\UtilityTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUtilityType extends CreateRecord
{
    protected static string $resource = UtilityTypeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
