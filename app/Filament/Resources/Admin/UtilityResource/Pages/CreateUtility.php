<?php

namespace App\Filament\Resources\Admin\UtilityResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Admin\UtilityResource;
use App\Extend\Filament\ResourcePages\NestedPage;

class CreateUtility extends CreateRecord
{
    use NestedPage;

    protected static string $resource = UtilityResource::class;

    protected static ?string $title = 'Thêm mới';

    protected static ?string $breadcrumb = 'Thêm mới';
}
