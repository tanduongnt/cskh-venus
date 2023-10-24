<?php

namespace App\Filament\Customer\Resources\InvoiceableResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Extend\Filament\ResourcePages\NestedPage;
use App\Filament\Customer\Resources\InvoiceableResource;

class CreateInvoiceable extends CreateRecord
{
    use NestedPage;
    protected static string $resource = InvoiceableResource::class;
}
