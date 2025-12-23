<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(
<<<<<<< HEAD
        string $message = 'Success',
        $data = null,
        int $statusCode = 200
=======
        string $message,
        mixed $data = null,
        int $status = 200
>>>>>>> 2347f10c6476bdca24e206f24d6746d0805d9124
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
<<<<<<< HEAD
            'data' => $data
        ], $statusCode);
    }

    public static function error(
        string $message = 'Error',
        $errors = null,
        int $statusCode = 400
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if (!is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
=======
            'data' => $data,
        ], $status);
    }

    public static function error(
        string $message,
        mixed $errors = null,
        int $status = 400
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
>>>>>>> 2347f10c6476bdca24e206f24d6746d0805d9124
    }
}
