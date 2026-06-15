<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Media;
use App\Models\User;
use App\Models\Schedule;
use App\Models\MediaLog;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\Api\BaseController;

class BoxController extends BaseController
{
    /**
     * @OA\Get(
     * path="/api/server-time",
     * summary="API lấy thời gian hiện tại của Server",
     * description="Cung cấp mốc thời gian chuẩn của Server để App Mobile đồng bộ đồng hồ, tránh lỗi lệch múi giờ dưới thiết bị.",
     * security={{"sanctum": {}}}, 
     * tags={"Box API"},
     * @OA\Response(
     * response=200,
     * description="Lấy thời gian thành công",
     * @OA\JsonContent(
     * @OA\Property(property="status", type="string", example="success"),
     * @OA\Property(property="timezone", type="string", example="Asia/Ho_Chi_Minh"),
     * @OA\Property(property="server_time", type="string", example="2026-06-15 03:15:00", description="Định dạng chuẩn Y-m-d H:i:s"),
     * @OA\Property(property="timestamp", type="integer", example=1781512500, description="Thời gian dạng số Unix Timestamp")
     * )
     * )
     * )
     */
    public function getServerTime()
    {
        return response()->json([
            'status' => 'success',
            'timezone' => config('app.timezone'), // Trả về múi giờ (Ví dụ: Asia/Ho_Chi_Minh)
            'server_time' => now()->toDateTimeString(), // Trả về dạng "2026-06-15 03:00:49"
            'timestamp' => now()->timestamp // Trả về dạng số gốc (Milli-seconds) để Mobile dễ tính toán
        ], 200);
    }
    /**
     * @OA\Post(
     * path="/api/check-status",
     * summary="API Check trạng thái Box",
     * description="Cục Box gọi API này định kỳ để cập nhật thời gian hoạt động gần nhất (last_connected_at).",
     * operationId="checkBoxStatus",
     * tags={"Box API"},
     * security={{"sanctum": {}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"id_devices"},
     * @OA\Property(property="box_id", type="integer", example=1, description="ID của cục Box (id trong bảng devices)")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Cập nhật trạng thái thành công",
     * @OA\JsonContent(
     * @OA\Property(property="status", type="string", example="success"),
     * @OA\Property(property="message", type="string", example="Cập nhật trạng thái thiết bị thành công."),
     * @OA\Property(property="device_name", type="string", example="Màn hình LED Sảnh Chính")
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Lỗi Unauthenticated - Chưa đăng nhập hoặc Token sai"
     * ),
     * @OA\Response(
     * response=422,
     * description="Lỗi Validation - box_id không tồn tại trong hệ thống"
     * )
     * )
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'box_id' => 'required|integer|exists:devices,id',
        ]);

        // 2. Tìm thiết bị tương ứng trong bảng devices
        $device = Device::query()->find($request->box_id);

        // 3. Cập nhật thời gian kết nối mới nhất vào cột 'last_connected_at'
        $device->update([
            'last_connected_at' => now(), // Dùng hàm có sẵn của Laravel (đã nhận giờ VN)
        ]);

        // 4. Trả về phản hồi thành công cho Box
        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật trạng thái thiết bị thành công.',
            'device_name' => $device->name // Trả thêm tên thiết bị nếu cần hiển thị dưới Box
        ], 200);
    }

    /**
     * @OA\Get(
     * path="/api/schedule",
     * summary="API Lấy lịch phát quảng cáo cho Thiết bị",
     * description="Cục Box gọi API này để lấy danh sách các file hình ảnh/video cần tải về và trình chiếu theo địa điểm của nó.",
     * operationId="getDeviceSchedule",
     * tags={"Box API"},
     * security={{"sanctum": {}}},
     * @OA\Parameter(
     * name="box_id",
     * in="query",
     * required=true,
     * description="ID của thiết bị (id trong bảng devices)",
     * @OA\Schema(type="integer", example=1)
     * ),
     * @OA\Response(
     * response=200,
     * description="Lấy lịch trình thành công",
     * @OA\JsonContent(
     * @OA\Property(property="status", type="string", example="success"),
     * @OA\Property(property="device_name", type="string", example="Màn hình LED Sảnh Chính"),
     * @OA\Property(property="playlist", type="array", @OA\Items(
     * @OA\Property(property="media_id", type="integer", example=9),
     * @OA\Property(property="title", type="string", example="Video Quảng Cáo Trà Sữa"),
     * @OA\Property(property="file_url", type="string", example="https://ten-mien-cua-ban.com/storage/media/video1.mp4"),
     * @OA\Property(property="type", type="string", example="video")
     * ))
     * )
     * ),
     * @OA\Response(response=422, description="Lỗi dữ liệu đầu vào - box_id không hợp lệ")
     * )
     */
    public function getSchedule(Request $request)
    {
        // 1. Validate kiểm tra xem box_id gửi lên có nằm trong bảng devices không
        $request->validate([
            'box_id' => 'required|integer|exists:devices,id',
        ]);

        // 2. Tìm thiết bị kèm theo vị trí của nó (Eager Loading mối quan hệ sang bảng addresses và schedules)
        // Lưu ý: Đoạn này giả định bạn đã cài đặt các hàm Relationship trong Model Device.
        $device = Device::with(['address.schedules.media'])->find($request->box_id);

        // 3. Gom và bóc tách dữ liệu từ các bảng trung gian để tạo ra một danh sách Playlist sạch
        $playlist = [];

        if ($device->address && $device->address->schedules) {
            foreach ($device->address->schedules as $schedule) {
                foreach ($schedule->media as $mediaItem) {
                    $playlist[] = [
                        'media_id' => $mediaItem->id,
                        'title'    => $mediaItem->name, // Giả định cột tên file trong bảng media là name
                        'file_url' => asset('storage/' . $mediaItem->url), // Đường dẫn để tải file công khai
                        'type'     => $mediaItem->type, // video hoặc image
                    ];
                }
            }
        }

        // 4. Trả về định dạng JSON chuẩn cho cục Box đọc
        return response()->json([
            'status'      => 'success',
            'device_name' => $device->name,
            'playlist'    => $playlist
        ], 200);
    }


    /**
     * @OA\Post(
     * path="/api/log-media",
     * summary="API cập nhật log media",
     * description="Ghi nhận Box đã phát xong một tệp tin quảng cáo/hình ảnh",
     * tags={"Box API"},
     * security={{"sanctum": {}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"media_id", "played_at"},
     * @OA\Property(property="media_id", type="integer", example=1, description="ID của file media vừa phát xong (Lấy từ bảng media)"),
     * @OA\Property(property="played_at", type="string", format="date-time", example="2026-06-15 02:45:00", description="Thời gian phát xong thực tế dưới Box (Định dạng: Y-m-d H:i:s)")
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
     * @OA\Response(
     * response=422,
     * description="Dữ liệu gửi lên không hợp lệ hoặc không tồn tại media_id"
     * ),
     * @OA\Response(
     * response=401,
     * description="Chưa đăng nhập / Token không hợp lệ"
     * )
     * )
     */
    public function updateLogMedia(Request $request)
    {
        // 1. Kiểm tra dữ liệu Mobile gửi lên
        $validator = Validator::make($request->all(), [
            // Bắt buộc phải có media_id và số ID này phải tồn tại trong cột id của bảng media
            'media_id'  => 'required|integer|exists:media,id',
            // Bắt buộc đúng định dạng ngày giờ Năm-Tháng-Ngày Giờ:Phút:Giây
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
            // 2. Lưu log vào database thông qua Model
            MediaLog::create([
                'box_id'    => $request->user()->id, // Tự động lấy ID của Box đang đăng nhập qua Token Sanctum
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
     * summary="API Lấy thông tin và cấu hình hệ thống của Box",
     * description="Box gọi API này khi vừa khởi động để lấy thông tin định danh và cấu hình vận hành từ Server.",
     * operationId="getDeviceInfo",
     * tags={"Box API"},
     * security={{"sanctum": {}}},
     * @OA\Parameter(
     * name="box_id",
     * in="query",
     * required=true,
     * description="ID của thiết bị",
     * @OA\Schema(type="integer", example=1)
     * ),
     * @OA\Response(
     * response=200,
     * description="Lấy thông tin thành công"
     * )
     * )
     */
    public function getInfo(Request $request)
    {
        $request->validate([
            'box_id' => 'required|integer|exists:devices,id',
        ]);

        // Lấy thiết bị và địa chỉ liên kết
        $device = Device::with('address')->find($request->box_id);

        return response()->json([
            'status' => 'success',
            'system_info' => [
                'device_id'   => $device->id,
                'device_name' => $device->name,
                'ip_address'  => $device->ip_address,
                'is_active'   => $device->is_active,
                'location'    => $device->address ? $device->address->name : 'Chưa gán vị trí' // Giả định cột tên ở bảng address là name
            ],
            // Bạn có thể fix cứng một số cấu hình hệ thống ở đây nếu DB chưa có bảng config riêng
            'configurations' => [
                'heartbeat_interval_seconds' => 60, // Bắt Box cứ 60s phải gọi API check-status 1 lần
                'schedule_refresh_interval_minutes' => 30, // Cứ 30 phút phải gọi API lấy lịch 1 lần
            ]
        ], 200);
    }

    /**
     * @OA\Get(
     * path="/api/download-media/{id}",
     * tags={"Box API"},
     * summary="API Download media",
     * security={{"sanctum": {}}},
     * description="Lấy đường dẫn tải tệp tin media cụ thể",
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="ID của file media",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(response=200, description="Trả về link download")
     * )
     */
    public function downloadMedia($id)
    {
        // Tìm file trong database
        $media = Media::findOrFail($id);

        if (!$media) {
            return response()->json(['message' => 'Không tìm thấy dữ liệu file này'], 404);
        }

        // Đường dẫn đến file vật lý (ví dụ lưu trong thư mục public/uploads/)
        $filePath = storage_path('app/public/' . $media->file_path);
        $mimeType = mime_content_type($filePath);
        if (file_exists($filePath)) {
            // return response()->download($filePath);
            return response()->download($filePath, $media->file_name ?? 'file_download.mp4', [
                'Content-Type' => $mimeType,
                'Accept-Ranges' => 'bytes' // Giúp mobile có thể tua được video khi xem stream
            ]);
        }

        return response()->json(['message' => 'File vật lý không tồn tại trên server.'], 404);
    }
}
