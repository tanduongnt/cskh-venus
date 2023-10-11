<?php

namespace App\Filament\Customer\Pages;

use App\Models\Utility as ModelsUtility;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Utility extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.customer.pages.utility';

    protected static ?string $title = 'Đăng ký tiện ích';

    protected static ?string $slug = 'utilities';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public $utilities;
    public $block;

    public function mount()
    {
        $this->utilities = ModelsUtility::withWhereHas('building', function ($query) {
            $query->whereHas('apartments', function ($query) {
                $query->whereHas('customers', function ($query) {
                    $query->where('customer_id', Auth::id());
                });
            });
        })->get();

        foreach ($this->utilities as $key => $utility) {
            $this->block = $utility->block;
        }
        //dd($this->utilities);
    }
}
