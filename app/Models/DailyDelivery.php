<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyDelivery extends Model
{
    public function get_user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function get_delivery_partner()
    {
        return $this->belongsTo(DeliveryPartner::class, 'delivery_id', 'id');
    }
}
