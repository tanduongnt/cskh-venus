<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'Danh sách';

    protected static ?string $breadcrumb = 'Danh sách';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Thêm mới'),
        ];
    }
}
