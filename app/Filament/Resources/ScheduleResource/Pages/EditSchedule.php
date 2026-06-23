<?php

namespace App\Filament\Resources\ScheduleResource\Pages;

use App\Filament\Resources\ScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Traits\RedirectsToPageList;

class EditSchedule extends EditRecord
{
    use RedirectsToPageList;
    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
