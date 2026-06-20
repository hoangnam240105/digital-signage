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
use Filament\Forms\Components\Select;
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

                        Select::make('address_id')
                            ->label('Địa điểm')
                            ->relationship('address', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

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
                Tables\Columns\TextColumn::make('id')->label('ID'),
                Tables\Columns\TextColumn::make('name')->label('Thiết bị')->searchable(),
                Tables\Columns\TextColumn::make('device_code')->label('Mã')->searchable(),
                Tables\Columns\TextColumn::make('address.name')->label('Địa điểm')->default('-'),
                Tables\Columns\TextColumn::make('ip_address')->label('Địa chỉ IP'),
                
                // Trạng thái dùng dấu chấm tròn (giống ảnh của em)
                Tables\Columns\IconColumn::make('status')
                    ->label('Trạng thái')
                    ->icon('heroicon-s-circle')
                    ->color(fn (string $state): string => match ($state) {
                        'online' => 'success', // Chấm xanh
                        'offline' => 'danger', // Chấm đỏ
                        default => 'warning',
                    }),
                    
                Tables\Columns\TextColumn::make('created_at')->label('Ngày tạo')->date('d/m/Y'),
            ])
            ->filters([
                // BỘ LỌC TÌM KIẾM (Giống ảnh 4)
                Tables\Filters\SelectFilter::make('address_id')
                    ->relationship('address', 'name')
                    ->label('Địa điểm (Lọc)')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    
                
                    Tables\Actions\EditAction::make()
                        ->label('Sửa')
                        ->icon('heroicon-m-pencil-square')
                        ->modalHeading('Sửa thông tin thiết bị')
                        ->modalButton('LƯU'),

                    
                    Tables\Actions\ViewAction::make()
                        ->label('Information')
                        ->icon('heroicon-m-information-circle')
                        ->modalHeading('Device Information'),

                   
                    Tables\Actions\DeleteAction::make()
                        ->label('Xóa')
                        ->icon('heroicon-m-trash')
                        ->modalHeading('Bạn có chắc chắn muốn xóa mục này không?')
                        ->modalDescription('Hành động này không thể hoàn tác.'),
                ])
                ->icon('heroicon-m-ellipsis-vertical') 
                ->tooltip('Hành động')
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
            // 'create' => Pages\CreateDevice::route('/create'),
            // 'edit' => Pages\EditDevice::route('/{record}/edit'),
        ];
    }
}
