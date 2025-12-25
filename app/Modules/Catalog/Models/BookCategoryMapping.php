<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Str;

class BookCategoryMapping extends Pivot
{
    protected $table = 'book_category_mappings';

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}
