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
        // 1. Đếm số lượng thiết bị
        $disconnectTime = now()->subMinutes(5);

        // Tự động đếm dựa vào thời gian updated_at thật trong DB
        $onlineDevices = Device::where('updated_at', '>=', $disconnectTime)->count();
        $offlineDevices = Device::where('updated_at', '<', $disconnectTime)->count();

        // 2. Đếm tổng số file trong kho Media
        $totalMedia = Media::count();

        return [
            Stat::make('Thiết bị trực tuyến', $onlineDevices)
                ->description('Hoạt động ổn định')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Thiết bị ngoại tuyến', $offlineDevices)
                ->description($offlineDevices > 0 ? 'Cần bảo trì ngay' : 'Hệ thống an toàn')
                ->descriptionIcon($offlineDevices > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($offlineDevices > 0 ? 'danger' : 'success'),

            Stat::make('Kho Media', $totalMedia)
                ->description('Tổng số video/ảnh mẫu')
                ->descriptionIcon('heroicon-m-film')
                ->color('info'),
            
        ];
    }
}
