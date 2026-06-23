<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Traits\RedirectsToPageList;

class EditMedia extends EditRecord
{
    use RedirectsToPageList;
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
