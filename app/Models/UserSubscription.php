<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{

    protected $fillable = [
        'user_id',
        'subscription_id',
        'status',
        'start_date',
        'end_date',
        'valid_date',
        'pack',
        'quantity',
    ];
    public function get_subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id', 'id');
    }
}
