<?php

namespace App\Modules\Review_Reading\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryItem extends Model
{
    protected $table = 'library_items';

    protected $fillable = [
        'id',
        'user_id',
        'book_id',
        'order_id',
        'status',
        'granted_at',
        'revoked_at'
    ];

    protected $casts = [
        'granted_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public $timestamps = false;
}
