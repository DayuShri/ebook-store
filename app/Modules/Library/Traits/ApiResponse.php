<?php

namespace App\Modules\Library\Traits;

trait ApiResponse
{
    protected function successResponse(string $message = 'OK', $data = null, int $status = 200)
    {
        $payload = [
            'success' => true,
            'message' => $message,
        ];

        if (! is_null($data)) {
            $payload['data'] = $data;
        }

        return response()->json($payload, $status);
    }

    protected function errorResponse(string $message = 'Error', $errors = null, int $status = 400)
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if (! is_null($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }
}
