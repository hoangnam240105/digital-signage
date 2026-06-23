<?php

namespace App\Filament\Resources\AddressResource\Pages;

use App\Filament\Resources\AddressResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Traits\RedirectsToPageList;

class CreateAddress extends CreateRecord
{
    use RedirectsToPageList;
    protected static string $resource = AddressResource::class;
}
