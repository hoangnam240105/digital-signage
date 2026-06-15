<?php

namespace App\Filament\Widgets;

use App\Models\Device;
use App\Models\Address;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class DeviceTable extends BaseWidget
{
    protected static ?int $sort = 2;
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

            Tables\Columns\IconColumn::make('is_active')
                ->label('Kết nối')
                ->boolean()
                ->trueColor('success')
                ->falseColor('danger'),

            Tables\Columns\TextColumn::make('updated_at')
                ->label('Cập nhật cuối')
                ->dateTime('H:i d/m')
                ->description(fn (Device $record): string => $record->updated_at->diffForHumans()),
            ]);
    }
}
