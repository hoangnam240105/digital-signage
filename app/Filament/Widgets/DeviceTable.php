<?php

namespace App\Filament\Widgets;

use App\Models\Device;
use App\Models\Media;
use App\Models\MediaLog;
use Filament\Tables;
use Filament\Tables\Table;
use Carbon\Carbon;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Actions\ViewAction;

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

                Tables\Columns\TextColumn::make('address.name')
                    ->sortable()
                    ->label('Vị trí')
                    ->action(
                        ViewAction::make()
                            ->modalHeading('Chi Tiết Tình Trạng Trình Chiếu')
                            ->form([
                                // 🌟 1. PHẦN THÔNG TIN THIẾT BỊ & ĐỊA CHỈ (HIỂN THỊ 2 CỘT CHO ĐẸP)
                                \Filament\Forms\Components\Grid::make(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Tên màn hình')
                                            ->disabled(),

                                        // Mã định danh box (device_code)
                                        TextInput::make('device_code')
                                            ->label('Mã định danh Box')
                                            ->placeholder('Chưa có mã định danh')
                                            ->disabled(),

                                        // Địa chỉ chi tiết (address.description)
                                        TextInput::make('address_description')
                                            ->label('Địa chỉ chi tiết')
                                            ->formatStateUsing(fn($record) => $record->address?->description ?? 'Chưa cập nhật địa chỉ chi tiết')
                                            ->disabled(),

                                        // Địa chỉ IP (ip_address)
                                        TextInput::make('ip_address')
                                            ->label('Địa chỉ IP của Box')
                                            ->placeholder('Chưa nhận IP')
                                            ->disabled(),
                                    ]),

                                // 🌟 2. BẢNG THỐNG KÊ CHI TIẾT & TỔNG HỢP TIẾN ĐỘ
                                Placeholder::make('thong_ke_trinh_chieu')
                                    ->label('Báo cáo tiến độ trình chiếu thực tế')
                                    ->content(function ($record) {
                                        if (!$record->schedules || $record->schedules->isEmpty()) {
                                            return new \Illuminate\Support\HtmlString('<p class="text-sm text-gray-500 italic py-2">Màn hình này hiện chưa được gán lịch trình nào.</p>');
                                        }

                                        $scheduledMediaIds = $record->schedules
                                            ->flatMap(fn($schedule) => $schedule->media->pluck('id'))
                                            ->unique()
                                            ->toArray();

                                        if (empty($scheduledMediaIds)) {
                                            return new \Illuminate\Support\HtmlString('<p class="text-sm text-gray-500 italic py-2">Lịch trình của màn hình này chưa có file media nào.</p>');
                                        }

                                        // Query vào bảng media_logs lấy số lần phát và thời gian cuối dựa theo box_id
                                        $logs = \App\Models\MediaLog::query()
                                            ->where('box_id', $record->id)
                                            ->whereIn('media_id', $scheduledMediaIds)
                                            ->selectRaw('media_id, count(*) as total_played, MAX(played_at) as last_played_at')
                                            ->groupBy('media_id')
                                            ->get()
                                            ->keyBy('media_id');

                                        // Lấy thông tin các file media (Kiểm tra xem tên cột thời lượng là duration hay gì nhé bạn)
                                        $mediaItems = \App\Models\Media::query()->whereIntegerInRaw('id', $scheduledMediaIds)->get();

                                        // --- ĐOẠN TÍNH TOÁN TỔNG SỐ LẦN & TỔNG THỜI GIAN CHIẾU ---
                                        $grandTotalPlayed = 0;       // Tổng số lần chiếu hoàn thành
                                        $totalDurationInSeconds = 0; // Tổng số giây phát sóng

                                        foreach ($mediaItems as $media) {
                                            $logData = $logs->get($media->id);
                                            $playedCount = $logData ? $logData->total_played : 0;

                                            $grandTotalPlayed += $playedCount;

                                            // Nếu bảng media của bạn có cột thời lượng file, hãy điền đúng tên cột (Ví dụ: duration)
                                            // Nếu không có cột thời lượng, mặc định tính 15 giây/lượt phát để không bị lỗi
                                            $duration = $media->duration ?? 15;
                                            $totalDurationInSeconds += ($playedCount * $duration);
                                        }

                                        // Công thức của bạn: (Tổng lần * thời gian mỗi video) / 3600 để ra số giờ
                                        // Làm tròn lấy 2 chữ số thập phân cho đẹp (Ví dụ: 1.25 giờ)
                                        $totalHours = round($totalDurationInSeconds / 3600, 2);
                                        // ---------------------------------------------------------

                                        // Build khối thông tin tổng kết (Tổng số lần & Tổng số giờ)
                                        $htmlOutput = "
                    <div class='grid grid-cols-2 gap-4 mb-4'>
                        <div class='p-3 bg-green-50 dark:bg-green-950/20 border border-green-200 dark:border-green-800 rounded-lg text-center'>
                            <p class='text-xs font-medium text-green-600 dark:text-green-400 uppercase tracking-wider'>Tổng số lần chiếu hoàn thành</p>
                            <p class='text-xl font-bold text-green-700 dark:text-green-300 mt-1'>{$grandTotalPlayed} lần</p>
                        </div>
                        <div class='p-3 bg-primary-50 dark:bg-primary-950/20 border border-primary-200 dark:border-primary-800 rounded-lg text-center'>
                            <p class='text-xs font-medium text-primary-600 dark:text-primary-400 uppercase tracking-wider'>Tổng thời gian chiếu được</p>
                            <p class='text-xl font-bold text-primary-700 dark:text-primary-300 mt-1'>{$totalHours} giờ</p>
                        </div>
                    </div>";

                                        // Build phần danh sách bảng chi tiết từng file ở dưới
                                        $htmlOutput .= '
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm text-left">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-2 font-semibold text-gray-700 dark:text-gray-300">Tên File Media</th>
                                    <th class="px-4 py-2 font-semibold text-gray-700 dark:text-gray-300 text-center">Số Lần Chiếu</th>
                                    <th class="px-4 py-2 font-semibold text-gray-700 dark:text-gray-300">Cập Nhật Cuối (Hoàn thành)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">';

                                        foreach ($mediaItems as $media) {
                                            $logData = $logs->get($media->id);
                                            $playedCount = $logData ? $logData->total_played : 0;

                                            $completionTime = ($logData && $logData->last_played_at)
                                                ? \Carbon\Carbon::parse($logData->last_played_at)->format('H:i:s d/m/Y')
                                                : '<span class="text-gray-400 italic">Chưa phát sóng</span>';

                                            $htmlOutput .= "
                                <tr class='hover:bg-gray-50 dark:hover:bg-gray-800/50'>
                                    <td class='px-4 py-3 font-medium text-gray-900 dark:text-white'>🎬 {$media->name}</td>
                                    <td class='px-4 py-3 text-center'>
                                        <span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'>
                                            {$playedCount} lần
                                        </span>
                                    </td>
                                    <td class='px-4 py-3 text-gray-600 dark:text-gray-400'>{$completionTime}</td>
                                </tr>";
                                        }

                                        $htmlOutput .= '
                            </tbody>
                        </table>
                    </div>';

                                        return new \Illuminate\Support\HtmlString($htmlOutput);
                                    }),
                            ])
                    ),

                Tables\Columns\TextColumn::make('address.schedules.media.name')
                    ->label('File trình chiếu')
                    ->badge() //Biến danh sách tên video thành các ô Tag
                    ->color('success') // Màu xanh lá
                    ->separator(',')
                    ->wrap(),

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


                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Cập nhật cuối')
                    ->dateTime('H:i d/m')
                    ->description(fn(Device $record): string => $record->updated_at->diffForHumans()),
            ]);
    }
}
