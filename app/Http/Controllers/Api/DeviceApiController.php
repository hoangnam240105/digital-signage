<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use Illuminate\Support\Str;

class DeviceApiController extends Controller
{
    /**
     * Android Box gửi device_code lên để đăng ký/lấy mã kích hoạt hiển thị lên TV
     */
    public function register(Request $request)
    {
        $request->validate([
            'device_code' => 'required|string', // Mã UUID hoặc Serial của Box Android
            'name'        => 'required|string', // Tên thiết bị (ví dụ: Box_Tầng_1)
            'ip_address'  => 'nullable|ip',
        ]);

        // Tìm thiết bị theo mã device_code, nếu chưa có thì tạo mới
        $device = Device::firstOrNew(['device_code' => $request->device_code]);

        $device->name = $request->name;
        $device->ip_address = $request->ip_address;
        $device->last_connected_at = now();

        // Nếu thiết bị chưa được active (mới tinh hoặc đang chờ kết nối lại)
        if ($device->status !== 'active') {
            // Tạo mã ghép đôi gồm 6 ký tự viết hoa ngẫu nhiên (Ví dụ: AX89TR)
            $device->pairing_code = strtoupper(Str::random(6));
            // Mã này chỉ có giá trị trong vòng 10 phút
            $device->pairing_expires_at = now()->addMinutes(10);
            $device->status = 'pending';
        }

        $device->save();

        return response()->json([
            'success' => true,
            'message' => 'Vui lòng nhập mã này trên hệ thống quản trị để kích hoạt thiết bị.',
            'data' => [
                'pairing_code' => $device->pairing_code,
                'expires_at'   => $device->pairing_expires_at,
                'status'       => $device->status
            ]
        ]);
    }

    /**
     * Android Box liên tục gọi API này (Polling) để kiểm tra xem Admin đã duyệt chưa
     */
    public function checkPairing(Request $request)
    {
        $request->validate([
            'device_code' => 'required|string',
        ]);

        $device = Device::query()->where('device_code', $request->device_code)->first();

        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Thiết bị không tồn tại.'], 404);
        }

        // Nếu Admin đã duyệt kích hoạt trên Filament
        if ($device->status === 'active') {
            // Nếu chưa có token bảo mật thì tạo mới
            if (empty($device->device_token)) {
                $device->device_token = Str::random(64); // Chuỗi token bí mật dài 64 ký tự
                $device->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Thiết bị đã được kích hoạt thành công!',
                'device_token' => $device->device_token,
                'status' => $device->status
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Đang chờ Admin duyệt kích hoạt...',
            'status' => $device->status
        ]);
    }

    /**
     * Android Box gửi ping định kỳ để cập nhật trạng thái "Đang hoạt động"
     */
    public function ping(Request $request)
    {
        // Lấy token từ Header (Authorization: Bearer <token>) hoặc từ request gửi lên
        $token = $request->bearerToken() ?? $request->input('device_token');

        if (!$token) {
            return response()->json(['success' => false, 'message' => 'Thiếu Token xác thực.'], 401);
        }

        // Tìm thiết bị đang hoạt động có token trùng khớp
        $device = Device::query()->where('device_token', $token)->where('status', 'active')->first();

        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Token không hợp lệ hoặc Box đã bị khóa.'], 401);
        }

        // Cập nhật thời gian tương tác cuối và IP mới nhất (nếu Box đổi mạng)
        $device->update([
            'last_connected_at' => now(),
            'ip_address' => $request->ip()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ping thành công',
            'last_connected_at' => $device->last_connected_at
        ]);
    }
}
