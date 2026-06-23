<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function checkStatus(Request $request)
    {
        $data = ['status' => 'active', 'message' => 'Hệ thống ổn định'];

        // Đừng chỉ return $data;
        return response()->json($data);
    }
}
