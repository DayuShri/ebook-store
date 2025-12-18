<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class BookAuthor extends Model
{
    use HasUuids;

    protected $table = 'book_authors';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['book_id', 'author_id', 'author_order'];
    public $timestamps = false;
}
