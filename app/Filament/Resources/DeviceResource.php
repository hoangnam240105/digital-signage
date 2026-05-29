<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceResource\Pages;
use App\Filament\Resources\DeviceResource\RelationManagers;
use App\Models\Device;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\SelectFilter;
class DeviceResource extends Resource
{
    protected static ?string $model = Device::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Thông tin thiết bị')
                    ->description('Nhập các thông số cơ bản của Android Box/TV')
                    ->schema([
                        TextInput::make('name')
                            ->label('Tên thiết bị')
                            ->required()
                            ->placeholder('Ví dụ: Box Tầng 1'),

                        TextInput::make('location')
                            ->label('Vị trí lắp đặt')
                            ->required()
                            ->placeholder('Ví dụ: Sảnh chính'),

                        TextInput::make('ip_address')
                            ->label('Địa chỉ IP')
                            ->ipv4() // Kiểm tra định dạng IP chuẩn
                            ->placeholder('192.168.1.10'),

                        Toggle::make('is_active')
                            ->label('Trạng thái hoạt động')
                            ->default(true),

                        DateTimePicker::make('last_connected_at')
                            ->label('Lần cuối kết nối')
                            ->disabled(), // Chỉ xem, không cho sửa bằng tay
                    ])->columns(2) // Chia làm 2 cột
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Tên')
                    ->searchable() // Cho phép tìm kiếm theo tên
                    ->sortable(),

                TextColumn::make('location')
                    ->label('Vị trí')
                    ->badge() // Hiện dạng nhãn cho đẹp
                    ->color('info'),

                TextColumn::make('ip_address')
                    ->label('Địa chỉ IP'),

                IconColumn::make('is_active')
                    ->label('Online')
                    ->boolean() // Hiện dấu tích xanh/đỏ
                    ->sortable(),

                TextColumn::make('last_connected_at')
                    ->label('Kết nối cuối')
                    ->dateTime('H:i d/m/Y') // Định dạng ngày giờ Việt Nam
                    ->sortable(),
            ])
            ->filters([
                // 1. Bộ lọc đúng/sai (Online/Offline)
                TernaryFilter::make('is_active')
                    ->label('Trạng thái hoạt động')
                    ->placeholder('Tất cả thiết bị')
                    ->trueLabel('Chỉ thiết bị Online')
                    ->falseLabel('Chỉ thiết bị Offline'),

                // 2. Bộ lọc theo vị trí (Lấy danh sách vị trí đang có trong DB)
                SelectFilter::make('location')
                    ->label('Lọc theo vị trí')
                    ->options([
                        'Phòng khách' => 'Phòng khách',
                        'Sảnh chính' => 'Sảnh chính',
                        'Tầng 1' => 'Tầng 1',
                    ])
                    ->searchable(), // Cho phép gõ tìm kiếm trong danh sách lọc
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDevices::route('/'),
            'create' => Pages\CreateDevice::route('/create'),
            'edit' => Pages\EditDevice::route('/{record}/edit'),
        ];
    }
}
