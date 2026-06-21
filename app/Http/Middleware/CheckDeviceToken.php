<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Device;
use Symfony\Component\HttpFoundation\Response;

class CheckDeviceToken
{
    /**
     * Xử lý kiểm tra Token của Android Box gửi lên
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Lấy chuỗi Token từ Header "Authorization: Bearer <token>"
        $token = $request->bearerToken();

        // Nếu Box không gửi Token lên Header
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi xác thực: Thiếu Device Token trên Header.'
            ], 401);
        }

        // 2. Tìm trong Database xem có con Box nào sở hữu cái Token này và đang ở trạng thái 'active' không
        $device = Device::query()->where('device_token', $token)
            ->where('status', 'active')
            ->first();

        // Nếu Token sai hoặc Box bị khóa (status không phải active)
        if (!$device) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi xác thực: Device Token không hợp lệ hoặc thiết bị chưa được duyệt.'
            ], 401);
        }

        // 3. ĐÍNH KÈM thông tin con Box tìm được vào Request để mang sang BoxController xài luôn
        $request->attributes->set('current_device', $device);

        return $next($request);
    }
}
