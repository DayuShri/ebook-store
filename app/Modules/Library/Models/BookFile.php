<?php

namespace App\Modules\Library\Models;

use Illuminate\Database\Eloquent\Model;

class BookFile extends Model
{
    protected $table = 'book_files';
    
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'book_id',
        'file_path',
        'file_format',
        'file_size_mb',
        'encryption_key',
        'checksum'
    ];

    protected $casts = [
        'file_size_mb' => 'decimal:2',
    ];
}
