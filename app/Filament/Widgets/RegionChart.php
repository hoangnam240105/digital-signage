<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Device;
use Carbon\Carbon;

class RegionChart extends ChartWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $labels = [];
        $dataReal = [];
        $monthsKeys = [];

        // 1. Định nghĩa thời điểm bắt đầu dự án (Tháng 5 năm 2026)
        $startDate = Carbon::create(2026, 5, 1)->startOfMonth();
        $currentDate = now()->startOfMonth();

        // 2. Tạo danh sách các tháng chạy từ Tháng 5 đến Tháng hiện tại
        while ($startDate->lessThanOrEqualTo($currentDate)) {
            $labels[] = $startDate->format('M');
            $monthsKeys[$startDate->format('Y-m')] = 0; // Khởi tạo key '2026-05'

            $startDate->addMonth(); // Tăng thêm 1 tháng cho vòng lặp
        }
        
        // 3. Lấy dữ liệu thật từ DB (Lấy ngày tạo của các thiết bị từ Tháng 5/2026 đổ đi)
        $devicesByMonth = Device::query()
            ->select('created_at')
            ->where('created_at', '>=', Carbon::create(2026, 5, 1)->startOfMonth())
            ->get() // Lấy toàn bộ dữ liệu về dạng Collection của Laravel
            ->groupBy(function ($device) {
                // Dùng PHP để định dạng 'Y-m' thay vì dùng SQL, cực kỳ an toàn
                return Carbon::parse($device->created_at)->format('Y-m');
            })
            ->map(function ($group) {
                // Đếm số lượng phần tử trong mỗi nhóm tháng
                return $group->count();
            })
            ->toArray();

        // 4. Trộn dữ liệu thật vào danh sách tháng đã lọc
        foreach ($monthsKeys as $yearMonth => $value) {
            $dataReal[] = $devicesByMonth[$yearMonth] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Thiết bị mới',
                    'data' => $dataReal,
                    'backgroundColor' => '#f97316',
                    'borderColor' => '#ea580c',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false, // Ẩn cái chữ "Thiết bị mới" ở dưới cùng đi cho đỡ rối mắt
                ],
            ]
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
