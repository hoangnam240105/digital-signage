<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceResource\Pages;
use App\Models\Device;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class DeviceResource extends Resource
{
    protected static ?string $model = Device::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationLabel = 'Quản lý Android Box';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin quyền Admin')
                    ->description('Admin được phép thay đổi tên gọi nhớ và vị trí lắp đặt của Android Box này.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Tên gợi nhớ của Box')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('address_id')
                            ->label('Vị trí lắp đặt')
                            ->relationship('address', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])->columns(2),

                Forms\Components\Section::make('Thông tin kỹ thuật (Hệ thống tự quản lý)')
                    ->description('Các thông tin định danh và kết nối do thiết bị tự động đồng bộ về.')
                    ->schema([
                        Forms\Components\TextInput::make('device_code')
                            ->label('Mã định danh Box (Device Code)')
                            ->disabled(),

                        Forms\Components\TextInput::make('ip_address')
                            ->label('Địa chỉ IP hiện tại')
                            ->disabled(),

                        Forms\Components\TextInput::make('status')
                            ->label('Trạng thái phê duyệt')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('last_connected_at')
                            ->label('Thời gian tương tác cuối')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Thông tin Bảo mật & Pairing')
                    ->description('Mã bảo mật dùng để xác thực quyền truy cập API của Android Box.')
                    ->schema([
                        Forms\Components\TextInput::make('pairing_code')
                            ->label('Mã kết nối (Pairing Code)')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('pairing_expires_at')
                            ->label('Thời gian hết hạn mã Pairing')
                            ->disabled(),

                        Forms\Components\TextInput::make('device_token')
                            ->label('Mã Token xác thực (Device Token)')
                            ->password()
                            ->revealable()
                            ->disabled(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Tên Box')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('device_code')
                    ->label('Mã định danh Box')
                    ->searchable(),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('Địa chỉ IP')
                    ->default('N/A'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        default => 'gray',
                    }),

                // CỘT KIỂM TRA ONLINE / OFFLINE THỜI GIAN THỰC
                Tables\Columns\TextColumn::make('online_status')
                    ->label('Kết nối')
                    ->badge()
                    ->state(function (Device $record): string {
                        if ($record->status !== 'active') {
                            return 'Chưa kích hoạt';
                        }
                        if ($record->last_connected_at && $record->last_connected_at->diffInMinutes(now()) <= 5) {
                            return 'Online';
                        }
                        return 'Offline';
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'Online' => 'success',
                        'Offline' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('address.name')
                    ->label('Vị trí đặt Box')
                    ->default('Chưa gán')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pairing_code')
                    ->label('Mã Pairing')
                    ->badge()
                    ->color('warning')
                    ->placeholder('Đã kết nối'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Trạng thái phê duyệt')
                    ->options([
                        'pending' => 'Chờ duyệt',
                        'active' => 'Đang hoạt động',
                    ]),

                // BỘ LỌC THEO VỊ TRÍ LẮP ĐẶT
                Tables\Filters\SelectFilter::make('address_id')
                    ->label('Lọc theo vị trí')
                    ->relationship('address', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('activate')
                    ->label('Duyệt kết nối')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(Device $record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Select::make('address_id')
                            ->label('Chọn vị trí lắp đặt cho Box này')
                            ->relationship('address', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function (Device $record, array $data): void {
                        $randomToken = Str::random(64);

                        $record->update([
                            'status' => 'active',
                            'address_id' => $data['address_id'],
                            'device_token' => $randomToken,
                            'pairing_code' => null,
                            'pairing_expires_at' => null,
                        ]);

                        Notification::make()
                            ->title('Kích hoạt Android Box thành công!')
                            ->body("Đã cấp mã Token bảo mật cho thiết bị.")
                            ->success()
                            ->send();
                    }), // Tự load lại bảng sau khi duyệt thành công

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
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
