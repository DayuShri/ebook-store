<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class BookCategoryMapping extends Model
{
    use HasUuids;

    protected $table = 'book_category_mappings';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'book_id',
        'category_id',
    ];

    public $timestamps = false;
}
