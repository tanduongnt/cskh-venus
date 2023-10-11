<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Facades\Filament;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $navigationIcon = 'bi-slack';

    public static function getNavigationLabel(): string
    {
        return 'Tổng quan';
    }
}
