<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Filament\Resources\Pages\CreateRecord;
use App\Traits\RedirectsToPageList;

class CreateMedia extends CreateRecord
{
    use RedirectsToPageList;
    protected static string $resource = MediaResource::class;
}
