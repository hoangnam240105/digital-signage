<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Device;
class DeviceStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
        Stat::make('Thiết bị hoạt động', Device::query()->where('is_active', true)->count())
            ->description('Máy đang online')
            ->descriptionIcon('heroicon-m-arrow-trending-up') // Thêm icon cho đẹp
            ->color('success'), 

        Stat::make('Thiết bị hoạt động', Device::query()->where('is_active', false)->count())
            ->description('Máy mất kết nối')
            ->descriptionIcon('heroicon-m-arrow-trending-down')
            ->color('danger'),
    ];
    }
}
