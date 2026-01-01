<?php

namespace App\Modules\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class WalletTransaction extends Model
{
    use HasUuids;

    // --- TAMBAHKAN KODE INI ---
    /**
     * Memberitahu Laravel bahwa tabel ini tidak memiliki kolom 'updated_at'.
     * Laravel tetap akan mengisi 'created_at'.
     */
    const UPDATED_AT = null;
    // --------------------------

    protected $fillable = [
        'wallet_id',
        'transaction_type', 
        'amount',
        'balance_before',
        'balance_after',
        'reference_id',
        'description',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    protected $appends = ['type'];

    public function getTypeAttribute()
    {
        return $this->transaction_type;
    }
}