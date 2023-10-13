<?php

namespace App\Filament\Admin\Resources\UtilityResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Admin\Resources\UtilityResource;
use App\Extend\Filament\ResourcePages\NestedPage;

class EditUtility extends EditRecord
{
    use NestedPage;

    protected static string $resource = UtilityResource::class;

    protected static ?string $title = 'Cập nhật';

    protected static ?string $breadcrumb = 'Cập nhật';

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
