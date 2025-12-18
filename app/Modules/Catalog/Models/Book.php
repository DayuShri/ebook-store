<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Modules\Catalog\Models\BookAuthor;
use App\Modules\Catalog\Models\BookCategory;

class Book extends Model
{
    use HasUuids;

    protected $table = 'books';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'isbn',
        'title',
        'subtitle',
        'synopsis',
        'cover_image_url',
        'price',
        'discount_percentage',
        'publication_date',
        'page_count',
        'language',
        'file_format',
        'file_size_mb',
        'publisher_id',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'is_active' => 'boolean',
        'publication_date' => 'date',
    ];

    // ✅ Category tetap boleh (domain kamu)
    public function categories()
    {
        return $this->belongsToMany(
            BookCategory::class,
            'book_category_mappings',
            'book_id',
            'category_id'
        );
    }

    // ✅ Author hanya lewat pivot
    public function authorPivots()
    {
        return $this->hasMany(BookAuthor::class, 'book_id');
    }
//     public function authors()
// {
//     return $this->belongsToMany(
//         \App\Modules\Catalog\Models\Author::class,
//         'book_authors',
//         'book_id',
//         'author_id'
//     )->withPivot('author_order')
//      ->orderBy('pivot_author_order');
// }

}
