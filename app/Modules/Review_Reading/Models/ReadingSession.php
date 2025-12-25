<?php

namespace App\Modules\Review_Reading\Models;

use Illuminate\Database\Eloquent\Model;

class ReadingSession extends Model
{
    protected $table = 'reading_sessions';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'reading_progress_id',
        'started_at',
        'ended_at',
        'duration_minutes',
        'pages_read'
    ];
}
