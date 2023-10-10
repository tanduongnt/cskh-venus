<?php

namespace App\Filament\Dashboard\Pages;

use Filament\Pages\Page;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $navigationIcon = 'bi-slack';

    protected static string $view = 'filament-panels::pages.dashboard';
}
