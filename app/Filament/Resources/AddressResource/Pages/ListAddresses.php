<?php

namespace App\Filament\Resources\AddressResource\Pages;

use App\Filament\Resources\AddressResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAddresses extends ListRecords
{
    protected static string $resource = AddressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Cấu hình nút tạo mới thành Popup Modal
            Actions\CreateAction::make()
                ->label('Thêm địa điểm')
                ->modalHeading('Tạo địa điểm mới')
                ->modalWidth('md'),
        ];
    }
}
