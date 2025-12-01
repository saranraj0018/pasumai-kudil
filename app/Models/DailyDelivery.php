<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyDelivery extends Model
{

    protected $table = 'daily_deliveries';
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'delivery_id',
        'subscription_id',
        'delivery_date',
        'pack',
        'quantity',
        'delivery_status',
        'amount',
        'modify',
    ];

    public function get_user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function get_delivery_partner()
    {
        return $this->belongsTo(DeliveryPartner::class, 'delivery_id', 'id');
    }

    public function get_user_subscription()
    {
        return $this->belongsTo(UserSubscription::class, 'subscription_id', 'id');
    }
}
