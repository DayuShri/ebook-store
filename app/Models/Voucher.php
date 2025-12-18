<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Voucher extends Model
{
    use HasUuids;

    public $timestamps = false; // tabel vouchers hanya created_at
    protected $fillable = [
        'code','description','discount_type','discount_value','max_discount_amount',
        'min_purchase_amount','quota','used_count','valid_from','valid_until','is_active','created_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'created_at' => 'datetime',
    ];
}
