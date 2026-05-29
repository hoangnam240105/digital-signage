<?php

namespace App\Http\Controllers\Api;

use App\Services\AuthService;
use Illuminate\Http\Request;

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
     * @OA\Property(property="email", type="string", format="email", example="admin@gmail.com"),
     * @OA\Property(property="password", type="string", format="password", example="12345678")
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
        // Gọi Service để check login
        $result = $this->authService->checkLogin($request->email, $request->password);

        if (!$result) {
            return response()->json(['message' => 'Sai tài khoản hoặc mật khẩu'], 401);
        }

        return response()->json($result, 200);
    }
}

