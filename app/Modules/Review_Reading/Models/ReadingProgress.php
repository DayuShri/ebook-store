<?php

namespace App\Modules\Review_Reading\Models;

use Illuminate\Database\Eloquent\Model;

class ReadingProgress extends Model
{
    protected $table = 'reading_progress';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'user_id',
        'book_id',
        'last_page_read',
        'total_pages',
        'progress_percentage',
        'last_read_at',
        'device_info'
    ];

    public function sessions()
    {
        return $this->hasMany(ReadingSession::class, 'reading_progress_id');
    }
}
