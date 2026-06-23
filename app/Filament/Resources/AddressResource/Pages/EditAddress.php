<?php

namespace App\Filament\Resources\AddressResource\Pages;

use App\Filament\Resources\AddressResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Traits\RedirectsToPageList;

class EditAddress extends EditRecord
{
    use RedirectsToPageList;
    protected static string $resource = AddressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
