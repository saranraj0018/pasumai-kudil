<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'subscription_id',
        'balance',
    ];

    public function user_subscription()
    {
        return $this->belongsTo(UserSubscription::class, 'subscription_id', 'id');
    }
}
