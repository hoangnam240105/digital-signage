<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function checkLogin($email, $password)
    {
        $user = User::query()->where('email', $email)->first();

        // Kiểm tra xem user có tồn tại và mật khẩu có đúng không
        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        // Token cho Mobile App
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ],200);
    }
}
