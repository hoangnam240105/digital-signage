<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 * version="1.0.0",
 * title="Digital Signage API Documentation",
 * description="Tài liệu API dành cho dự án Digital Signage",
 * @OA\Contact(
 * email="admin@gmail.com"
 * ),
 * )
 */

class BaseController extends Controller
{
    public function sendResponse($result, $message) {
            return response()->json([
                'success' => true,
                'data'    => $result,
                'message' => $message,
            ], 200);
        }

    public function sendError($error, $errorMessages = [], $code = 404) {
        return response()->json([
            'success' => false,
            'message' => $error,
            'errors'  => $errorMessages,
        ], $code);
    }
}
