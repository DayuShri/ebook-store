<?php

namespace App\Modules\Library\Controllers\Internal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Library\Services\LibraryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Modules\Library\Traits\ApiResponse;

class LibraryInternalController extends Controller
{
    use ApiResponse;
    
    protected $service;

    public function __construct(LibraryService $service)
    {
        $this->service = $service;
    }

    public function grant(Request $request)
    {
        $data = $request->only(['user_id', 'book_id', 'order_id']);

        $validator = Validator::make($data, [
            'user_id' => ['required', 'uuid'],
            'book_id' => ['required', 'uuid'],
            'order_id' => ['nullable', 'uuid'],
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors()->messages(), 422);
        }

        if (! empty($data['order_id'])) {
            $order = DB::table('orders')->where('id', $data['order_id'])->first();
            if (! $order) {
                return $this->errorResponse('Validation failed', ['order_id' => ['The selected order id is invalid.']], 422);
            }

            if (strtolower($order->status) !== 'paid') {
                return $this->errorResponse('Validation failed', ['order_id' => ['The order is not paid.']], 422);
            }
        }

        try {
            $item = $this->service->grant($data['user_id'], $data['book_id'], $data['order_id'] ?? null);
        } catch (\Throwable $e) {
            Log::error('Library HMVC grant failed: ' . $e->getMessage(), $data);
            return $this->errorResponse('Failed to grant book', null, 500);
        }

        return $this->successResponse('Book granted to user', ['id' => $item->id, 'status' => $item->status], 200);
    }
}
