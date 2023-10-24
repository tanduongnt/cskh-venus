<?php

namespace App\Filament\Customer\Resources\InvoiceResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Extend\Filament\ResourcePages\NestedPage;
use App\Filament\Customer\Resources\InvoiceResource;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected static ?string $title = 'Danh sách';

    protected static ?string $breadcrumb = 'Danh sách';

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
