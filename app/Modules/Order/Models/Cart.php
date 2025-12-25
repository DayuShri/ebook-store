<?php

namespace App\Modules\Order\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'carts';

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'book_id',
        'quantity',
        'added_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'added_at' => 'datetime',
        ];
    }
}
