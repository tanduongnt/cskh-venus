<?php

namespace App\Filament\Customer\Resources\InvoiceableResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Extend\Filament\ResourcePages\NestedPage;
use App\Filament\Customer\Resources\InvoiceableResource;

class ViewInvoiceable extends ViewRecord
{
    use NestedPage;

    protected static string $resource = InvoiceableResource::class;

    protected static ?string $title = 'Chi tiết';

    protected static ?string $breadcrumb = 'Chi tiết';

    protected function getHeaderActions(): array
    {
        return [
            //Actions\EditAction::make(),
        ];
    }
}
