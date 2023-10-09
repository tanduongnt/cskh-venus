<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ApartmentCustomerRole: string implements HasLabel
{
    case OWNER = 'owner';
    case MEMBER = 'member';

    public function getLabel(): ?string
    {

        return match ($this) {
            self::OWNER => 'Chủ hộ',
            self::MEMBER => 'Thành viên',
        };
    }
}
