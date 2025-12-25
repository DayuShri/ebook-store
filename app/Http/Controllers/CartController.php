<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'uuid'],
        ]);

        $items = Cart::where('user_id', $data['user_id'])
            ->orderByDesc('added_at')
            ->get();

        return ApiResponse::success(
            'Cart retrieved successfully',
            $items
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'uuid'],
            'book_id' => ['required', 'uuid'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $item = Cart::updateOrCreate(
            [
                'user_id' => $data['user_id'],
                'book_id' => $data['book_id'],
            ],
            [
                'quantity' => $data['quantity'],
                'added_at' => now(),
            ]
        );

        return ApiResponse::success(
            'Item added/updated in cart',
            $item,
            201
        );
    }

    public function update(Request $request, string $bookId)
    {
        $data = $request->validate([
            'user_id' => ['required', 'uuid'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $item = Cart::where('user_id', $data['user_id'])
            ->where('book_id', $bookId)
            ->first();

        if (!$item) {
            return ApiResponse::error(
                'Cart item not found',
                ['book_id' => ['Item not found in cart']],
                404
            );
        }

        $item->update(['quantity' => $data['quantity']]);

        return ApiResponse::success(
            'Cart item updated',
            $item
        );
    }

    public function destroy(Request $request, string $bookId)
    {
        $data = $request->validate([
            'user_id' => ['required', 'uuid'],
        ]);

        Cart::where('user_id', $data['user_id'])
            ->where('book_id', $bookId)
            ->delete();

        return ApiResponse::success(
            'Cart item removed'
        );
    }
}
