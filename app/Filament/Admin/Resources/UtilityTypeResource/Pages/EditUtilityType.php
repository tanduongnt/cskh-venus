<?php

namespace App\Filament\Admin\Resources\UtilityTypeResource\Pages;

use App\Filament\Admin\Resources\UtilityTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUtilityType extends EditRecord
{
    protected static string $resource = UtilityTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
