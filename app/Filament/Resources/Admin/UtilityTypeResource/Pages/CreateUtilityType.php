<?php

namespace App\Filament\Resources\Admin\UtilityTypeResource\Pages;

use App\Filament\Resources\Admin\UtilityTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUtilityType extends CreateRecord
{
    protected static string $resource = UtilityTypeResource::class;

    protected static ?string $title = 'Thêm mới';

    protected static ?string $breadcrumb = 'Thêm mới';
}
