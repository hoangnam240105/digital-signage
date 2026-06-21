<?php

namespace App\Filament\Widgets;

use App\Models\Device;
use App\Models\Address;
use Filament\Tables;
use Filament\Tables\Table;
use Carbon\Carbon;
use Filament\Widgets\TableWidget as BaseWidget;

class DeviceTable extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    public function table(Table $table): Table
    {
        return $table
            ->query(Device::query()->latest())
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên màn hình')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('Address.name')
                    ->sortable()
                    ->label('Vị trí'),

            Tables\Columns\TextColumn::make('schedule.name') // 'schedule' là tên hàm relationship trong Model Device, 'name' là tên cột ở bảng schedules
                ->label('Nội dung đang phát')
                ->default('Chưa gán lịch chiếu')
                ->badge()
                ->color(fn($state) => $state === 'Chưa gán lịch chiếu' ? 'gray' : 'success'),


                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Cập nhật cuối')
                    ->dateTime('H:i d/m')
                    ->description(fn(Device $record): string => $record->updated_at->diffForHumans()),
            ]);
    }
}
