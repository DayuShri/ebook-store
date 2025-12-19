<?php

namespace App\Modules\Review_Reading\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Review extends Model
{
    use HasUuids;

    protected $table = 'reviews';

    protected $fillable = [
        'id',
        'user_id',
        'book_id',
        'rating',
        'review_text',
        'helpful_count',
        'is_verified_purchase'
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function helpfulness()
    {
        return $this->hasMany(ReviewHelpfulness::class);
    }
}
