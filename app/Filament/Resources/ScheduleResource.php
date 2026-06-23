<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleResource\Pages;
use App\Models\Schedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin lịch')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Tên lịch')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('start_date')
                            ->label('Ngày bắt đầu')
                            ->required(),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('Ngày kết thúc')
                            ->required(),

                        Forms\Components\TimePicker::make('start_time')
                            ->label('Giờ bắt đầu')
                            ->required(),

                        Forms\Components\TimePicker::make('end_time')
                            ->label('Giờ kết thúc')
                            ->required(),

                        Forms\Components\CheckboxList::make('days_of_week')
                            ->label('Ngày trong tuần')
                            ->options([
                                2 => 'Thứ 2',
                                3 => 'Thứ 3',
                                4 => 'Thứ 4',
                                5 => 'Thứ 5',
                                6 => 'Thứ 6',
                                7 => 'Thứ 7',
                                8 => 'Chủ nhật',
                            ])
                            ->columns(3),
                    ])
                    ->columns(2),

                Forms\Components\Select::make('addresses_id') // Tên trùng với tên hàm 'addresses()' trong Model Schedule
                    ->relationship('addresses', 'name') // 'addresses' là quan hệ, 'name' là cột hiển thị của bảng addresses
                    ->multiple() // Cho phép chọn nhiều Địa điểm cùng lúc
                    ->label('Áp dụng cho địa điểm')
                    ->required()
                    ->preload() // Load trước danh sách cho mượt
                    ->searchable() // Cho phép gõ tìm kiếm tên địa điểm nếu danh sách quá dài
                    ->columns(4),
                Repeater::make('scheduleMedia') // Đổi lại thành tên hàm quan hệ hasMany mới
                    ->relationship('scheduleMedia') // Trỏ sang hàm hasMany trong Model Schedule
                    ->label('Chọn File và trình tự phát Media')
                    ->columnSpanFull()
                    ->schema([

                        //Ô CHỌN THIẾT BỊ
                        Select::make('media_id') // Tên mối quan hệ trong Model (media)
                            ->relationship('media', 'name') // Tên quan hệ và cột hiển thị
                            ->label('Chọn File trình chiếu')
                            ->preload() // Load trước danh sách cho mượt
                            ->required(),

                        Select::make('zone_name')
                            ->label('Vùng hiển thị')
                            ->options([
                                'main_zone' => 'Main_zone',
                                'sidebar_zone' => 'Sidebar_zone',
                                'footer_zone' => 'Footer_zone',
                            ])
                            ->required(),

                        TextInput::make('play_order')
                            ->label('Thứ tự phát')
                            ->numeric()
                            ->minValue(1)
                            ->required(),

                        TextInput::make('duration')
                            ->label('Thời lượng (Giây)')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ])
                    ->columns(4)
                    ->addActionLabel('Thêm cấu hình phát')
                    ->reorderable('play_order')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên lịch')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('addresses.name')
                    ->label('Android Box')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('media.name')
                    ->label('File trình chiếu')
                    ->badge() //Biến danh sách tên video thành các ô Tag
                    ->color('success') // Màu xanh lá
                    ->wrap(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Ngày bắt đầu')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Ngày kết thúc')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Giờ bắt đầu')
                    ->time(),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('Giờ kết thúc')
                    ->time(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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

    protected function getRedirectUrl(): string
    {
        // Thêm dòng này để tạo xong tự quay về trang danh sách (index)
        return $this->getResource()::getUrl('index');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchedules::route('/'),
            'create' => Pages\CreateSchedule::route('/create'),
            'edit' => Pages\EditSchedule::route('/{record}/edit'),
        ];
    }
}
