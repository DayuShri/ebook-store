<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class BookCategory extends Model
{
    use HasUuids;

    protected $table = 'book_categories';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * ❗ Tabel TIDAK punya updated_at
     */
    public $timestamps = false; // 🔥 INI KUNCI UTAMA

    /**
     * UUID dikirim manual
     */
    protected $fillable = [
        'name',
        'slug',
        'parent_id',
    ];

    // ===============================
    // RELATIONS
    // ===============================

    public function parent()
    {
        return $this->belongsTo(BookCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(BookCategory::class, 'parent_id');
    }
}
