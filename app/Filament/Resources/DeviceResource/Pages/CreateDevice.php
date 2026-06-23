<?php

namespace App\Filament\Resources\DeviceResource\Pages;

use App\Filament\Resources\DeviceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Traits\RedirectsToPageList;

class CreateDevice extends CreateRecord
{
    use RedirectsToPageList;
    protected static string $resource = DeviceResource::class;
}
