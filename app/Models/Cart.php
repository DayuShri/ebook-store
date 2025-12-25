<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Cart extends Model
{
    use HasUuids;

    public $timestamps = false; // karena tabel carts pakai added_at, bukan created_at/updated_at
    protected $table = 'carts';

    protected $fillable = [
        'user_id', 'book_id', 'quantity', 'added_at'
    ];

    protected $casts = [
        'added_at' => 'datetime',
    ];
}
