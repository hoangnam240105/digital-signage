<?php

namespace App\Http\Controllers\Api;

use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseController
{ // Kế thừa BaseControlle

    protected $authService;
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @OA\Post(
     * path="/api/login",
     * summary="Đăng nhập dành cho Mobile App",
     * tags={"Authen"},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"email","password"},
     * @OA\Property(property="email", type="string", format="email", example="VD:admin@gmail.com"),
     * @OA\Property(property="password", type="string", format="password", example="VD:12345678")
     * ),
     * ),
     * @OA\Response(
     * response=200,
     * description="Đăng nhập thành công",
     * @OA\JsonContent()
     * ),
     * @OA\Response(
     * response=401,
     * description="Sai tài khoản hoặc mật khẩu"
     * )
     * )
     */

    public function login(Request $request)
    {
        // 1. Tìm User thật từ Database
        $user = User::query()->where('email', $request->email)->first();

        // 2. Kiểm tra password (nhớ thêm 'use Illuminate\Support\Facades\Hash;' ở đầu file)
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Sai tài khoản hoặc mật khẩu'], 401);
        }

        // 3. TẠO TOKEN TỪ BIẾN $user (Biến này phải là Model User)
        $token = $user->createToken('MobileApp')->plainTextToken;

        // 4. Trả về kết quả
        return response()->json([
            'token' => $token,
            'user' => $user
        ], 200);
    }
}
