<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'wallet_id',
        'type',
        'amount',
        'balance_amount',
        'description',
        'date',
    ];
    public function get_user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
