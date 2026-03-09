<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'user_id',
        'amount',
        'type',
        'description',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
