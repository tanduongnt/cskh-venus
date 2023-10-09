<?php

namespace App\Filament\Admin\Resources\UtilityResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Admin\Resources\UtilityResource;
use App\Extend\Filament\ResourcePages\NestedPage;

class CreateUtility extends CreateRecord
{
    use NestedPage;

    protected static string $resource = UtilityResource::class;

    protected static ?string $title = 'Thêm mới';

    protected static ?string $breadcrumb = 'Thêm mới';
}
