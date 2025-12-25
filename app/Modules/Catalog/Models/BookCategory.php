<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Catalog\Models\Book;

class BookCategory extends Model
{
    protected $table = 'book_categories';

    protected $fillable = [
        'id',
        'name',
        'slug',
        'parent_id',
    ];

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    /**
     * Relasi ke buku (many-to-many)
     */
    public function books()
    {
        return $this->belongsToMany(
            Book::class,
            'book_category_mappings',
            'category_id',
            'book_id'
        );
    }

    /**
     * Parent category (self relation)
     */
    public function parent()
    {
        return $this->belongsTo(
            BookCategory::class,
            'parent_id'
        );
    }

    /**
     * Child categories
     */
    public function children()
    {
        return $this->hasMany(
            BookCategory::class,
            'parent_id'
        );
    }
}
