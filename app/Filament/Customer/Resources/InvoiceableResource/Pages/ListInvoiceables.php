<?php

namespace App\Filament\Customer\Resources\InvoiceableResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Extend\Filament\ResourcePages\NestedPage;
use App\Filament\Customer\Resources\InvoiceableResource;

class ListInvoiceables extends ListRecords
{
    use NestedPage;
    protected static string $resource = InvoiceableResource::class;

    protected static ?string $title = 'Danh sách';

    protected static ?string $breadcrumb = 'Danh sách';

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
