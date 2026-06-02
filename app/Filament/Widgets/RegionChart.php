<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class RegionChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        return [
            'datasets' => [
            [
                'label' => 'Thiết bị mới',
                'data' => [0, 10, 5, 2, 21, 32, 45], // Đây là nơi đổ dữ liệu thật từ DB vào
                'backgroundColor' => '#36A2EB',
                'borderColor' => '#9BD0F5',
            ],
        ],
        'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
