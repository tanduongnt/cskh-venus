<?php

namespace App\Filament\Customer\Resources\InvoiceResource\Pages;

use App\Filament\Customer\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected static ?string $title = 'Chi tiết';

    protected static ?string $breadcrumb = 'Chi tiết';

    protected function getHeaderActions(): array
    {
        return [
            //Actions\EditAction::make(),
        ];
    }
}
