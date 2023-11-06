<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;

class RegistrationUtility extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.admin.pages.registration-utility';

    protected static ?string $title = 'Đăng ký tiện ích';

    protected static ?string $slug = 'registration-utility';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
