<?php

namespace App\Filament\Resources\Admin\UtilityResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Admin\UtilityResource;
use App\Extend\Filament\ResourcePages\NestedPage;

class EditUtility extends EditRecord
{
    use NestedPage;

    protected static string $resource = UtilityResource::class;

    protected static ?string $title = 'Cập nhật';

    protected static ?string $breadcrumb = 'Cập nhật';

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
