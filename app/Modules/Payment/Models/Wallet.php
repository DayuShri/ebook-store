<?php

namespace App\Modules\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Wallet extends Model
{
    use HasUuids; // Menggunakan UUID sesuai standar proyek

    protected $fillable = [
        'user_id',
        'balance',
    ];

    // Relasi ke transaksi
    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }
}