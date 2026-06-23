<?php

namespace App\Filament\Resources\ScheduleResource\Pages;

use App\Filament\Resources\ScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Traits\RedirectsToPageList;

class CreateSchedule extends CreateRecord
{
    use RedirectsToPageList;
    protected static string $resource = ScheduleResource::class;
}
