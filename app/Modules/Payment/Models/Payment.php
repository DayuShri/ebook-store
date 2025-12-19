<?php

namespace App\Modules\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Payment extends Model
{
    protected $table = 'payments';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'payment_number',
        'order_id',
        'user_id',
        'amount',
        'payment_method',
        'payment_gateway_ref',
        'status',
        'paid_at',
        'expired_at',
        'checkout_link'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}