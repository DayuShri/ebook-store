<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class VoucherUsage extends Model
{
    use HasUuids;

    public $timestamps = false; // voucher_usages pakai used_at
    protected $table = 'voucher_usages';

    protected $fillable = [
        'voucher_id',
        'user_id',
        'order_id',
        'discount_amount',
        'used_at',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'used_at' => 'datetime',
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
