<?php

namespace App\Filament\Customer\Resources\InvoiceableResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Extend\Filament\ResourcePages\NestedPage;
use App\Filament\Customer\Resources\InvoiceableResource;

class EditInvoiceable extends EditRecord
{
    use NestedPage;
    protected static string $resource = InvoiceableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
