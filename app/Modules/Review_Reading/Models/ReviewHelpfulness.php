<?php

namespace App\Modules\Review_Reading\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewHelpfulness extends Model
{
    protected $table = 'review_helpfulness';

    public $timestamps = false; // ✅ HARUS public

    protected $fillable = [
        'id',
        'review_id',
        'user_id',
        'is_helpful'
    ];
}
