<?php

namespace App\Modules\Order\Controllers\Http;

use App\Http\Controllers\Controller;
use App\Modules\Order\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $items = Cart::query()
            ->where('user_id', $userId)
            ->orderByDesc('added_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Cart fetched',
            'data' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $userId = $request->user()->id;

        $data = $request->validate([
            'book_id' => ['required', 'uuid'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $item = Cart::query()->updateOrCreate(
            ['user_id' => $userId, 'book_id' => $data['book_id']],
            ['quantity' => $data['quantity'], 'added_at' => now()]
        );

        return response()->json([
            'success' => true,
            'message' => 'Item added/updated in cart',
            'data' => $item,
        ], 201);
    }

    public function update(Request $request, string $bookId)
    {
        $userId = $request->user()->id;

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $item = Cart::query()
            ->where('user_id', $userId)
            ->where('book_id', $bookId)
            ->first();

        if (!$item) {
            throw ValidationException::withMessages([
                'book_id' => ['Cart item not found'],
            ]);
        }

        $item->update(['quantity' => $data['quantity']]);

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated',
            'data' => $item->fresh(),
        ]);
    }

    public function destroy(Request $request, string $bookId)
    {
        $userId = $request->user()->id;

        Cart::query()
            ->where('user_id', $userId)
            ->where('book_id', $bookId)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart item removed',
        ]);
    }
}
