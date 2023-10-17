<?php

namespace App\Filament\Customer\Pages;

use Filament\Pages\Page;

class ViewUtilityRegistration extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.customer.pages.view-utility-registration';

    protected static ?string $title = 'Tiện ích đã đăng ký';

    protected static ?string $slug = 'utilities/view';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
