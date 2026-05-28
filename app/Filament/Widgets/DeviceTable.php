<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use app\models\User;
class DeviceTable extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Tạm thời lấy bảng User để hiển thị dữ liệu mẫu
                User::query() 
            )
            ->columns([
                // Định nghĩa các cột muốn hiện ra
                Tables\Columns\TextColumn::make('name')->label('Tên thiết bị'),
                Tables\Columns\TextColumn::make('email')->label('Địa điểm (Email)'),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày tạo')->dateTime(),
            ]);
    }
}
