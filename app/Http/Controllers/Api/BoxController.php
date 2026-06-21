<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
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
     * path="/api/schedule",
     * summary="API Lấy lịch phát quảng cáo cho Thiết bị",
     * description="Tự động nhận diện thiết bị qua Token để trả về danh sách các file hình ảnh/video quảng cáo tương ứng với Vị trí lắp đặt.",
     * tags={"Box API"},
     * security={{"deviceToken": {}}},
     * @OA\Response(
     * response=200,
     * description="Lấy lịch trình thành công",
     * @OA\JsonContent(
     * @OA\Property(property="status", type="string", example="success"),
     * @OA\Property(property="device_name", type="string", example="Màn hình LED Sảnh Chính"),
     * @OA\Property(property="playlist", type="array", @OA\Items(
     * @OA\Property(property="media_id", type="integer", example=9),
     * @OA\Property(property="title", type="string", example="Video Quảng Cáo Trà Sữa"),
     * @OA\Property(property="file_url", type="string", example="https://ten-mien.com/storage/media/video1.mp4"),
     * @OA\Property(property="type", type="string", example="video")
     * ))
     * )
     * ),
     * @OA\Response(response=400, description="Thiết bị hoạt động nhưng chưa được gán vị trí lắp đặt trên Filament")
     * )
     */
    public function getSchedule(Request $request)
    {
        $device = $request->attributes->get('current_device');

        if (!$device->address_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Thiết bị chưa được gán vị trí lắp đặt trên hệ thống.'
            ], 400);
        }

        // Load mối quan hệ từ địa chỉ sang lịch trình và danh sách tệp tin quảng cáo
        $device->load(['address.schedules.media']);

        $playlist = [];

        if ($device->address && $device->address->schedules) {
            foreach ($device->address->schedules as $schedule) {
                foreach ($schedule->media as $mediaItem) {
                    $playlist[] = [
                        'media_id' => $mediaItem->id,
                        'title'    => $mediaItem->name,
                        // Khớp chính xác trường file_path theo ảnh cấu trúc DB của bạn
                        'file_url' => asset('storage/' . $mediaItem->file_path),
                        'type'     => $mediaItem->type, // video hoặc image
                    ];
                }
            }
        }

        return response()->json([
            'status'      => 'success',
            'device_name' => $device->name,
            'playlist'    => $playlist
        ], 200);
    }


    /**
     * @OA\Post(
     * path="/api/log-media",
     * summary="API Cập nhật lượt phát của tệp quảng cáo",
     * description="Ghi nhận Android Box đã phát xong một tệp tin quảng cáo thành công phục vụ việc làm báo cáo.",
     * tags={"Box API"},
     * security={{"deviceToken": {}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"media_id", "played_at"},
     * @OA\Property(property="media_id", type="integer", example=1, description="ID của file media vừa phát xong"),
     * @OA\Property(property="played_at", type="string", format="date-time", example="2026-06-15 02:45:00", description="Định dạng: Y-m-d H:i:s")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Đã ghi log thành công",
     * @OA\JsonContent(
     * @OA\Property(property="status", type="string", example="success"),
     * @OA\Property(property="message", type="string", example="Đã cập nhật log lượt chiếu quảng cáo thành công.")
     * )
     * ),
     * @OA\Response(response=422, description="Dữ liệu gửi lên sai định dạng")
     * )
     */
    public function updateLogMedia(Request $request)
    {
        $device = $request->attributes->get('current_device');

        $validator = Validator::make($request->all(), [
            'media_id'  => 'required|integer|exists:media,id',
            'played_at' => 'required|date_format:Y-m-d H:i:s',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Dữ liệu gửi lên không hợp lệ.',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            MediaLog::create([
                // Đổi trường box_id ăn theo chính xác id của device được Middleware xác thực qua token
                'device_id' => $device->id, // Thay bằng tên cột log của bạn (device_id hoặc box_id)
                'media_id'  => $request->input('media_id'),
                'played_at' => $request->input('played_at'),
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Đã cập nhật log lượt chiếu quảng cáo thành công.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Có lỗi xảy ra trên server: ' . $e->getMessage()
            ], 500);
        }
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
