<?php

namespace App\Modules\Library\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryItem extends Model
{
    protected $table = 'library_items';
    
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

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
}
