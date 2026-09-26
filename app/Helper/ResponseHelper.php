<?php

namespace App\Helper;

class ResponseHelper {
    public static function jsonResponse($status, $message, $data = null, $statusCode = 200) {
        return response()->json([
            'status' => $status,
            'status_code' => $statusCode,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }
}
