<?php

namespace App\Modules\Catalog\Models;
use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Catalog\Models\BookCategory;
use App\Modules\Review_Reading\Models\Review;
class Book extends Model
{
    protected $table = 'books';

    protected $fillable = [
        'id', 'isbn', 'title', 'subtitle', 'synopsis',
        'author', 'publisher', 'cover_image_url',
        'price', 'discount_percentage', 'publication_date',
        'page_count', 'language', 'file_format',
        'file_size_mb', 'is_active'
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function categories()
    {
        return $this->belongsToMany(
            BookCategory::class,
            'book_category_mappings',
            'book_id',
            'category_id'
        )->using(BookCategoryMapping::class);
    }

    protected static function boot()
{
    parent::boot();

    static::creating(function ($model) {
        if (empty($model->id)) {
            $model->id = (string) Str::uuid();
        }
    });
}

public function reviews()
{
    return $this->hasMany(Review::class, 'book_id', 'id');
}
 

}