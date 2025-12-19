<?php

namespace App\Modules\Library\Models;

use Illuminate\Database\Eloquent\Model;

class ViewerSession extends Model
{
    protected $table = 'viewer_sessions';
    
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'user_id',
        'book_id',
        'file_id',
        'file_format',
        'token',
        'expires_at',
        'created_at',
        'last_activity_at',
        'device_info',
        'ip_address'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];
}
