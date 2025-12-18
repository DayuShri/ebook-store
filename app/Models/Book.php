<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Book extends Model
{
    use HasUuids;

    protected $table = 'books';

    protected $fillable = [
        'isbn','title','subtitle','synopsis','cover_image_url','price','discount_percentage',
        'publication_date','page_count','language','file_format','file_size_mb','publisher_id',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
