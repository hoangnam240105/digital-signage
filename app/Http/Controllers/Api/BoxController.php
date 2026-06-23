<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Device;
use App\Models\Media;
use App\Models\Schedule;
use App\Models\MediaLog;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;

class BoxController extends BaseController
{
    /**
     * @OA\Post(
     * path="/api/register-device",
     * summary="API Đăng ký Thiết bị mới đầy đủ thông tin (Từ Mobile/Box)",
     * description="Android Box gọi API này gửi thông tin lên để nằm trong danh sách chờ duyệt. Khi Admin duyệt trên Filament, gọi lại API này sẽ trả về Token.",
     * tags={"REGISTER-DEVICE"},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"device_code", "name"},
     * @OA\Property(property="device_code", type="string", example="BOX_8899_PRO", description="Mã phần cứng duy nhất (Mobile tự lấy ngầm)"),
     * @OA\Property(property="name", type="string", example="Màn hình LED Tầng 1", description="Tên gợi nhớ (Thợ lắp máy gõ vào ô Input)"),
     * @OA\Property(property="os_version", type="string", example="Android 11", description="Phiên bản hệ điều hành của Box (Mobile tự lấy ngầm)"),
     * @OA\Property(property="app_version", type="string", example="v1.0.0", description="Phiên bản của ứng dụng chạy trên Box (Mobile tự lấy ngầm)")
     * )
     * ),
     * @OA\Response(
     * response=202,
     * description="Đăng ký thành công, đang chờ phê duyệt",
     * @OA\JsonContent(
     * @OA\Property(property="status", type="string", example="pending"),
     * @OA\Property(property="message", type="string", example="Thiết bị đã gửi thông tin thành công, vui lòng chờ phê duyệt.")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Đã phê duyệt - Trả Token về cho Box tự động lưu",
     * @OA\JsonContent(
     * @OA\Property(property="status", type="string", example="active"),
     * @OA\Property(property="device_token", type="string", example="chuoi_token_ngau_nhien_64_ky_tu...")
     * )
     * )
     * )
     */
    public function registerDevice(Request $request)
    {
        $request->validate([
            'device_code' => 'required|string',
            'name' => 'required|string|max:255',
        ]);

        $device = Device::query()->where('device_code', $request->device_code)->first();

        // 1. Nếu chưa có trong DB ➔ Tiến hành tạo mới ở trạng thái 'pending'
        if (!$device) {
            Device::create([
                'device_code' => $request->device_code,
                'name' => $request->name,
                'status' => 'pending',
                'ip_address' => $request->ip(),
                'device_token' => null,
            ]);

            return response()->json([
                'status' => 'pending',
                'message' => 'Thiết bị đã gửi thông tin thành công, vui lòng chờ Admin phê duyệt.'
            ], 202);
        }

        // 2. Nếu đã có và trạng thái VẪN ĐANG CHỜ DUYỆT
        if ($device->status === 'pending') {
            return response()->json([
                'status' => 'pending',
                'message' => 'Thông tin đã có trên hệ thống. Vui lòng đợi Admin bấm nút Duyệt trên Filament.'
            ], 202);
        }

        // 3. Nếu Admin ĐÃ BẤM DUYỆT trên Filament thành công ➔ Nhả Token về để Mobile "đớp" và lưu lại
        if ($device->status === 'active' && $device->device_token) {
            return response()->json([
                'status' => 'active',
                'message' => 'Thiết bị đã được phê duyệt hoạt động!',
                'device_token' => $device->device_token
            ], 200);
        }

        return response()->json(['status' => 'error', 'message' => 'Trạng thái thiết bị không hợp lệ.'], 400);
    }

    /**
     * @OA\Get(
     * path="/api/server-time",
     * summary="API lấy thời gian hiện tại của Server",
     * description="Cung cấp mốc thời gian chuẩn của Server để Android Box đồng bộ đồng hồ, tránh lỗi lệch múi giờ.",
     * security={{"deviceToken": {}}}, 
     * tags={"Box API"},
     * @OA\Response(
     * response=200,
     * description="Lấy thời gian thành công",
     * @OA\JsonContent(
     * @OA\Property(property="status", type="string", example="success"),
     * @OA\Property(property="timezone", type="string", example="Asia/Ho_Chi_Minh"),
     * @OA\Property(property="server_time", type="string", example="2026-06-15 03:15:00"),
     * @OA\Property(property="timestamp", type="integer", example=1781512500)
     * )
     * )
     * )
     */
    public function getServerTime()
    {
        return response()->json([
            'status' => 'success',
            'timezone' => config('app.timezone'),
            'server_time' => now()->toDateTimeString(),
            'timestamp' => now()->timestamp
        ], 200);
    }

    /**
     * @OA\Post(
     * path="/api/check-status",
     * summary="API Check trạng thái Box (Heartbeat)",
     * description="Cục Box gọi API này định kỳ để báo danh và cập nhật thời gian hoạt động gần nhất (last_connected_at) lên hệ thống.",
     * tags={"Box API"},
     * security={{"deviceToken": {}}},
     * @OA\Response(
     * response=200,
     * description="Cập nhật trạng thái thành công",
     * @OA\JsonContent(
     * @OA\Property(property="status", type="string", example="success"),
     * @OA\Property(property="message", type="string", example="Cập nhật trạng thái thiết bị thành công."),
     * @OA\Property(property="device_name", type="string", example="Màn hình LED Sảnh Chính")
     * )
     * ),
     * @OA\Response(response=401, description="Chưa xác thực hoặc Token sai")
     * )
     */
    public function checkStatus(Request $request)
    {
        // TỰ ĐỘNG lấy con Box đã được Middleware xác thực thông qua Token ghim vào request
        $device = $request->attributes->get('current_device');

        // Cập nhật thời gian tương tác cuối cùng và địa chỉ IP (nếu có thay đổi)
        $device->update([
            'last_connected_at' => now(),
            'ip_address' => $request->ip() // Tự lấy IP thực tế của Box gửi lên luôn
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật trạng thái thiết bị thành công.',
            'device_name' => $device->name
        ], 200);
    }

    /**
     * @OA\Get(
     * path="/api/get-schedule",
     * summary="Lấy lịch trình cho thiết bị qua Device Token",
     * tags={"Box API"},
     * security={{"deviceToken": {}}},
     * @OA\Response(
     * response=200,
     * description="Thành công"
     * ),
     * @OA\Response(response=401, description="Token không hợp lệ hoặc thiếu")
     * )
     */
    public function getSchedule(Request $request)
    {
        $deviceToken = $request->bearerToken() ?? $request->header('device_token');

        if (!$deviceToken) {
            return response()->json([
                'success' => false,
                'message' => 'Thiếu token xác thực!'
            ], 401);
        }

        $device = Device::with([
            'address.schedules.media' // Kết bảng liên hoàn để lấy lịch trình và file
        ])
            ->where('device_token', $deviceToken)
            ->first();

        // 3. Kiểm tra nếu không tìm thấy thiết bị
        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực: Device Token không hợp lệ hoặc thiết bị chưa được duyệt.'
            ], 401);
        }

        // 4. Lấy lịch trình an toàn
        $allSchedules = $device->address?->schedules ?? collect();

        // 5. Dùng biến $activeSchedules đã lọc là chuẩn bài, không bị lỗi gạch đỏ nữa
        $responseData = $allSchedules->map(function ($schedule) {
            $daysArray = is_string($schedule->days_of_week) ? json_decode($schedule->days_of_week, true) : $schedule->days_of_week;

            if (is_array($daysArray)) {
                sort($daysArray);
            }

            $currentPlaylist = DB::table('schedule_media')
                ->join('media', 'schedule_media.media_id', '=', 'media.id')
                ->where('schedule_media.schedule_id', $schedule->id) // Lọc chính xác theo ID lịch trình đang chạy
                ->select(
                    'media.id as media_id',
                    'media.name as file_name',
                    'schedule_media.zone_name',
                    'schedule_media.play_order',
                    'schedule_media.duration'
                )
                ->orderBy('schedule_media.zone_name')
                ->orderBy('schedule_media.play_order')
                ->get();

            // Tự động thêm link tải (download_url) cho từng file trong danh sách này
            foreach ($currentPlaylist as $item) {
                $item->download_url = url("/api/download-media/{$item->media_id}");
            }

            // Trả về cấu hình đầy đủ thông tin cho Mobile
            return [
                'schedule_id'   => $schedule->id,
                'schedule_name' => $schedule->name,
                'total_files'   => $currentPlaylist->count(), // Đếm số file thực tế của lịch trình này
                'playlist'      => $currentPlaylist,          // Trả về danh sách file sạch kèm cấu hình phát
                'date_start'    => $schedule->start_date . ' - ' . $schedule->start_time,
                'date_end'      => $schedule->end_date . ' - ' . $schedule->end_time,
                'days_active'   => is_array($daysArray) ? implode(', ', $daysArray) : '',
            ];
        });
        return response()->json([
            'success' => true,
            'playlist' => $responseData // Biến chứa data sau khi map xong
        ], 200);
    }

    /**
     * @OA\Post(
     * path="/api/updateMediaLog",
     * summary="Đồng bộ lịch sử phát (Logs) theo chùm Schedule",
     * tags={"Box API"},
     * security={{"deviceToken": {}}},
     * @OA\RequestBody(
     * required=true,
     * description="Dữ liệu danh sách log đã gom nhóm gọn gàng theo Schedule ID",
     * @OA\JsonContent(
     * required={"schedule_id", "logs"},
     * @OA\Property(property="schedule_id", type="integer", example=2, description="ID của lịch trình đang phát"),
     * @OA\Property(
     * property="logs",
     * type="array",
     * description="Danh sách các file media đã phát xong",
     * @OA\Items(
     * required={"media_id", "played_at"},
     * @OA\Property(property="media_id", type="integer", example=12, description="ID của file media"),
     * @OA\Property(property="played_at", type="string", format="date-time", example="2026-06-23 17:01:20", description="Thời gian phát xong (Định dạng chuẩn: Y-m-d H:i:s)")
     * )
     * )
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Đồng bộ thành công dữ liệu sạch",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Hệ thống đã đồng bộ thành công 2 log hợp lệ!")
     * )
     * ),
     * @OA\Response(response=422, description="Sai cấu trúc JSON hoặc sai định dạng thời gian"),
     * @OA\Response(response=401, description="Token không hợp lệ hoặc thiếu token"),
     * @OA\Response(response=400, description="Không có dòng log nào hợp lệ để lưu")
     * )
     */
    public function updateMediaLog(Request $request)
    {
        // 1. Xác thực thiết bị qua Token như cũ
        $deviceToken = $request->bearerToken() ?? $request->header('device_token');
        if (!$deviceToken) {
            return response()->json(['success' => false, 'message' => 'Thiếu token xác thực!'], 401);
        }

        $device = Device::where('device_token', $deviceToken)->first();
        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Thiết bị không hợp lệ.'], 401);
        }

        // TẦNG 1: Validate cấu trúc JSON mới
        $validator = Validator::make($request->all(), [
            'schedule_id'       => 'required|integer',
            'logs'              => 'required|array|min:1',
            'logs.*.media_id'   => 'required|integer',
            'logs.*.played_at'  => 'required|date_format:Y-m-d H:i:s',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu gửi lên sai cấu trúc hoặc sai định dạng thời gian.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $scheduleId = $request->input('schedule_id');
        $logs       = $request->input('logs');
        $dataToInsert = [];

        // TỐI ƯU HIỆU NĂNG: Chỉ bốc đúng các media_id hợp lệ THUỘC schedule_id này lên RAM (Quá nhanh!)
        $validMediaIds = DB::table('schedule_media')
            ->where('schedule_id', $scheduleId)
            ->pluck('media_id')
            ->toArray();

        // TẦNG 2: Vòng lặp gom log sạch
        foreach ($logs as $log) {
            $mediaId = $log['media_id'];

            // KIỂM TRA CHÉO: Nếu file media gửi lên không nằm trong lịch trình này -> BỎ QUA
            if (!in_array($mediaId, $validMediaIds)) {
                continue;
            }

            $dataToInsert[] = [
                'device_id'   => $device->id,
                'media_id'    => $mediaId,
                'schedule_id' => $scheduleId,
                'played_at'   => $log['played_at'],
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        // 4. Lưu một loạt xuống DB
        if (count($dataToInsert) > 0) {
            MediaLog::insert($dataToInsert);

            return response()->json([
                'success' => true,
                'message' => 'Hệ thống đã đồng bộ thành công ' . count($dataToInsert) . ' log hợp lệ!'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Không có dữ liệu log nào khớp với lịch chiếu hiện tại để lưu.'
        ], 400);
    }


    /**
     * @OA\Get(
     * path="/api/info",
     * summary="API Lấy thông tin cấu hình hệ thống của Box",
     * description="Box gọi API này khi vừa khởi động để lấy thông tin định danh và tham số vận hành từ Server.",
     * tags={"Box API"},
     * security={{"deviceToken": {}}},
     * @OA\Response(
     * response=200,
     * description="Lấy thông tin thành công"
     * )
     * )
     */
    public function getInfo(Request $request)
    {
        $device = $request->attributes->get('current_device');

        // Eager load bảng address liên kết
        $device->load('address');

        return response()->json([
            'status' => 'success',
            'system_info' => [
                'device_id'   => $device->id,
                'device_name' => $device->name,
                'device_code' => $device->device_code,

                'ip_address'  => $device->ip_address,
                'status'      => $device->status,
                'location'    => $device->address ? $device->address->name : 'Chưa gán vị trí'
            ],
            'configurations' => [
                'heartbeat_interval_seconds' => 60, // Bắt Box cứ 60s gọi check-status 1 lần
                'schedule_refresh_interval_minutes' => 30, // Cứ 30p đồng bộ lại lịch 1 lần
            ]
        ], 200);
    }

    /**
     * @OA\Get(
     * path="/api/download-media/{id}",
     * tags={"Box API"},
     * summary="API Stream/Tải tệp tin media",
     * security={{"deviceToken": {}}},
     * description="Hỗ trợ trả về tệp tin vật lý dưới dạng luồng bytes giúp thiết bị tua và tải video mượt mà.",
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="ID của file media",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(response=200, description="Trả về luồng dữ liệu file")
     * )
     */
    public function downloadMedia($id)
    {
        $media = Media::query()->find($id);

        if (!$media) {
            return response()->json(['message' => 'Không tìm thấy dữ liệu file này trên hệ thống.'], 404);
        }

        $filePath = storage_path('app/public/' . $media->file_path);

        if (file_exists($filePath)) {
            $mimeType = mime_content_type($filePath);
            return response()->download($filePath, $media->name ?? 'file_download.mp4', [
                'Content-Type' => $mimeType,
                'Accept-Ranges' => 'bytes' // CRITICAL: Cực kỳ quan trọng để trình phát video Android có thể đọc stream mượt
            ]);
        }

        return response()->json(['message' => 'File vật lý không tồn tại trên server.'], 404);
    }
}
