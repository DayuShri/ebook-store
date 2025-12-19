<?php

namespace App\Modules\Library\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LibraryItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'book_id' => $this->book_id,
            'order_id' => $this->order_id,
            'status' => $this->status,
            'granted_at' => $this->granted_at,
            'revoked_at' => $this->revoked_at,
        ];
    }
}
