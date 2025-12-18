<?php

namespace App\Modules\Library\Controllers\Http;

use App\Http\Controllers\Controller;
use App\Modules\Library\Services\LibraryService;
use App\Modules\Library\Requests\GrantLibraryRequest;
use App\Modules\Library\Resources\LibraryItemResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Modules\Library\Traits\ApiResponse;

class LibraryController extends Controller
{
    use ApiResponse;

    protected $service;

    public function __construct(LibraryService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        \Log::info('Library index called');
        \Log::info('Authorization header: ' . $request->header('Authorization'));
        \Log::info('Bearer token: ' . $request->bearerToken());
        \Log::info('User from request: ' . ($request->user() ? $request->user()->id : 'NULL'));
        
        if (!$request->user()) {
            \Log::error('No user found in request - authentication failed');
            return $this->errorResponse('Unauthenticated', [], 401);
        }
        
        $userId = $request->user()->id;
        $items = $this->service->listUserLibrary($userId);
        return $this->successResponse('Library retrieved', LibraryItemResource::collection($items)->resolve(), 200);
    }


    public function store(Request $request)
    {
        $data = $request->only(['user_id', 'book_id', 'order_id']);

        $validator = Validator::make($data, [
            'book_id' => ['required', 'uuid'],
            'order_id' => ['nullable', 'uuid'],
            'user_id' => ['nullable', 'uuid'],
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors()->messages(), 422);
        }

        $userId = $request->user()->id ?? $data['user_id'];
        $bookId = $data['book_id'];
        $orderId = $data['order_id'] ?? null;

        try {
            $item = $this->service->grant($userId, $bookId, $orderId);
        } catch (\Throwable $e) {
            return $this->errorResponse('Failed to grant book', null, 500);
        }

        return $this->successResponse('Book added to library successfully', new LibraryItemResource($item), 201);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (! $user || ($user->role ?? '') !== 'admin') {
            return $this->errorResponse('Forbidden: only admin can revoke access', null, 403);
        }

        $item = $this->service->revoke($id);
        if (! $item) {
            return $this->errorResponse('Library item not found', null, 404);
        }

        return $this->successResponse('Book access revoked successfully', new LibraryItemResource($item), 200);
    }

    public function revokeByUserAndBook(Request $request)
    {
        $user = $request->user();
        if (! $user || ($user->role ?? '') !== 'admin') {
            return $this->errorResponse('Forbidden: only admin can revoke access', null, 403);
        }

        $data = $request->only(['user_id', 'book_id']);

        $validator = Validator::make($data, [
            'user_id' => ['required', 'uuid'],
            'book_id' => ['required', 'uuid'],
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors()->messages(), 422);
        }

        try {
            $item = $this->service->revokeByUserAndBook($data['user_id'], $data['book_id']);
            if (! $item) {
                return $this->errorResponse('Library item not found', null, 404);
            }
        } catch (\Throwable $e) {
            return $this->errorResponse('Failed to revoke access', null, 500);
        }

        return $this->successResponse('Book access revoked successfully', new LibraryItemResource($item), 200);
    }
}
