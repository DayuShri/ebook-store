<?php

namespace App\Modules\Library\Services;

use App\Modules\Library\Models\LibraryItem;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LibraryService
{
    public function listUserLibrary(string $userId)
    {
        return LibraryItem::where('user_id', $userId)->get();
    }

    public function grant(string $userId, string $bookId, ?string $orderId = null): LibraryItem
    {
        // If user already has the book, return existing (or re-activate if revoked)
        $existing = LibraryItem::where('user_id', $userId)->where('book_id', $bookId)->first();
        if ($existing) {
            if ($existing->status === 'ACTIVE') {
                return $existing;
            }

            // Re-activate revoked item
            $existing->status = 'ACTIVE';
            $existing->revoked_at = null;
            $existing->order_id = $orderId ?? $existing->order_id;
            $existing->granted_at = Carbon::now()->toDateTimeString();
            $existing->save();

            return $existing;
        }

        $item = LibraryItem::create([
            'id' => Str::uuid()->toString(),
            'user_id' => $userId,
            'book_id' => $bookId,
            'order_id' => $orderId,
            'status' => 'ACTIVE',
            'granted_at' => Carbon::now()->toDateTimeString(),
        ]);

        return $item;
    }

    public function revoke(string $id): ?LibraryItem
    {
        $item = LibraryItem::find($id);
        if (! $item) {
            return null;
        }

        $item->status = 'REVOKED';
        $item->revoked_at = Carbon::now()->toDateTimeString();
        $item->save();

        return $item;
    }

    public function revokeByUserAndBook(string $userId, string $bookId): ?LibraryItem
    {
        $item = LibraryItem::where('user_id', $userId)
            ->where('book_id', $bookId)
            ->where('status', 'ACTIVE')
            ->first();

        if (! $item) {
            return null;
        }

        $item->status = 'REVOKED';
        $item->revoked_at = Carbon::now()->toDateTimeString();
        $item->save();

        return $item;
    }
}
