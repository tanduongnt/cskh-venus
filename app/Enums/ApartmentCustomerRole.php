<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ApartmentCustomerRole: string implements HasLabel
{
    case OWNER = 'Owner';
    case MEMBER = 'Member';

    public function getLabel(): ?string
    {

        return match ($this) {
            self::OWNER => 'Chủ hộ',
            self::MEMBER => 'Thành viên',
        };
    }
}
