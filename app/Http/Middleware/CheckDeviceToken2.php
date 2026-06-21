<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Device;
use Symfony\Component\HttpFoundation\Response;

class CheckDeviceToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Lấy token từ Header của request do App TV gửi lên
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'message' => 'Không tìm thấy Device Token. Truy cập bị từ chối!'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // 2. Kiểm tra xem token này có hợp lệ không
        $device = Device::where('device_token', $token)
            ->where('status', 'active')
            ->first();

        if (!$device) {
            return response()->json([
                'message' => 'Device Token không hợp lệ hoặc thiết bị đã bị khóa!'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // 3. Đính kèm thông tin thiết bị vào request để sau này xài
        $request->merge(['current_device' => $device]);

        return $next($request);
    }
}
