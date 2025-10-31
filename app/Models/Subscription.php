<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
     protected $casts = [
        'delivery_days' => 'array',
        'plan_details' => 'array',
    ];

    public function get_user()
    {
        return $this->belongsTo(UserSubscription::class, 'id', 'subscription_id');
    }

}
