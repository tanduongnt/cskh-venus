<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum InvoiceableType: string implements HasLabel
{
    case UTILITY = 'App\Models\Utility';
    case SURCHARGE = 'App\Models\Surcharge';

    public function getLabel(): ?string
    {

        return match ($this) {
            self::UTILITY => 'Tiện ích',
            self::SURCHARGE => 'Phụ thu',
        };
    }
}
