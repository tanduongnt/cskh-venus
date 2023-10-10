<?php

namespace App\Filament\Admin\Resources\CustomerResource\Pages;

use App\Filament\Admin\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    protected static ?string $title = 'Chi tiết';

    protected static ?string $breadcrumb = 'Chi tiết';
}
