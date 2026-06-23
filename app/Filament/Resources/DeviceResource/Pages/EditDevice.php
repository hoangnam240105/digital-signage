<?php

namespace App\Filament\Resources\DeviceResource\Pages;

use App\Filament\Resources\DeviceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Traits\RedirectsToPageList;

class EditDevice extends EditRecord
{
    use RedirectsToPageList;
    protected static string $resource = DeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
