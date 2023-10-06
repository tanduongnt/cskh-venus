<?php

namespace App\Filament\Resources\Admin\UtilityResource\Pages;

use App\Filament\Resources\Admin\UtilityResource;
use Filament\Resources\Pages\Page;

class BlockUtility extends Page
{
    protected static string $resource = UtilityResource::class;


    protected static ?string $title = 'block';

    protected static ?string $breadcrumb = 'block';

    protected static string $view = 'filament.resources.utility-resource.pages.block-utility';
}
