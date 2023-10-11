<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Building;
use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $buildingCount = Building::count();
        $customerCount = Customer::count();
        return [
            Stat::make('Chung cư', $buildingCount),
            Stat::make('Khách hàng', $customerCount),
        ];
    }
}
