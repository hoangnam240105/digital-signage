<?php

namespace App\Filament\Widgets;

use App\Models\Device;
use App\Models\Media;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected function getStats(): array
    {
        return [
            Stat::make('Thiết bị trực tuyến', Device::query()->where('is_active', true)->count())
            ->description('Hoạt động ổn định')
            ->descriptionIcon('heroicon-m-check-badge')
            ->color('success')
            ->chart([7, 2, 10, 3, 15, 4, 17]),

        Stat::make('Thiết bị ngoại tuyến', Device::query()->where('is_active', false)->count())
            ->description('Cần bảo trì ngay')
            ->descriptionIcon('heroicon-m-exclamation-triangle')
            ->color('danger'),

        Stat::make('Kho Media', Media::all()->count())
            ->description('Tổng số video/ảnh mẫu')
            ->descriptionIcon('heroicon-m-film')
            ->color('info'),
        ];
    }
}
