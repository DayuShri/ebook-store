<?php

namespace App\Modules\Wishlist\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Book extends Model
{
    use HasFactory, HasUuids;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount_percentage' => 'decimal:2',
            'file_size_mb' => 'decimal:2',
            'publication_date' => 'date',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the publisher of the book.
     */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    /**
     * Get the authors of the book.
     */
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'book_authors')
            ->withPivot('author_order')
            ->orderBy('author_order');
    }

    /**
     * Get the categories of the book.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(BookCategory::class, 'book_category_mappings', 'book_id', 'category_id');
    }

    /**
     * Get the final price after discount.
     */
    public function getFinalPriceAttribute(): float
    {
        return $this->price - ($this->price * $this->discount_percentage / 100);
    }
}
